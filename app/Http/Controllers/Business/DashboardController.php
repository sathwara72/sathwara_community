<?php

namespace App\Http\Controllers\Business;

use App\Services\PaymentMailer;
use App\Services\RazorpayVerifier;
use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Area;
use App\Models\BusinessCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
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
        $business   = $this->business()->load('category', 'area', 'user');
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
            'email'         => ['required', 'email', 'max:255', Rule::unique('businesses', 'email')->ignore($business->id)->whereNull('deleted_at')],
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

        $request->merge(['email' => strtolower(trim((string) $request->email))]);

        $request->validate($rules, [
            'email.unique'              => 'This email is already registered with another business.',
            'current_password.required' => 'Please enter your current password.',
            'password.confirmed'        => 'The new password and confirmation do not match.',
            'password.min'              => 'Password must be at least 6 characters.',
        ]);

        $data = $request->only([
            'member_id', 'business_name', 'owner_name', 'category_id', 'area_id',
            'description', 'address', 'phone', 'whatsapp', 'email',
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
        $gallery = is_array($business->gallery_images) ? $business->gallery_images : [];
        if ($request->filled('delete_gallery')) {
            $toDelete = (array) $request->input('delete_gallery');
            foreach ($toDelete as $path) {
                if (in_array($path, $gallery)) {
                    Storage::disk('public')->delete($path);
                    $gallery = array_values(array_filter($gallery, fn($g) => $g !== $path));
                }
            }
        }

        // Handle Gallery Upload
        if ($request->hasFile('gallery')) {
            $remainingSlots = max(0, 6 - count($gallery));
            $uploadedFiles  = array_filter((array) $request->file('gallery'), fn($f) => $f !== null && $f->isValid());
            $files          = array_slice(array_values($uploadedFiles), 0, $remainingSlots);
            foreach ($files as $file) {
                $gallery[] = $file->store('businesses/gallery', 'public');
            }
        }

        // Always persist the gallery state (even if unchanged, ensures cast/array consistency)
        $data['gallery_images'] = array_values($gallery);

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

        $renewalFee = (float) Setting::get('business_renewal_fee', Setting::get('business_registration_fee', '500'));

        // Renewal set to Free by the admin: renew without any payment or transaction
        if ($renewalFee <= 0) {
            $business->approved_at = now();
            $business->status = 'approved';
            $business->membership_status = 'active';
            $business->save();

            return redirect()->route('business.renewal')->with('success', __('Business membership renewed successfully! Your listing is now active for 1 year.'));
        }

        $request->validate([
            'razorpay_payment_id' => 'required|string|max:255',
        ]);

        $paymentId = $request->razorpay_payment_id;

        $verification = app(RazorpayVerifier::class)->verify($paymentId, $renewalFee);
        if (!$verification['valid']) {
            return redirect()->route('business.renewal')->with('error', $verification['error']);
        }


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

        app(RazorpayVerifier::class)->record($paymentId, 'business_renewal', $renewalFee, $business, $business->owner_name . ' (' . $business->business_name . ')', $business->phone);

        Log::info('Business renewed via online payment', [
            'business_id' => $business->id,
            'payment_id' => $request->razorpay_payment_id,
            'amount' => $renewalFee,
        ]);

        PaymentMailer::send($business->email, new BusinessRenewalReceiptMail($business, $link), 'Business renewal', [
            'Business' => $business->business_name,
            'Amount' => '₹' . number_format((float) $link->amount, 2),
            'Payment ID' => $link->razorpay_payment_id,
            'Phone' => $business->phone,
        ]);

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

            // The redirect URL can be typed by anyone, so the link's real status comes from Razorpay
            $remote = $link && $link->status !== 'paid' ? app(RazorpayVerifier::class)->fetchPaymentLink($paymentLinkId) : null;
            if ($remote) {
                $status = $remote['status'] ?? null;
                $paymentId = $remote['payments'][0]['payment_id'] ?? $paymentId;
            }

            if ($link && ($link->status === 'paid' || ($remote && $status === 'paid'))) {
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

                    app(RazorpayVerifier::class)->record($business->payment_id, 'business_renewal', (float) $link->amount, $business, $business->owner_name . ' (' . $business->business_name . ')', $business->phone);

                    PaymentMailer::send($business->email, new BusinessRenewalReceiptMail($business, $link), 'Business renewal', [
                        'Business' => $business->business_name,
                        'Amount' => '₹' . number_format((float) $link->amount, 2),
                        'Payment ID' => $link->razorpay_payment_id,
                        'Phone' => $business->phone,
                    ]);
                }

                return redirect()->route('business.renewal')->with('success', __('Business membership renewed successfully! Your listing is now active for 1 year.'));
            }
        }

        return redirect()->route('business.renewal');
    }
}
