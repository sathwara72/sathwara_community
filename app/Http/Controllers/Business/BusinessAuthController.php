<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class BusinessAuthController extends Controller
{
    /**
     * Show the Business Login Form
     */
    public function showLoginForm()
    {
        if (Auth::guard('business')->check()) {
            return redirect()->route('business.profile.edit');
        }

        return view('business.auth.login');
    }

    /**
     * Handle Business Login
     */
    public function login(Request $request)
    {
        $request->validate([
            'login'    => 'required|string',
            'password' => 'required|string',
        ], [
            'login.required'    => 'Please enter your Email or Phone number.',
            'password.required' => 'Please enter your password.',
        ]);

        $login    = trim($request->login);
        $password = $request->password;
        $throttleKey = \Illuminate\Support\Str::transliterate(\Illuminate\Support\Str::lower($login) . '|' . $request->ip());

        // Check if rate limited
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'login' => trans('auth.throttle', [
                    'seconds' => $seconds,
                    'minutes' => ceil($seconds / 60),
                ]),
            ]);
        }

        // Try finding business by email or phone
        $business = Business::where('email', $login)
            ->orWhere('phone', $login)
            ->first();

        if (!$business) {
            \Illuminate\Support\Facades\RateLimiter::hit($throttleKey, 60);
            throw ValidationException::withMessages([
                'login' => __('auth.failed'),
            ]);
        }

        // Check if business has a direct password set
        if ($business->password && Hash::check($password, $business->password)) {
            \Illuminate\Support\Facades\RateLimiter::clear($throttleKey);
            // Direct password match — log in
            Auth::guard('business')->login($business, $request->boolean('remember'));
            $request->session()->regenerate();

            Log::info("Business Login: {$business->business_name} (ID: {$business->id}) logged in via business guard.");

            return redirect()->intended(route('business.profile.edit'));
        }

        // Fallback: check linked Member user's password
        if ($business->user_id && $business->user) {
            $memberUser = $business->user;
            if (Hash::check($password, $memberUser->password)) {
                \Illuminate\Support\Facades\RateLimiter::clear($throttleKey);
                // Sync password to business account
                $business->password = $password; // auto-hashed via cast
                $business->save();

                Auth::guard('business')->login($business, $request->boolean('remember'));
                $request->session()->regenerate();

                Log::info("Business Login (member fallback): {$business->business_name} synced password from member account.");

                return redirect()->intended(route('business.profile.edit'));
            }
        }

        \Illuminate\Support\Facades\RateLimiter::hit($throttleKey, 60);
        throw ValidationException::withMessages([
            'login' => __('auth.failed'),
        ]);
    }

    /**
     * Handle Business Logout
     */
    public function logout(Request $request)
    {
        Auth::guard('business')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('business.login')->with('success', 'You have been logged out successfully.');
    }
}
