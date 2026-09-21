<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Area;
use App\Models\BusinessCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Mail\VerifyEmailOtpMail;
use App\Models\BusinessPaymentLink;
use App\Models\Setting;
use App\Services\RazorpayPaymentLinkService;
use App\Mail\BusinessRenewalReceiptMail;

class DashboardController extends Controller
{
    /**
     * Resolve the currently authenticated business.
     */
    protected function business(): Business
    {
        return Auth::guard('business')->user();
    }

    /**
     * Business Dashboard (Redirected to Profile)
     */
    public function index()
    {
        return redirect()->route('business.profile.edit');
    }

    /**
     * Show Profile Edit Form
     */
    public function editProfile()
    {
        $business   = $this->business()->load('category', 'area');
        $categories = BusinessCategory::orderBy('name')->get();
        $areas      = Area::orderBy('name')->get();

        return view('business.profile', compact('business', 'categories', 'areas'));
    }

    /**
     * Update Profile
     */
    public function updateProfile(Request $request)
    {
        $business = $this->business();

        $rules = [
            'member_id'     => 'nullable|string|max:255',
            'business_name' => 'required|string|max:255',
            'owner_name'    => 'required|string|max:255',
            'category_id'   => 'nullable|exists:business_categories,id',
            'area_id'       => 'required|exists:areas,id',
            'description'   => 'nullable|string',
            'address'       => 'required|string',
            'phone'         => 'required|digits:10',
            'whatsapp'      => 'nullable|digits:10',
            'website'       => 'nullable|url|max:255',
            'facebook'      => 'nullable|string|max:255',
            'instagram'     => 'nullable|string|max:255',
            'youtube'       => 'nullable|string|max:255',
            'linkedin'      => 'nullable|string|max:255',
            'logo'          => 'nullable|file|mimes:jpeg,jpg,png,webp,gif,bmp,pdf|max:10240',
            'gallery'       => 'nullable|array|max:6',
            'gallery.*'     => 'nullable|file|mimes:jpeg,jpg,png,webp,gif,bmp|max:10240',
        ];

        // Only validate password if user is trying to change it
        if ($request->filled('password')) {
            $hasExistingPassword = !empty($business->password) || ($business->user && !empty($business->user->password));
            if ($hasExistingPassword) {
                $rules['current_password'] = 'required|string';
            }
            $rules['password'] = 'required|string|min:6|confirmed';
        }

        $request->validate($rules, [
            'current_password.required' => 'Please enter your current password.',
            'password.confirmed'        => 'The new password and confirmation do not match.',
            'password.min'              => 'Password must be at least 6 characters.',
        ]);

        $data = $request->only([
            'member_id', 'business_name', 'owner_name', 'category_id', 'area_id',
            'description', 'address', 'phone', 'whatsapp',
            'website', 'facebook', 'instagram', 'youtube', 'linkedin',
        ]);

        // Handle Logo Upload
        if ($request->hasFile('logo')) {
            if ($business->logo_path) {
                Storage::disk('public')->delete($business->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('businesses/logos', 'public');
        }

        // Handle Gallery Image Deletion first
        $gallery = $business->gallery_images ?? [];
        if ($request->filled('delete_gallery')) {
            $toDelete = (array) $request->input('delete_gallery');
            foreach ($toDelete as $path) {
                if (in_array($path, $gallery)) {
                    Storage::disk('public')->delete($path);
                    $gallery = array_values(array_filter($gallery, fn($g) => $g !== $path));
                }
            }
            $data['gallery_images'] = $gallery;
        }

        // Handle Gallery Upload
        if ($request->hasFile('gallery')) {
            $remainingSlots = max(0, 6 - count($gallery));
            $files = array_slice($request->file('gallery'), 0, $remainingSlots);
            foreach ($files as $file) {
                $gallery[] = $file->store('businesses/gallery', 'public');
            }
            $data['gallery_images'] = $gallery;
        }

        // Handle optional password change
        $passwordChanged = false;
        if ($request->filled('password')) {
            $hasExistingPassword = !empty($business->password) || ($business->user && !empty($business->user->password));
            if ($hasExistingPassword) {
                $matches = false;
                if (!empty($business->password)) {
                    $matches = Hash::check($request->current_password, $business->password) || ($request->current_password === $business->password);
                }
                if (!$matches && $business->user && !empty($business->user->password)) {
                    $matches = Hash::check($request->current_password, $business->user->password) || ($request->current_password === $business->user->password);
                }

                if (!$matches) {
                    return back()->withErrors(['current_password' => 'The current password you entered is incorrect.'])->withInput();
                }
            }

            $data['password'] = Hash::make($request->password);
            $passwordChanged = true;

            // Also synchronize password to linked Member User account if present
            if ($business->user) {
                $business->user->update(['password' => Hash::make($request->password)]);
            }
        }

        $business->update($data);

        $successMsg = $passwordChanged
            ? 'Business profile and password updated successfully.'
            : 'Business profile updated successfully.';

        return redirect()->route('business.profile.edit')
            ->with('success', $successMsg);
    }

    /**
     * Send OTP to new Business Email (for profile email change)
     */
    public function sendProfileEmailOtp(Request $request)
    {
        $request->validate(['email' => 'required|email|max:255']);

        $email    = strtolower(trim($request->email));
        $business = $this->business();

        if (Business::where('email', $email)
            ->where('id', '!=', $business->id)
            ->whereNotNull('email_verified_at')
            ->exists()
        ) {
            return response()->json([
                'success' => false,
                'message' => 'This email is already registered with another business.',
            ], 422);
        }

        $otp = (string) mt_rand(100000, 999999);

        session([
            'biz_profile_otp_email'   => $email,
            'biz_profile_otp_code'    => $otp,
            'biz_profile_otp_expires' => now()->addMinutes(10),
        ]);
        session()->save();

        try {
            Mail::to($email)->send(new VerifyEmailOtpMail($otp, $email));
        } catch (\Exception $e) {
            Log::error('Business Profile Email OTP Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to send OTP email.'], 500);
        }

        return response()->json(['success' => true, 'message' => 'OTP sent. Please check your email.']);
    }

    /**
     * Verify OTP and update Business Email
     */
    public function verifyProfileEmailOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp'   => 'required|string|size:6',
        ]);

        $sessionEmail   = session('biz_profile_otp_email');
        $sessionOtp     = session('biz_profile_otp_code');
        $sessionExpires = session('biz_profile_otp_expires');
        $inputEmail     = strtolower(trim($request->email));
        $inputOtp       = trim($request->otp);

        if (!$sessionEmail || !$sessionOtp || !$sessionExpires) {
            return response()->json(['success' => false, 'message' => 'No active OTP session. Please request a new OTP.'], 400);
        }
        if ($sessionEmail !== $inputEmail) {
            return response()->json(['success' => false, 'message' => 'Email mismatch. Please request a new OTP.'], 400);
        }
        if (now()->greaterThan($sessionExpires)) {
            return response()->json(['success' => false, 'message' => 'OTP has expired. Please request a new code.'], 400);
        }

        $attempts = (int) session('biz_profile_otp_attempts', 0) + 1;
        session(['biz_profile_otp_attempts' => $attempts]);

        if ($attempts > 5) {
            session()->forget(['biz_profile_otp_email', 'biz_profile_otp_code', 'biz_profile_otp_expires', 'biz_profile_otp_attempts']);
            return response()->json(['success' => false, 'message' => 'Too many failed OTP attempts. Please request a new code.'], 429);
        }

        if ($inputOtp !== (string) $sessionOtp) {
            return response()->json(['success' => false, 'message' => 'Invalid OTP. Please try again.'], 400);
        }

        // Update email
        $business = $this->business();
        $business->update([
            'email'             => $inputEmail,
            'email_verified_at' => now(),
        ]);

        session()->forget(['biz_profile_otp_email', 'biz_profile_otp_code', 'biz_profile_otp_expires', 'biz_profile_otp_attempts']);

        return response()->json(['success' => true, 'message' => 'Email updated and verified successfully.']);
    }

    /**
     * Update Business Password
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password'      => 'required|string',
            'password'              => 'required|string|min:6|confirmed',
        ], [
            'password.confirmed' => 'The new password and confirmation do not match.',
            'password.min'       => 'Password must be at least 6 characters.',
        ]);

        $business = $this->business();

        if (!Hash::check($request->current_password, $business->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }

        $business->password = $request->password; // auto-hashed via cast
        $business->save();

        return back()->with('success', 'Password changed successfully.');
    }

    /**
     * Show Renewal Page
     */
    public function renewal()
    {
        $business = $this->business()->load('paymentLinks');
        $renewalFee = (float) Setting::get('business_renewal_fee', Setting::get('business_registration_fee', '500'));
        $razorpayKeyId = Setting::get('razorpay_key_id', env('RAZORPAY_KEY_ID', ''));
        $pendingLink = $business->paymentLinks
            ->where('status', 'created')
            ->filter(function ($link) {
                return is_null($link->expires_at) || $link->expires_at->isFuture();
            })
            ->first();

        return view('business.renewal', compact('business', 'renewalFee', 'razorpayKeyId', 'pendingLink'));
    }

    /**
     * Process direct renewal payment via Razorpay Checkout
     */
    public function processRenewal(Request $request)
    {
        $business = $this->business();

        if (!$business->isRenewalDue()) {
            return redirect()->route('business.renewal')->with('info', __('Your business listing is currently active and does not require renewal yet.'));
        }

        $request->validate([
            'razorpay_payment_id' => 'required|string|max:255',
        ]);

        $paymentId = $request->razorpay_payment_id;
        $alreadyUsed = BusinessPaymentLink::where('razorpay_payment_id', $paymentId)->exists()
            || \App\Models\User::where('payment_id', $paymentId)->exists();
        if ($alreadyUsed) {
            return redirect()->route('business.renewal')->with('error', 'This payment transaction ID has already been utilized.');
        }

        $renewalFee = (float) Setting::get('business_renewal_fee', Setting::get('business_registration_fee', '500'));

        $link = BusinessPaymentLink::create([
            'business_id' => $business->id,
            'amount' => $renewalFee,
            'razorpay_link_id' => 'online_' . time(),
            'razorpay_link_url' => '',
            'status' => 'paid',
            'razorpay_payment_id' => $request->razorpay_payment_id,
            'created_by' => null,
            'paid_at' => now(),
            'expires_at' => now(),
        ]);

        $business->approved_at = now();
        $business->status = 'approved';
        $business->membership_status = 'active';
        $business->payment_status = 'paid';
        $business->payment_id = $request->razorpay_payment_id;
        $business->payment_amount = $renewalFee;
        $business->save();

        Log::info('Business renewed via online payment', [
            'business_id' => $business->id,
            'payment_id' => $request->razorpay_payment_id,
            'amount' => $renewalFee,
        ]);

        if (!empty($business->email)) {
            try {
                Mail::to($business->email)->send(new BusinessRenewalReceiptMail($business, $link));
            } catch (\Throwable $e) {
                Log::error('Business Renewal Receipt Mail Error: ' . $e->getMessage());
            }
        }

        return redirect()->route('business.renewal')->with('success', __('Business membership renewed successfully! Your listing is now active for 1 year.'));
    }

    /**
     * Generate Razorpay Payment Link and redirect
     */
    public function generateRenewalLink(Request $request)
    {
        $business = $this->business();

        if (!$business->isRenewalDue()) {
            return redirect()->route('business.renewal')->with('info', __('Your business listing is currently active and does not require renewal yet.'));
        }

        $renewalFee = (float) Setting::get('business_renewal_fee', Setting::get('business_registration_fee', '500'));

        try {
            $result = app(RazorpayPaymentLinkService::class)->createLink($business, $renewalFee);

            $link = BusinessPaymentLink::create([
                'business_id' => $business->id,
                'amount' => $renewalFee,
                'razorpay_link_id' => $result['id'],
                'razorpay_link_url' => $result['short_url'],
                'status' => 'created',
                'created_by' => null,
                'expires_at' => $result['expires_at'],
            ]);

            return redirect($result['short_url']);
        } catch (\Throwable $e) {
            Log::error('Generate Business Renewal Link Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate payment link. Please try online payment or contact admin.');
        }
    }

    /**
     * Callback for Razorpay Payment Link redirect
     */
    public function renewalCallback(Request $request)
    {
        $business = $this->business();
        $paymentLinkId = $request->query('razorpay_payment_link_id');
        $paymentId = $request->query('razorpay_payment_id');
        $status = $request->query('razorpay_payment_link_status');

        if ($paymentLinkId) {
            $link = BusinessPaymentLink::where('business_id', $business->id)
                ->where('razorpay_link_id', $paymentLinkId)
                ->first();

            if ($link && ($status === 'paid' || !empty($paymentId))) {
                if ($link->status !== 'paid') {
                    $link->status = 'paid';
                    $link->paid_at = now();
                    $link->razorpay_payment_id = $paymentId ?: $link->razorpay_payment_id;
                    $link->save();

                    $business->approved_at = now();
                    $business->status = 'approved';
                    $business->membership_status = 'active';
                    $business->payment_status = 'paid';
                    $business->payment_id = $paymentId ?: $link->razorpay_payment_id;
                    $business->payment_amount = $link->amount;
                    $business->save();

                    if (!empty($business->email)) {
                        try {
                            Mail::to($business->email)->send(new BusinessRenewalReceiptMail($business, $link));
                        } catch (\Throwable $e) {
                            Log::error('Business Renewal Callback Receipt Mail Error: ' . $e->getMessage());
                        }
                    }
                }

                return redirect()->route('business.renewal')->with('success', __('Business membership renewed successfully! Your listing is now active for 1 year.'));
            }
        }

        return redirect()->route('business.renewal');
    }
}
