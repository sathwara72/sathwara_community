<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\MemberProfile;
use App\Models\User;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerifyEmailOtpMail;
use Illuminate\Validation\Rule;

class DashboardController extends Controller
{
    /**
     * Member Dashboard Index
     */
    public function index()
    {
        $user = auth()->user();
        $profile = $user->memberProfile;
        $family = $user->familyMembers;
        $familyCount = $family->count();
        
        // Active Published Events
        $activeEvents = Event::where('status', 'published')
            ->where(function($q) {
                $q->whereNull('published_date')
                  ->orWhere('published_date', '<=', now()->toDateString());
            })
            ->where('date', '>=', now()->toDateString())
            ->orderBy('date', 'asc')
            ->get();

        // My Event Registrations (Passes)
        $myRegistrations = \App\Models\EventRegistration::where('user_id', $user->id)
            ->with('event')
            ->orderBy('created_at', 'desc')
            ->get();

        $totalPersonsSum = $myRegistrations->filter(function($r) {
            $isPass = ($r->registration_type === 'pass') || (empty($r->form_data['student_name']) && empty($r->form_data['surname']));
            $fee = (float)($r->event?->pass_fee ?? 0);
            return $isPass && ($fee <= 0 || $r->payment_status === 'paid');
        })->sum(function($r) {
            return (int) ($r->form_data['person_count'] ?? 1);
        });
        
        $formattedMemberId = $user->member_code ?: ('SSAM' . sprintf('%04d', $user->id));
        $myBusinesses = \App\Models\Business::where('user_id', $user->id)
                        ->orWhere('member_id', (string)$user->id)
                        ->orWhere('member_id', $formattedMemberId)
                        ->orWhere('member_id', '#' . $user->id)
                        ->with('category', 'area')
                        ->latest()
                        ->get();

        return view('member.dashboard', compact(
            'user', 
            'profile', 
            'family', 
            'familyCount', 
            'activeEvents', 
            'myRegistrations', 
            'totalPersonsSum',
            'myBusinesses'
        ));
    }

    /**
     * Display Registered Businesses for logged in member
     */
    public function myBusinesses()
    {
        $user = auth()->user();
        $formattedMemberId = '#' . sprintf('%05d', $user->id);
        $businesses = \App\Models\Business::where('user_id', $user->id)
                        ->orWhere('member_id', (string)$user->id)
                        ->orWhere('member_id', $formattedMemberId)
                        ->orWhere('member_id', '#' . $user->id)
                        ->with('category', 'area')
                        ->latest()
                        ->get();

        return view('member.my_businesses', compact('user', 'businesses'));
    }

    /**
     * Account Status Display (Pending/Rejected Feedback)
     */
    public function status()
    {
        $user = auth()->user();
        
        if ($user->hasRole('Administrator') || $user->hasRole('Sub Admin')) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->status === 'approved' && $user->account_status !== 'close') {
            return redirect()->route('member.dashboard');
        }

        return view('member.status', compact('user'));
    }

    /**
     * Edit Profile Form
     */
    public function editProfile()
    {
        $user = auth()->user();
        $profile = $user->memberProfile;
        $areas = \App\Models\Area::orderBy('name')->get();
        return view('member.profile', compact('user', 'profile', 'areas'));
    }

    /**
     * Update Profile Info
     */
    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        $profile = $user->memberProfile;

        $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'father_member_id' => 'nullable|string|max:50',
            'gender' => 'required|in:Male,Female,Other',
            'dob' => 'nullable|date|before:today',
            'blood_group' => 'nullable|string|max:10',
            'education' => 'nullable|string|max:255',
            'occupation' => 'nullable|string|max:255',
            'phone' => 'required|digits:10',
            'whatsapp' => 'nullable|digits:10',
            'address' => 'required|string',
            'area_id' => 'nullable|exists:areas,id',
            'city' => 'required|string|max:100',
            'state' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:10',
            'photo' => 'nullable|image|max:2048',
        ]);

        $fullName = trim(preg_replace('/\s+/', ' ', implode(' ', array_filter([$request->first_name, $request->middle_name, $request->last_name]))));

        // Update User name
        $user->update([
            'name' => $fullName,
        ]);

        // Upload new photo if provided
        $photoPath = $profile->photo_path;
        if ($request->hasFile('photo')) {
            // Delete old photo
            if ($profile->photo_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($profile->photo_path) && !str_starts_with($profile->photo_path, 'http')) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($profile->photo_path);
            }
            $photoPath = $request->file('photo')->store('registrations/photos', 'public');
        }

        $pincode = $request->pincode ?: $profile->pincode;
        if ($request->filled('area_id')) {
            $selectedArea = \App\Models\Area::find($request->area_id);
            if ($selectedArea && $selectedArea->pincode) {
                $pincode = $selectedArea->pincode;
            }
        }

        // Update Profile
        $profile->update([
            'first_name' => $request->first_name,
            'middle_name' => $request->middle_name,
            'last_name' => $request->last_name,
            'father_member_id' => $request->father_member_id,
            'gender' => $request->gender,
            'dob' => $request->dob,
            'blood_group' => $request->blood_group,
            'education' => $request->education,
            'occupation' => $request->occupation,
            'phone' => $request->phone,
            'whatsapp' => $request->phone,
            'address' => $request->address,
            'area_id' => $request->area_id,
            'city' => $request->city,
            'state' => 'Gujarat',
            'pincode' => $pincode,
            'photo_path' => $photoPath,
        ]);

        session()->flash('success', 'Profile updated successfully.');
        session()->save();
        return redirect()->back();
    }

    /**
     * Update Password
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = auth()->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return redirect()->back()->withErrors(['current_password' => 'Current password does not match our records.']);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        session()->flash('success', 'Password updated successfully.');
        session()->save();
        return redirect()->back();
    }

    /**
     * View/Print Membership Card
     */
    public function membershipCard()
    {
        $user = auth()->user();
        $profile = $user->memberProfile;
        
        if (!$profile) {
            return redirect()->route('member.dashboard')->with('error', 'Profile not found.');
        }

        return view('member.card', compact('user', 'profile'));
    }

    /**
     * Account Settings Form (Email/Password Update)
     */
    public function accountSettings()
    {
        $user = auth()->user();
        return view('member.account_settings', compact('user'));
    }

    /**
     * Send OTP for Email Update
     */
    public function sendEmailOtp(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'email' => ['required', 'email', Rule::unique('users', 'email')->whereNull('deleted_at')->ignore($user->id)],
        ], [
            'email.unique' => 'This email address is already in use by another account.',
        ]);

        if ($request->email === $user->email) {
            return redirect()->back()->withErrors(['email' => 'This is already your current email address.']);
        }

        $otp = (string) mt_rand(100000, 999999);

        // Store OTP details in session
        session([
            'pending_email' => $request->email,
            'email_otp_code' => $otp,
            'email_otp_expires' => now()->addMinutes(15),
        ]);

        // Log OTP code for local debugging/testing
        \Illuminate\Support\Facades\Log::info("Email Update OTP generated for {$request->email}: {$otp}");

        // Send Email
        try {
            Mail::to($request->email)->send(new VerifyEmailOtpMail($otp, $request->email));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to send Email OTP: " . $e->getMessage());
            return redirect()->back()->withErrors(['email' => 'Failed to send verification email. Please check your mail settings.']);
        }

        session()->flash('success', 'OTP sent successfully.');
        session()->flash('success_otp', 'A 6-digit verification code has been sent to ' . $request->email . '. Please enter it below to confirm.');
        session()->save();
        return redirect()->back();
    }

    /**
     * Verify OTP and Update Email Address
     */
    public function verifyEmailOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|size:6',
        ]);

        $user = auth()->user();
        $pendingEmail = session('pending_email');
        $otpCode = session('email_otp_code');
        $expiresAt = session('email_otp_expires');

        if (!$pendingEmail || !$otpCode || !$expiresAt) {
            return redirect()->back()->withErrors(['otp' => 'No pending email change request found. Please request a new OTP.']);
        }

        if (now()->greaterThan($expiresAt)) {
            session()->forget(['pending_email', 'email_otp_code', 'email_otp_expires']);
            return redirect()->back()->withErrors(['otp' => 'The verification code has expired. Please request a new code.']);
        }

        if ($request->otp !== $otpCode) {
            return redirect()->back()->withErrors(['otp' => 'The verification code you entered is invalid.']);
        }

        // Check uniqueness once more before updating
        if (User::where('email', $pendingEmail)->where('id', '!=', $user->id)->exists()) {
            session()->forget(['pending_email', 'email_otp_code', 'email_otp_expires']);
            return redirect()->back()->withErrors(['otp' => 'This email address is already taken. Please try another one.']);
        }

        // Perform the update
        $user->update([
            'email' => $pendingEmail,
        ]);

        // Clear session
        session()->forget(['pending_email', 'email_otp_code', 'email_otp_expires', 'success_otp']);

        session()->flash('success', 'Your login email address has been updated successfully.');
        session()->save();
        return redirect()->route('member.account.settings');
    }

    /**
     * Cancel Pending Email Change Request
     */
    public function cancelEmailOtp()
    {
        session()->forget(['pending_email', 'email_otp_code', 'email_otp_expires', 'success_otp']);
        return redirect()->route('member.account.settings');
    }

    /**
     * Display Member Directory (List of approved community members)
     */
    public function directory(Request $request)
    {
        $query = User::onlyMembers()
            ->where('status', 'approved')
            ->with(['memberProfile.area']);

        if ($request->filled('search')) {
            $search = trim($request->search);
            $words = array_values(array_filter(preg_split('/\s+/', $search)));

            $query->where(function ($q) use ($search, $words) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('member_code', 'like', "%{$search}%")
                    ->orWhereHas('memberProfile', function ($sub) use ($search) {
                        $sub->where('first_name', 'like', "%{$search}%")
                            ->orWhere('middle_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%")
                            ->orWhere('city', 'like', "%{$search}%")
                            ->orWhere('state', 'like', "%{$search}%")
                            ->orWhere('address', 'like', "%{$search}%")
                            ->orWhereHas('area', function ($a) use ($search) {
                                $a->where('name', 'like', "%{$search}%")
                                  ->orWhere('pincode', 'like', "%{$search}%");
                            });
                    });

                if (count($words) > 1) {
                    $q->orWhere(function ($multiQ) use ($words) {
                        foreach ($words as $word) {
                            $multiQ->where(function ($wordQ) use ($word) {
                                $wordQ->where('name', 'like', "%{$word}%")
                                    ->orWhere('member_code', 'like', "%{$word}%")
                                    ->orWhereHas('memberProfile', function ($mp) use ($word) {
                                        $mp->where('first_name', 'like', "%{$word}%")
                                            ->orWhere('middle_name', 'like', "%{$word}%")
                                            ->orWhere('last_name', 'like', "%{$word}%")
                                            ->orWhere('phone', 'like', "%{$word}%")
                                            ->orWhere('city', 'like', "%{$word}%")
                                            ->orWhereHas('area', function ($aSub) use ($word) {
                                                $aSub->where('name', 'like', "%{$word}%")
                                                     ->orWhere('pincode', 'like', "%{$word}%");
                                            });
                                    });
                            });
                        }
                    });
                }
            });
        }

        if ($request->filled('area_id')) {
            $areaId = $request->area_id;
            $query->whereHas('memberProfile', function ($sub) use ($areaId) {
                $sub->where('area_id', $areaId);
            });
        }

        $members = $query->orderBy('name', 'asc')->paginate(18)->withQueryString();
        $areas = \App\Models\Area::orderBy('name')->get();

        return view('member.directory', compact('members', 'areas'));
    }
}
