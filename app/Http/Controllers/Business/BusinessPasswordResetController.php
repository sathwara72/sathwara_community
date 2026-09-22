<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Mail\ResetPasswordOtpMail;
use App\Models\Business;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class BusinessPasswordResetController extends Controller
{
    /**
     * Display the business forgot password request view.
     */
    public function showForgotPasswordForm(): View
    {
        return view('business.auth.forgot-password');
    }

    /**
     * Generate OTP and send to the registered business email.
     */
    public function sendOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:businesses,email'],
        ], [
            'email.required' => __('Please enter your Email or Phone number.'),
            'email.exists' => __('This email is not registered with any business.'),
        ]);

        $email = trim($request->email);
        $otp = (string) mt_rand(100000, 999999);

        Log::info("Business password reset OTP generated for {$email}");

        // Store OTP in database (password_reset_tokens table)
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => Hash::make($otp),
                'created_at' => now(),
            ]
        );

        // Send Email using existing community OTP Mailable
        Mail::to($email)->send(new ResetPasswordOtpMail($otp));

        // Save email and reset attempt counter in session
        session([
            'business_reset_email' => $email,
            'business_password_reset_otp_attempts' => 0,
        ]);

        return redirect()->route('business.password.otp.verify.form')
            ->with('status', __('messages.otp_email_sent_status'));
    }

    /**
     * Display the OTP verification view.
     */
    public function showVerifyOtpForm(): View|RedirectResponse
    {
        if (!session()->has('business_reset_email')) {
            return redirect()->route('business.password.request')
                ->withErrors(['email' => __('This email is not registered with any business.')]);
        }

        return view('business.auth.verify-otp');
    }

    /**
     * Verify OTP code with rate limit and attempt lockout.
     */
    public function verifyOtp(Request $request): RedirectResponse
    {
        $email = session('business_reset_email');

        if (!$email) {
            return redirect()->route('business.password.request')
                ->withErrors(['email' => __('This email is not registered with any business.')]);
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

        // Track and enforce attempt limit (max 5)
        $attempts = (int) session('business_password_reset_otp_attempts', 0) + 1;
        session(['business_password_reset_otp_attempts' => $attempts]);

        if ($attempts > 5) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            session()->forget(['business_reset_email', 'business_password_reset_otp_attempts']);
            return redirect()->route('business.password.request')
                ->withErrors(['email' => __('messages.too_many_otp_attempts_error')]);
        }

        // Verify Code Hash
        if (!Hash::check($request->otp, $record->token)) {
            return back()->withErrors(['otp' => __('messages.otp_incorrect_error')]);
        }

        // Mark verified in session
        session(['business_otp_verified_email' => $email]);
        session()->forget(['business_reset_email', 'business_password_reset_otp_attempts']);

        return redirect()->route('business.password.reset')
            ->with('status', __('messages.otp_code_verified_status'));
    }

    /**
     * Display the new password reset view.
     */
    public function showResetPasswordForm(): View|RedirectResponse
    {
        if (!session()->has('business_otp_verified_email')) {
            return redirect()->route('business.password.request')
                ->withErrors(['email' => __('messages.verify_identity_first')]);
        }

        return view('business.auth.reset-password');
    }

    /**
     * Reset and save the new business password.
     */
    public function resetPassword(Request $request): RedirectResponse
    {
        $email = session('business_otp_verified_email');

        if (!$email) {
            return redirect()->route('business.password.request')
                ->withErrors(['email' => __('messages.verify_identity_first')]);
        }

        $request->validate([
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $business = Business::where('email', $email)->firstOrFail();
        $business->password = Hash::make($request->password);
        $business->save();

        // If business is linked to a Member user account, sync the password
        if ($business->user_id && $business->user) {
            $memberUser = $business->user;
            $memberUser->password = Hash::make($request->password);
            $memberUser->save();
        }

        // Clear OTP tokens and session keys
        DB::table('password_reset_tokens')->where('email', $email)->delete();
        session()->forget('business_otp_verified_email');

        return redirect()->route('business.login')
            ->with('success', __('Your password has been successfully reset. Please log in with your new password.'));
    }
}
