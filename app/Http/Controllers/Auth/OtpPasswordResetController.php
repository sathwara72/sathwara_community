<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\ResetPasswordOtpMail;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class OtpPasswordResetController extends Controller
{
    /**
     * Display the forgot password request view.
     */
    public function showForgotPasswordForm(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Generate OTP and send email.
     */
    public function sendOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ], [
            'email.exists' => __('messages.email_not_found_error'),
        ]);

        $email = $request->email;
        $otp = (string) mt_rand(100000, 999999);

        // Safe logging without exposing OTP code
        \Illuminate\Support\Facades\Log::info("Password reset OTP generated for {$email}");

        // Store OTP in database (password_reset_tokens table)
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => Hash::make($otp),
                'created_at' => now(),
            ]
        );

        // Send Email
        Mail::to($email)->send(new ResetPasswordOtpMail($otp));

        // Save email in session to carry over to verification page
        session([
            'reset_email' => $email,
            'password_reset_otp_attempts' => 0,
        ]);

        return redirect()->route('password.otp.verify.form')
            ->with('status', 'We have emailed your password reset verification code.');
    }

    /**
     * Display the OTP verification view.
     */
    public function showVerifyOtpForm(): View|RedirectResponse
    {
        if (!session()->has('reset_email')) {
            return redirect()->route('password.request')
                ->withErrors(['email' => __('messages.email_not_found_error')]);
        }

        return view('auth.verify-otp');
    }

    /**
     * Verify OTP code.
     */
    public function verifyOtp(Request $request): RedirectResponse
    {
        $email = session('reset_email');

        if (!$email) {
            return redirect()->route('password.request')
                ->withErrors(['email' => __('messages.email_not_found_error')]);
        }

        $request->validate([
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $record = DB::table('password_reset_tokens')->where('email', $email)->first();

        if (!$record) {
            return back()->withErrors(['otp' => __('messages.otp_not_found_error')]);
        }

        // Check Expiration (15 minutes)
        if (now()->subMinutes(15)->gt($record->created_at)) {
            return back()->withErrors(['otp' => __('messages.otp_expired_error')]);
        }

        // Track and enforce attempt limit
        $attempts = (int) session('password_reset_otp_attempts', 0) + 1;
        session(['password_reset_otp_attempts' => $attempts]);

        if ($attempts > 5) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            session()->forget(['reset_email', 'password_reset_otp_attempts']);
            return redirect()->route('password.request')
                ->withErrors(['email' => 'Too many failed OTP attempts. Please request a new code.']);
        }

        // Verify Code Hash
        if (!Hash::check($request->otp, $record->token)) {
            return back()->withErrors(['otp' => __('messages.otp_incorrect_error')]);
        }

        // Mark as verified in session and clear reset_email & attempts
        session(['otp_verified_email' => $email]);
        session()->forget(['reset_email', 'password_reset_otp_attempts']);

        return redirect()->route('password.reset')
            ->with('status', 'Code verified. You can now reset your password.');
    }

    /**
     * Display the new password reset view.
     */
    public function showResetPasswordForm(): View|RedirectResponse
    {
        if (!session()->has('otp_verified_email')) {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'Please verify your identity first.']);
        }

        return view('auth.reset-password');
    }

    /**
     * Reset/Save the user password.
     */
    public function resetPassword(Request $request): RedirectResponse
    {
        $email = session('otp_verified_email');

        if (!$email) {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'Please verify your identity first.']);
        }

        $request->validate([
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::where('email', $email)->firstOrFail();
        $user->password = Hash::make($request->password);
        $user->save();

        // Clear OTP tokens & session keys
        DB::table('password_reset_tokens')->where('email', $email)->delete();
        session()->forget('otp_verified_email');

        return redirect()->route('login')
            ->with('status', 'Your password has been successfully reset. You can now log in.');
    }
}
