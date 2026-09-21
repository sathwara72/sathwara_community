<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\MemberProfile;
use App\Models\FamilyMember;
use App\Models\BusinessCategory;
use App\Models\Business;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Mail\VerifyEmailOtpMail;
use App\Mail\RegisterEmailOtpMail;
use App\Mail\MembershipPurchaseReceiptMail;
use App\Mail\BusinessCreateReceiptMail;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rule;
use App\Services\AdminNotifier;

class RegistrationController extends Controller
{
    /**
     * Display Single-page Registration Form
     */
    public function showMemberRegister()
    {
        $areas = Area::orderBy('name')->get();
        $signupFee = (float) \App\Models\Setting::get('member_signup_fee', '1000');
        $razorpayKeyId = \App\Models\Setting::get('razorpay_key_id', env('RAZORPAY_KEY_ID', ''));
        return view('public.register_member', compact('areas', 'signupFee', 'razorpayKeyId'));
    }

    /**
     * Send OTP for Member Registration Email Verification
     */
    public function sendRegistrationOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first('email'),
            ], 422);
        }

        $email = strtolower(trim($request->email));

        // Check if email already registered
        if (User::where('email', $email)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'This email address is already in use by another registered account.',
            ], 422);
        }

        $otp = (string) mt_rand(100000, 999999);

        // Save OTP to session explicitly
        session([
            'reg_otp_email' => $email,
            'reg_otp_code' => $otp,
            'reg_otp_expires' => now()->addMinutes(10),
        ]);
        session()->save(); // Explicit save for AJAX/database sessions

        Log::info("Member Registration OTP for {$email}: {$otp} | Session ID: " . session()->getId());

        try {
            Mail::to($email)->send(new RegisterEmailOtpMail($otp, $email));
        } catch (\Exception $e) {
            Log::error("Failed to send Member Registration Email OTP: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to send verification email. Please check your SMTP mail configuration.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => __('messages.otp_sent_success'),
        ]);
    }

    /**
     * Verify OTP for Member Registration Email
     */
    public function verifyRegistrationOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Please enter a valid 6-digit OTP code.',
            ], 422);
        }

        $sessionEmail = session('reg_otp_email');
        $sessionOtp = session('reg_otp_code');
        $sessionExpires = session('reg_otp_expires');

        $inputEmail = strtolower(trim($request->email));
        $inputOtp = trim($request->otp);

        // Debug log — always log to help diagnose session issues
        Log::info("OTP Verify attempt | Session ID: " . session()->getId() .
            " | session_email=[{$sessionEmail}] input_email=[{$inputEmail}]" .
            " | session_otp=[{$sessionOtp}] input_otp=[{$inputOtp}]" .
            " | expires=[{$sessionExpires}]");

        if (!$sessionEmail || !$sessionOtp || !$sessionExpires) {
            return response()->json([
                'success' => false,
                'message' => 'No active OTP session found. Please click Send OTP to request a new code.',
            ], 400);
        }

        if ($sessionEmail !== $inputEmail) {
            return response()->json([
                'success' => false,
                'message' => 'Email address mismatch. Please request a new OTP for this email.',
            ], 400);
        }

        if (now()->greaterThan($sessionExpires)) {
            return response()->json([
                'success' => false,
                'message' => 'OTP has expired. Please click Send OTP to receive a new code.',
            ], 400);
        }

        if ($inputOtp !== (string) $sessionOtp) {
            return response()->json([
                'success' => false,
                'message' => __('messages.otp_invalid'),
            ], 400);
        }

        // Mark email as verified in session
        session(['reg_email_verified' => $inputEmail]);
        session()->save();

        return response()->json([
            'success' => true,
            'message' => __('messages.email_verified'),
        ]);
    }

    /**
     * Pre-validate Member Registration Form before initiating Razorpay Payment
     */
    public function preValidateMember(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'middle_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|digits:10',
            'email' => ['required', 'email', Rule::unique('users', 'email')->whereNull('deleted_at')],
            'password' => 'required|string|min:8|confirmed',
            'address' => 'required|string',
            'area_id' => 'required|exists:areas,id',
            'father_member_id' => 'nullable|string|max:50',
            'gender' => 'nullable|in:Male,Female,Other',
            'dob' => 'nullable|date|before:today',
            'blood_group' => 'nullable|string|max:10',
            'education' => 'nullable|string|max:255',
            'occupation' => 'nullable|string|max:255',
            'whatsapp' => 'nullable|digits:10',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:10',
            'photo' => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()->all(),
            ], 422);
        }

        // Verify that email OTP was verified in session
        $verifiedEmail = session('reg_email_verified');
        if (empty($verifiedEmail) || strtolower(trim($request->email)) !== strtolower(trim($verifiedEmail))) {
            return response()->json([
                'success' => false,
                'errors' => [__('messages.please_verify_email')],
            ], 422);
        }

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Final Submission of Membership Registration
     */
    public function submitMemberRegister(Request $request)
    {
        $validated = $request->validate([
            // Mandatory Fields (as per form spec)
            'first_name' => 'required|string|max:255',
            'middle_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|digits:10',
            'email' => ['required', 'email', Rule::unique('users', 'email')->whereNull('deleted_at')],
            'password' => 'required|string|min:8|confirmed',
            'address' => 'required|string',
            'area_id' => 'required|exists:areas,id',

            // Optional Fields
            'father_member_id' => 'nullable|string|max:50',
            'gender' => 'nullable|in:Male,Female,Other',
            'dob' => 'nullable|date|before:today',
            'blood_group' => 'nullable|string|max:10',
            'education' => 'nullable|string|max:255',
            'occupation' => 'nullable|string|max:255',
            'whatsapp' => 'nullable|digits:10',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:10',
            'photo' => 'nullable|image|max:2048',
            'razorpay_payment_id' => 'nullable|string|max:255',
        ]);

        // Server-side check that email was verified via OTP
        $verifiedEmail = session('reg_email_verified');
        if (empty($verifiedEmail) || strtolower(trim($validated['email'])) !== strtolower(trim($verifiedEmail))) {
            return redirect()->back()->withInput()->withErrors([
                'email' => __('messages.please_verify_email'),
            ]);
        }

        $signupFee = (float) \App\Models\Setting::get('member_signup_fee', '1000');
        $paymentId = $request->input('razorpay_payment_id');
        $paymentStatus = (!empty($paymentId) || $signupFee <= 0) ? 'paid' : 'unpaid';

          // Release unique email constraint from any previously soft-deleted user records
        User::onlyTrashed()->where('email', $validated['email'])->update(['email' => null]);

        // 1. Create Login User (status remains 'pending' for Admin Approval)
        $user = User::create([
            'name' => $validated['first_name'] . ' ' . $validated['last_name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'status' => 'pending',
            'account_status' => 'open',
            'payment_id' => $paymentId,
            'payment_status' => $paymentStatus,
            'payment_amount' => $signupFee,
        ]);
        $user->member_code = 'SSAM' . sprintf('%04d', $user->id);
        $user->save();

        // Assign Member role
        $memberRole = Role::findByName('Member');
        $user->assignRole($memberRole);

        // Fetch area info for city, state, pincode fallback
        $area = Area::find($validated['area_id']);

        // Handle Photo Upload
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('registrations/photos', 'public');
        }

        // 2. Create Member Profile
        MemberProfile::create([
            'user_id' => $user->id,
            'first_name' => $validated['first_name'],
            'middle_name' => $validated['middle_name'],
            'last_name' => $validated['last_name'],
            'father_member_id' => $validated['father_member_id'] ?? null,
            'gender' => $validated['gender'] ?? 'Male',
            'dob' => $validated['dob'] ?? null,
            'blood_group' => $validated['blood_group'] ?? null,
            'education' => $validated['education'] ?? null,
            'occupation' => $validated['occupation'] ?? null,
            'phone' => $validated['phone'],
            'whatsapp' => $validated['phone'],
            'address' => $validated['address'],
            'area_id' => $validated['area_id'],
            'city' => !empty($validated['city']) ? $validated['city'] : ($area ? $area->city : 'Ahmedabad'),
            'state' => !empty($validated['state']) ? $validated['state'] : ($area ? $area->state : 'Gujarat'),
            'pincode' => !empty($validated['pincode']) ? $validated['pincode'] : ($area ? $area->pincode : ''),
            'photo_path' => $photoPath,
            'aadhaar_number' => null,
            'aadhaar_path' => null,
            'pan_number' => null,
            'pan_path' => null,
        ]);

        AdminNotifier::send(
            permission: 'members_manage',
            type: 'member_registered',
            title: 'New Member Registration',
            message: "{$user->name} registered as a new member (Member Code: {$user->member_code})",
            url: route('admin.members.show', $user->id),
            meta: ['member_id' => $user->id, 'member_code' => $user->member_code],
            color: 'primary'
        );

        // Clear OTP verification session
        session()->forget(['reg_otp_email', 'reg_otp_code', 'reg_otp_expires', 'reg_email_verified']);

        // Dispatch Membership Purchase Receipt Email
        if (!empty($user->email)) {
            try {
                Mail::to($user->email)->send(new MembershipPurchaseReceiptMail($user, $user->memberProfile, $signupFee, $paymentStatus, $paymentId));
            } catch (\Throwable $th) {
                Log::error('Membership Receipt Mail Error: ' . $th->getMessage());
            }
        }

        // Log the user in and redirect to account status page
        auth()->login($user);

        $response = redirect()->route('account.status')
            ->with('success', 'Your membership registration has been submitted successfully and is pending approval.');

        if ($paymentStatus === 'paid') {
            $receiptNo = $user->receipt_no ?: \App\Services\ReceiptNumberService::assign($user, 'receipt_no');
            $response->with('purchase_receipt', [
                'type' => 'membership',
                'receipt_no' => $receiptNo,
                'event_title' => 'Community Membership',
                'event_date' => now()->format('d M, Y'),
                'attendee_name' => $user->name,
                'phone' => $user->phone,
                'person_count' => 1,
                'amount_paid' => (float) $signupFee,
                'total_amount' => (float) $signupFee,
                'payment_id' => $paymentId,
                'payment_status' => $paymentStatus,
                'created_at' => now()->format('d M, Y h:i A'),
                'download_url' => route('receipts.membership', $user->id),
            ]);
        }

        return $response;
    }

    /**
     * Show Public Business Registration Form
     */
    public function showBusinessRegister()
    {
        $categories = BusinessCategory::orderBy('name')->get();
        $areas = Area::orderBy('name')->get();
        $businessFee = (float) \App\Models\Setting::get('business_registration_fee', '500');
        $razorpayKeyId = \App\Models\Setting::get('razorpay_key_id', env('RAZORPAY_KEY_ID', ''));

        $existingBusiness = null;
        if (auth()->check()) {
            $user = auth()->user();
            $formattedMemberId = '#' . sprintf('%05d', $user->id);
            $existingBusiness = Business::where('user_id', $user->id)
                ->orWhere('member_id', (string) $user->id)
                ->orWhere('member_id', $formattedMemberId)
                ->orWhere('member_id', '#' . $user->id)
                ->first();
        }

        return view('public.register_business', compact('categories', 'areas', 'existingBusiness', 'businessFee', 'razorpayKeyId'));
    }

    /**
     * Handle Business Registration Submission
     */
    public function submitBusinessRegister(Request $request)
    {
        $request->validate([
            'member_id' => 'nullable|string|max:255',
            'business_name' => 'required|string|max:255',
            'owner_name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:business_categories,id',
            'description' => 'nullable|string',
            'address' => 'required|string',
            'area_id' => 'required|exists:areas,id',
            'phone' => 'required|digits:10',
            'whatsapp' => 'nullable|digits:10',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'facebook' => 'nullable|string|max:255',
            'instagram' => 'nullable|string|max:255',
            'youtube' => 'nullable|string|max:255',
            'linkedin' => 'nullable|string|max:255',
            'logo' => 'required|file|mimes:jpeg,jpg,png,webp,gif,bmp,pdf|max:10240', // Attach Business Logo / Visiting Card
            'gallery' => 'nullable|array|max:6',
            'gallery.*' => 'nullable|file|mimes:jpeg,jpg,png,webp,gif,bmp|max:10240',
            'razorpay_payment_id' => 'nullable|string|max:255',
        ], [
            'logo.required' => 'Please upload your Business Logo or Visiting Card.',
            'logo.mimes' => 'Business Logo must be an image file (JPG, PNG, WEBP) or a PDF document.',
            'logo.max' => 'Business Logo file size must not exceed 10MB.',
        ]);

        $businessFee = (float) \App\Models\Setting::get('business_registration_fee', '500');
        $paymentId = $request->input('razorpay_payment_id');
        $paymentStatus = (!empty($paymentId) || $businessFee <= 0) ? 'paid' : 'unpaid';

        $userId = null;
        $rawMemberId = null;

        if ($request->filled('member_id')) {
            $rawMemberId = trim($request->member_id);
            $numericId = (int) preg_replace('/[^0-9]/', '', $rawMemberId);

            $memberUser = null;
            if ($numericId > 0) {
                $memberUser = User::find($numericId);
            }

            if (!$memberUser) {
                $profile = MemberProfile::where('id', $numericId)
                    ->orWhere('phone', $rawMemberId)
                    ->first();
                if ($profile) {
                    $memberUser = $profile->user;
                }
            }

            if (!$memberUser) {
                return back()->withInput()->withErrors([
                    'member_id' => __('messages.member_id_not_found') ?? 'The entered Member ID does not exist in our database. Please check your Member ID.',
                ]);
            }

            $userId = $memberUser->id;
        }

        if (!$userId && auth()->check()) {
            $userId = auth()->id();
        }

        // Single Business per Member Constraint Check
        if ($userId) {
            $formattedMemberId = '#' . sprintf('%05d', $userId);
            $existingBusiness = Business::where('user_id', $userId)
                ->orWhere('member_id', (string) $userId)
                ->orWhere('member_id', $formattedMemberId)
                ->orWhere('member_id', '#' . $userId)
                ->first();

            if ($existingBusiness) {
                return back()->withInput()->withErrors([
                    'member_id' => "Each member is allowed to register only 1 business. You have already registered '{$existingBusiness->business_name}'. (દરેક સભ્ય માત્ર ૧ જ વ્યવસાય રજીસ્ટર કરી શકે છે.)",
                ]);
            }
        }

        if ($rawMemberId) {
            $existingBusiness = Business::where('member_id', $rawMemberId)->first();
            if ($existingBusiness) {
                return back()->withInput()->withErrors([
                    'member_id' => "A business ('{$existingBusiness->business_name}') has already been registered with Member ID '{$rawMemberId}'. Only 1 business registration per member is allowed.",
                ]);
            }
        }

        // Upload Logo
        $logoPath = $request->file('logo')->store('businesses/logos', 'public');

        // Upload Gallery Images (Max 6 Photos)
        $galleryPaths = [];
        if ($request->hasFile('gallery')) {
            $files = array_slice($request->file('gallery'), 0, 6);
            foreach ($files as $file) {
                $path = $file->store('businesses/gallery', 'public');
                $galleryPaths[] = $path;
            }
        }

        // Create Business (anyone can register)
        $newBusiness = Business::create([
            'user_id' => $userId,
            'category_id' => $request->category_id,
            'area_id' => $request->area_id,
            'member_id' => $rawMemberId,
            'business_name' => $request->business_name,
            'owner_name' => $request->owner_name,
            'description' => $request->description ?? '',
            'address' => $request->address,
            'phone' => $request->phone,
            'whatsapp' => $request->whatsapp ?? $request->phone,
            'email' => $request->email,
            'website' => $request->website,
            'facebook' => $request->facebook,
            'instagram' => $request->instagram,
            'youtube' => $request->youtube,
            'linkedin' => $request->linkedin,
            'logo_path' => $logoPath,
            'gallery_images' => $galleryPaths,
            'status' => 'pending',
            'payment_id' => $paymentId,
            'payment_status' => $paymentStatus,
            'payment_amount' => $businessFee,
        ]);

        AdminNotifier::send(
            permission: 'businesses_manage',
            type: 'business_registered',
            title: 'New Business Registration',
            message: "{$newBusiness->business_name} ({$newBusiness->owner_name}) registered a new business listing",
            url: route('admin.businesses.show', $newBusiness->id),
            meta: ['business_id' => $newBusiness->id],
            color: 'emerald'
        );

        // Dispatch Business Registration Receipt Email
        $recipientEmail = $request->email ?? ($userId ? User::find($userId)?->email : null);
        if (!empty($recipientEmail)) {
            try {
                Mail::to($recipientEmail)->send(new BusinessCreateReceiptMail($newBusiness, $userId ? User::find($userId) : null, $businessFee, $paymentStatus, $paymentId));
            } catch (\Throwable $th) {
                Log::error('Business Receipt Mail Error: ' . $th->getMessage());
            }
        }

        $response = redirect()->route('business.directory')
            ->with('success', 'Your business directory registration has been submitted successfully and is pending admin approval.');

        if ($paymentStatus === 'paid') {
            $receiptNo = $newBusiness->receipt_no ?: \App\Services\ReceiptNumberService::assign($newBusiness, 'receipt_no');
            $response->with('purchase_receipt', [
                'type' => 'business',
                'receipt_no' => $receiptNo,
                'event_title' => $newBusiness->business_name,
                'event_date' => now()->format('d M, Y'),
                'attendee_name' => $newBusiness->owner_name,
                'phone' => $newBusiness->phone,
                'person_count' => 1,
                'amount_paid' => (float) $businessFee,
                'total_amount' => (float) $businessFee,
                'payment_id' => $paymentId,
                'payment_status' => $paymentStatus,
                'created_at' => now()->format('d M, Y h:i A'),
                'download_url' => route('receipts.business', $newBusiness->id),
            ]);
        }

        return $response;
    }

    /**
     * Live AJAX check if Member ID exists & single business limit check
     */
    public function checkMemberId(Request $request)
    {
        $memberId = trim($request->query('member_id', ''));
        if (empty($memberId)) {
            return response()->json(['found' => false, 'message' => '']);
        }

        $numericId = (int) preg_replace('/[^0-9]/', '', $memberId);
        $memberUser = null;
        if ($numericId > 0) {
            $memberUser = User::find($numericId);
        }

        if (!$memberUser) {
            $profile = MemberProfile::where('id', $numericId)
                ->orWhere('phone', $memberId)
                ->first();
            if ($profile) {
                $memberUser = $profile->user;
            }
        }

        if ($memberUser) {
            $name = $memberUser->memberProfile ? ($memberUser->memberProfile->first_name . ' ' . $memberUser->memberProfile->last_name) : $memberUser->name;
            $formattedMemberId = '#' . sprintf('%05d', $memberUser->id);

            // Check if member already registered a business
            $existingBusiness = Business::where('user_id', $memberUser->id)
                ->orWhere('member_id', $memberId)
                ->orWhere('member_id', (string) $memberUser->id)
                ->orWhere('member_id', $formattedMemberId)
                ->first();

            if ($existingBusiness) {
                return response()->json([
                    'found' => false,
                    'has_business' => true,
                    'message' => "❌ Member {$name} has already registered a business ('{$existingBusiness->business_name}'). Each member is allowed to register only 1 business."
                ]);
            }

            return response()->json([
                'found' => true,
                'name' => $name,
                'member_id' => $formattedMemberId,
                'message' => "Member Found: {$name} ({$formattedMemberId})"
            ]);
        }

        return response()->json([
            'found' => false,
            'message' => 'Member ID does not exist in database.'
        ]);
    }

    /**
     * Live AJAX check for Father Member ID / Code
     */
    public function lookupFatherMember(Request $request)
    {
        $code = trim($request->query('code', ''));
        if (empty($code)) {
            return response()->json([
                'found' => false,
                'message' => app()->getLocale() == 'gu' ? 'કૃપા કરીને સભ્ય કોડ દાખલ કરો.' : 'Please enter member code.'
            ]);
        }

        $cleanCode = str_replace(' ', '', $code);

        // 1. Try exact or spaceless member_code match
        $user = User::with('memberProfile')
            ->where(function ($query) use ($code, $cleanCode) {
                $query->where('member_code', $code)
                    ->orWhere('member_code', $cleanCode);
            })
            ->first();

        // 2. Try normalized SSAM + 4 digits
        if (!$user) {
            $digits = preg_replace('/[^0-9]/', '', $code);
            if (!empty($digits)) {
                $paddedCode = 'SSAM' . str_pad($digits, 4, '0', STR_PAD_LEFT);
                $user = User::with('memberProfile')->where('member_code', $paddedCode)->first();

                // 3. Fallback: match by numeric ID
                if (!$user) {
                    $fatherId = (int) $digits;
                    if ($fatherId > 0) {
                        $user = User::with('memberProfile')->find($fatherId);
                    }
                }
            }
        }

        if ($user) {
            $memberCode = $user->member_code ?: $user->formatted_member_id;
            return response()->json([
                'found' => true,
                'name' => $user->display_name,
                'member_code' => $memberCode,
                'message' => $user->display_name . ' (' . $memberCode . ')'
            ]);
        }

        return response()->json([
            'found' => false,
            'message' => app()->getLocale() == 'gu' 
                ? 'કોઈ સભ્ય મળ્યા નથી. કૃપા કરીને સભ્ય કોડ ચકાસો.' 
                : 'No member found with this Member ID.'
        ]);
    }
}
