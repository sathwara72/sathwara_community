<?php

namespace App\Http\Controllers;

use App\Services\PaymentMailer;
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
use App\Mail\MembershipPurchaseReceiptMail;
use App\Mail\BusinessCreateReceiptMail;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rule;
use App\Services\AdminNotifier;
use App\Models\Setting;
use App\Models\BusinessPaymentLink;

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

        $signupFee = (float) \App\Models\Setting::get('member_signup_fee', '1000');
        $paymentId = $request->input('razorpay_payment_id');

        // Verify Razorpay payment server-side & prevent replay
        $paymentVerification = $this->verifyRazorpayPayment($paymentId, $signupFee);
        if (!$paymentVerification['valid']) {
            return redirect()->back()->withInput()->withErrors([
                'payment' => $paymentVerification['error'],
            ]);
        }
        $paymentStatus = $paymentVerification['status'];

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
        app(\App\Services\RazorpayVerifier::class)->record($paymentId, 'membership', $signupFee, $user, $user->name, $validated['phone']);
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

        // Dispatch Membership Purchase Receipt Email
        PaymentMailer::send($user->email, new MembershipPurchaseReceiptMail($user, $user->memberProfile, $signupFee, $paymentStatus, $paymentId), 'Membership registration', [
            'Member' => $user->name,
            'Amount' => '₹' . number_format($signupFee, 2),
            'Payment ID' => $paymentId,
            'Phone' => $user->memberProfile->phone ?? null,
        ]);

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

        return view('public.register_business', compact('categories', 'areas', 'businessFee', 'razorpayKeyId'));
    }

    /**
     * Validate the Business Registration form before opening Razorpay, so nobody pays for a
     * registration that the final submit would reject.
     */
    public function preValidateBusiness(Request $request)
    {
        $validator = Validator::make($request->all(), $this->businessRegistrationRules(), $this->businessRegistrationMessages());

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()->all(),
            ], 422);
        }

        $check = $this->checkBusinessRegistration($request);
        if ($check['error']) {
            return response()->json([
                'success' => false,
                'errors' => [$check['error'][1]],
            ], 422);
        }

        return response()->json(['success' => true]);
    }

    private function businessRegistrationRules(): array
    {
        return [
            'member_id'           => 'nullable|string|max:255',
            'business_name'       => 'required|string|max:255',
            'owner_name'          => 'required|string|max:255',
            'category_id'         => 'nullable|exists:business_categories,id',
            'description'         => 'nullable|string',
            'address'             => 'required|string',
            'area_id'             => 'required|exists:areas,id',
            'phone'               => 'required|digits:10',
            'whatsapp'            => 'nullable|digits:10',
            'email'               => 'required|email|max:255',
            'password'            => 'required|string|min:6|confirmed',
            'website'             => 'nullable|url|max:255',
            'facebook'            => 'nullable|string|max:255',
            'instagram'           => 'nullable|string|max:255',
            'youtube'             => 'nullable|string|max:255',
            'linkedin'            => 'nullable|string|max:255',
            'logo'                => 'required|file|mimes:jpeg,jpg,png,webp,gif,bmp,pdf|max:10240',
            'gallery'             => 'nullable|array|max:6',
            'gallery.*'           => 'nullable|file|mimes:jpeg,jpg,png,webp,gif,bmp|max:10240',
        ];
    }

    private function businessRegistrationMessages(): array
    {
        return [
            'logo.required' => 'Please upload your Business Logo or Visiting Card.',
            'logo.mimes'    => 'Business Logo must be an image file (JPG, PNG, WEBP) or a PDF document.',
            'logo.max'      => 'Business Logo file size must not exceed 10MB.',
            'email.required' => 'Business email is required and will be used for your Business Panel login.',
            'password.required' => 'Please set a password for your Business Panel account.',
            'password.min' => 'Password must be at least 6 characters.',
            'password.confirmed' => 'The password and confirmation do not match. (પાસવર્ડ અને કન્ફર્મ પાસવર્ડ સરખા નથી.)',
        ];
    }

    /**
     * Rules beyond field validation, shared by pre-validation and the final submit: unique business
     * email and the Member ID must exist.
     *
     * @return array{error: ?array{0: string, 1: string}, user_id: ?int, member_id: ?string}
     */
    private function checkBusinessRegistration(Request $request): array
    {
        $result = ['error' => null, 'user_id' => null, 'member_id' => null];

        // Business email doubles as the Business Panel login, so it must be unique
        if (Business::where('email', strtolower(trim((string) $request->email)))->exists()) {
            $result['error'] = ['email', 'This email address is already registered with another business account.'];
            return $result;
        }

        $memberUser = null;
        if ($request->filled('member_id')) {
            $result['member_id'] = trim($request->member_id);
            $memberUser = $this->findMemberByCode($result['member_id']);

            if (!$memberUser) {
                $result['error'] = ['member_id', __('messages.member_id_not_found') ?? 'The entered Member ID does not exist in our database. Please check your Member ID.'];
                return $result;
            }
        } elseif (auth()->check()) {
            $memberUser = auth()->user();
        }

        // A member may register any number of businesses
        $result['user_id'] = $memberUser?->id;

        return $result;
    }

    /**
     * Find a member by what they type as "Member ID": member code (e.g. SSAM0123), numeric id or phone.
     */
    private function findMemberByCode(string $memberId): ?User
    {
        $memberUser = User::where('member_code', $memberId)
            ->orWhere('member_code', strtoupper($memberId))
            ->first();

        $numericId = (int) preg_replace('/[^0-9]/', '', $memberId);
        if (!$memberUser && $numericId > 0) {
            $memberUser = User::find($numericId);
        }

        if (!$memberUser) {
            $profile = MemberProfile::where('id', $numericId)
                ->orWhere('phone', $memberId)
                ->first();
            $memberUser = $profile?->user;
        }

        return $memberUser;
    }

    /**
     * Handle Business Registration Submission
     */
    public function submitBusinessRegister(Request $request)
    {
        $request->validate($this->businessRegistrationRules() + [
            'razorpay_payment_id' => 'nullable|string|max:255',
        ], $this->businessRegistrationMessages());

        $check = $this->checkBusinessRegistration($request);
        if ($check['error']) {
            return redirect()->back()->withInput()->withErrors([$check['error'][0] => $check['error'][1]]);
        }
        $userId = $check['user_id'];
        $rawMemberId = $check['member_id'];

        $businessFee = (float) \App\Models\Setting::get('business_registration_fee', '500');
        $paymentId = $request->input('razorpay_payment_id');

        // Verify Razorpay payment server-side & prevent replay
        $paymentVerification = $this->verifyRazorpayPayment($paymentId, $businessFee);
        if (!$paymentVerification['valid']) {
            return redirect()->back()->withInput()->withErrors([
                'payment' => $paymentVerification['error'],
            ]);
        }
        $paymentStatus = $paymentVerification['status'];

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
            'user_id'          => $userId,
            'category_id'      => $request->category_id,
            'area_id'          => $request->area_id,
            'member_id'        => $rawMemberId,
            'business_name'    => $request->business_name,
            'owner_name'       => $request->owner_name,
            'description'      => $request->description ?? '',
            'address'          => $request->address,
            'phone'            => $request->phone,
            'whatsapp'         => $request->whatsapp ?? $request->phone,
            'email'            => strtolower(trim($request->email)),
            'password'         => $request->password, // cast to hashed automatically
            'email_verified_at' => now(),
            'website'          => $request->website,
            'facebook'         => $request->facebook,
            'instagram'        => $request->instagram,
            'youtube'          => $request->youtube,
            'linkedin'         => $request->linkedin,
            'logo_path'        => $logoPath,
            'gallery_images'   => $galleryPaths,
            'status'           => 'pending',
            'payment_id'       => $paymentId,
            'payment_status'   => $paymentStatus,
            'payment_amount'   => $businessFee,
        ]);
        app(\App\Services\RazorpayVerifier::class)->record($paymentId, 'business_registration', $businessFee, $newBusiness, $newBusiness->owner_name . ' (' . $newBusiness->business_name . ')', $newBusiness->phone);

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
        PaymentMailer::send($recipientEmail, new BusinessCreateReceiptMail($newBusiness, $userId ? User::find($userId) : null, $businessFee, $paymentStatus, $paymentId), 'Business registration', [
            'Business' => $newBusiness->business_name,
            'Owner' => $newBusiness->owner_name,
            'Amount' => '₹' . number_format($businessFee, 2),
            'Payment ID' => $paymentId,
            'Phone' => $newBusiness->phone,
        ]);

        if ($request->input('redirect_to') === 'dashboard') {
            $redirectTarget = redirect()->route('member.dashboard');
        } elseif ($request->filled('redirect_to') && parse_url($request->input('redirect_to'), PHP_URL_HOST) === $request->getHost()) {
            $redirectTarget = redirect($request->input('redirect_to'));
        } elseif ($request->headers->has('referer')) {
            $redirectTarget = redirect()->back();
        } else {
            $redirectTarget = redirect()->route('register.business');
        }

        $response = $redirectTarget
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

        $memberUser = $this->findMemberByCode($memberId);

        if ($memberUser) {
            $memberUser->load('memberProfile');
            $name = $memberUser->memberProfile
                ? trim($memberUser->memberProfile->first_name . ' ' . $memberUser->memberProfile->last_name)
                : $memberUser->name;
            // Visitors who are not logged in only see a masked name, so the lookup cannot list members
            if (!auth()->check() && !auth()->guard('business')->check()) {
                $name = $this->maskName($name);
            }
            $memberCode = $memberUser->member_code ?: ('#' . sprintf('%05d', $memberUser->id));

            return response()->json([
                'found' => true,
                'name' => $name,
                'member_id' => $memberCode,
                'message' => "✓ Member Found: {$name} ({$memberCode})"
            ]);
        }

        return response()->json([
            'found' => false,
            'message' => 'Member ID does not exist in database.'
        ]);
    }

    /**
     * "Karan Sathwara" -> "K**** S*******"
     */
    private function maskName(string $name): string
    {
        return collect(preg_split('/\s+/u', trim($name)))
            ->filter()
            ->map(fn ($word) => mb_substr($word, 0, 1) . str_repeat('*', max(1, mb_strlen($word) - 1)))
            ->implode(' ');
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

    /**
     * Verify the Razorpay payment for a registration fee. A fee that is due must be paid.
     *
     * @return array{valid: bool, status: string, error: ?string}
     */
    protected function verifyRazorpayPayment(?string $paymentId, float $expectedAmount): array
    {
        $result = app(\App\Services\RazorpayVerifier::class)->verify($paymentId, $expectedAmount);

        return [
            'valid' => $result['valid'],
            'status' => $expectedAmount > 0 ? 'paid' : 'unpaid',
            'error' => $result['error'],
        ];
    }
}
