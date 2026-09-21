@extends('layouts.public')

@section('content')
{{-- @include('partials.page_header', [
    'title' => 'Membership Registration',
    'subtitle' => 'Join the Satwara Social Community Network',
    'breadcrumb' => 'Member Registration'
]) --}}

<!-- Registration Body -->
<section class="py-4 bg-slate-50/50">
    <div class="max-w-6xl mx-auto px-3 sm:px-4">
        
        <div class="bg-white border border-slate-200/80 rounded-lg p-4 md:p-5 shadow-sm">
            
            <!-- Form Header & Guidance -->
            <div class="mb-5 border-b border-slate-100 pb-3 flex items-center justify-between flex-wrap gap-2">
                <div>
                    <h2 class="text-base sm:text-lg font-bold text-slate-800">{{ __('messages.member_registration_form') }}</h2>
                    <p class="text-xs text-slate-500 mt-0.5">{{ __('messages.fill_registration_details') }}</p>
                </div>
                <span class="text-xs font-bold text-slate-500 bg-slate-100 px-3 py-1 rounded-full"><span class="text-rose-500">*</span> {{ __('messages.required_fields') }}</span>
            </div>

            <!-- Errors Alert -->
            @if ($errors->any())
                <div class="mb-6 p-4 bg-rose-50 border border-rose-100 text-rose-800 rounded-xl text-sm">
                    <p class="font-bold mb-1">{{ __('messages.please_correct_errors') }}</p>
                    <ul class="list-disc pl-4 text-xs space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('register.member.submit') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <!-- SECTION 1: PERSONAL DETAILS -->
                <div>
                    <div class="flex items-center space-x-2 border-b border-slate-100 pb-2.5 mb-4">
                        <span class="w-5 h-5 rounded-md bg-primary-50 text-primary-600 font-black text-xs flex items-center justify-center">1</span>
                        <h3 class="text-xs sm:text-sm font-bold text-slate-800 uppercase tracking-wider">{{ __('messages.personal_details') }}</h3>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">{{ __('messages.first_name') }} <span class="text-rose-500">*</span></label>
                            <input type="text" name="first_name" value="{{ old('first_name') }}" required class="w-full text-sm font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none transition">
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">{{ __('messages.father_middle_name') }} <span class="text-rose-500">*</span></label>
                            <input type="text" name="middle_name" value="{{ old('middle_name') }}" required class="w-full text-sm font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none transition">
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">{{ __('messages.surname_last_name') }} <span class="text-rose-500">*</span></label>
                            <input type="text" name="last_name" value="{{ old('last_name') }}" required class="w-full text-sm font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none transition">
                        </div>

                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">{{ __('messages.gender') }}</label>
                            <select name="gender" class="w-full text-sm font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none transition">
                                <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>{{ __('messages.gender_male') }}</option>
                                <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>{{ __('messages.gender_female') }}</option>
                                <option value="Other" {{ old('gender') == 'Other' ? 'selected' : '' }}>{{ __('messages.gender_other') }}</option>
                            </select>
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">{{ __('messages.dob') }}</label>
                            <input type="date" name="dob" value="{{ old('dob') }}" class="w-full text-sm font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none transition">
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">{{ __('messages.blood_group') }}</label>
                            <select name="blood_group" class="w-full text-sm font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none transition">
                                <option value="">-- {{ __('messages.select_blood_group') }} --</option>
                                <option value="A+" {{ old('blood_group') == 'A+' ? 'selected' : '' }}>A+</option>
                                <option value="A-" {{ old('blood_group') == 'A-' ? 'selected' : '' }}>A-</option>
                                <option value="B+" {{ old('blood_group') == 'B+' ? 'selected' : '' }}>B+</option>
                                <option value="B-" {{ old('blood_group') == 'B-' ? 'selected' : '' }}>B-</option>
                                <option value="AB+" {{ old('blood_group') == 'AB+' ? 'selected' : '' }}>AB+</option>
                                <option value="AB-" {{ old('blood_group') == 'AB-' ? 'selected' : '' }}>AB-</option>
                                <option value="O+" {{ old('blood_group') == 'O+' ? 'selected' : '' }}>O+</option>
                                <option value="O-" {{ old('blood_group') == 'O-' ? 'selected' : '' }}>O-</option>
                            </select>
                        </div>

                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">{{ __('messages.education') }}</label>
                            <input type="text" name="education" value="{{ old('education') }}" placeholder="{{ __('messages.education_placeholder') }}" class="w-full text-sm font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none transition">
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">{{ __('messages.occupation_details_label') }}</label>
                            <input type="text" name="occupation" value="{{ old('occupation') }}" placeholder="{{ __('messages.occupation_placeholder') }}" class="w-full text-sm font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none transition">
                        </div>
                        <div class="space-y-1"
                             x-data="{
                                 fatherCode: '{{ old('father_member_id', '') }}',
                                 searching: false,
                                 status: 'idle',
                                 statusMsg: '',
                                 async checkFather() {
                                     const code = this.fatherCode.trim();
                                     if (!code) {
                                         this.status = 'idle';
                                         this.statusMsg = '';
                                         return;
                                     }
                                     this.searching = true;
                                     this.status = 'searching';
                                     try {
                                         const res = await fetch(`{{ route('api.lookup_father_member') }}?code=${encodeURIComponent(code)}`);
                                         const data = await res.json();
                                         if (data.found) {
                                             this.status = 'found';
                                             this.statusMsg = data.message;
                                         } else {
                                             this.status = 'not_found';
                                             this.statusMsg = data.message;
                                         }
                                     } catch (e) {
                                         this.status = 'not_found';
                                         this.statusMsg = '{{ app()->getLocale() == "gu" ? "ચકાસણીમાં ભૂલ આવી" : "Error checking member code" }}';
                                     } finally {
                                         this.searching = false;
                                     }
                                 }
                             }">
                            <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">{{ __('messages.father_member_id') }} <span class="text-xs text-slate-400 font-normal">{{ __('messages.if_registered') }}</span></label>
                            <div class="flex items-center gap-1.5">
                                <input type="text" name="father_member_id" 
                                    x-model="fatherCode"
                                    @keydown.enter.prevent="checkFather()"
                                    placeholder="{{ __('messages.father_id_placeholder') }}" 
                                    class="w-full text-sm font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none transition">
                                <button type="button" 
                                        @click="checkFather()"
                                        :disabled="searching"
                                        class="inline-flex items-center justify-center gap-1 px-3.5 py-2.5 bg-primary-50 hover:bg-primary-100 text-primary-700 border border-primary-200 font-bold text-xs rounded-lg transition-all cursor-pointer shrink-0 disabled:opacity-50">
                                    <template x-if="searching">
                                        <svg class="w-3.5 h-3.5 animate-spin text-primary-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </template>
                                    <template x-if="!searching">
                                        <svg class="w-3.5 h-3.5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                        </svg>
                                    </template>
                                    <span>{{ __('messages.search') }}</span>
                                </button>
                            </div>
                            <div x-show="status === 'found'" class="text-[11px] font-bold text-emerald-600 flex items-center gap-1 mt-1" x-cloak>
                                <span>✓</span>
                                <span x-text="statusMsg"></span>
                            </div>
                            <div x-show="status === 'not_found'" class="text-[11px] font-bold text-rose-600 flex items-center gap-1 mt-1" x-cloak>
                                <span>✕</span>
                                <span x-text="statusMsg"></span>
                            </div>
                        </div>

                        <!-- Mobile Number — moved to Personal Details -->
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">{{ __('messages.mobile_whatsapp_number') }} <span class="text-rose-500">*</span></label>
                            <input type="text" name="phone" value="{{ old('phone') }}" required minlength="10" maxlength="10" pattern="[0-9]{10}" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)" class="w-full text-sm font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none transition">
                        </div>

                        <!-- Profile Photo Upload -->
                        <div class="space-y-1 sm:col-span-3">
                            <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">{{ __('messages.profile_photo') ?? 'Profile Photo' }} <span class="text-xs text-slate-400 font-normal">(JPG, PNG max 2MB)</span></label>
                            <input type="file" name="photo" accept="image/*" class="w-full text-xs font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none transition file:mr-3 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-bold file:bg-primary-50 file:text-primary-600 hover:file:bg-primary-100 cursor-pointer">
                        </div>
                    </div>
                </div>

                <!-- SECTION 2: ADDRESS DETAILS -->
                <div>
                    <div class="flex items-center space-x-2 border-b border-slate-100 pb-2.5 mb-4">
                        <span class="w-5 h-5 rounded-md bg-primary-50 text-primary-600 font-black text-xs flex items-center justify-center">2</span>
                        <h3 class="text-xs sm:text-sm font-bold text-slate-800 uppercase tracking-wider">{{ __('messages.address_details') }}</h3>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div class="space-y-1 sm:col-span-2">
                            <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">{{ __('messages.address') }} <span class="text-rose-500">*</span></label>
                            <textarea name="address" rows="2" required class="w-full text-sm font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none transition">{{ old('address') }}</textarea>
                        </div>
                        <div class="space-y-1 sm:col-span-1">
                            <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">{{ __('messages.area') }} <span class="text-rose-500">*</span></label>
                            <select name="area_id" required class="w-full text-sm font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none transition">
                                <option value="">-- {{ __('messages.select_area') }} --</option>
                                @foreach($areas as $area)
                                    <option value="{{ $area->id }}" {{ old('area_id') == $area->id ? 'selected' : '' }}>{{ $area->name }}{{ $area->pincode ? ' (' . $area->pincode . ')' : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-1 sm:col-span-1">
                            <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">{{ __('messages.city') }} <span class="text-rose-500">*</span></label>
                            <input type="text" name="city" value="{{ old('city', 'Ahmedabad') }}" readonly required class="w-full text-sm font-semibold px-3 py-2 bg-slate-100 text-slate-700 border border-slate-200 rounded-lg focus:bg-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none transition cursor-not-allowed">
                            <input type="hidden" name="state" value="{{ old('state', 'Gujarat') }}">
                            <input type="hidden" name="pincode" value="{{ old('pincode') }}">
                        </div>
                    </div>
                </div>

                <!-- SECTION 3: LOGIN ACCOUNT -->
                <div>
                    <div class="flex items-center space-x-2 border-b border-slate-100 pb-2.5 mb-4">
                        <span class="w-5 h-5 rounded-md bg-primary-50 text-primary-600 font-black text-xs flex items-center justify-center">3</span>
                        <h3 class="text-xs sm:text-sm font-bold text-slate-800 uppercase tracking-wider">{{ __('messages.contact_login_account') }}</h3>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                        <div class="space-y-1">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">{{ __('messages.member_email_id') }} <span class="text-rose-500">*</span></label>
                                <span id="emailVerifiedBadge" class="hidden text-[11px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">
                                    ✓ {{ __('messages.email_verified') }}
                                </span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <input type="email" id="emailInput" name="email" value="{{ old('email') }}" required class="w-full text-sm font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none transition">
                                <button type="button" id="sendOtpBtn" class="shrink-0 px-3 py-2 bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs rounded-lg shadow transition-all active:scale-95 cursor-pointer">
                                    <span id="sendOtpBtnText">{{ __('messages.send_otp') }}</span>
                                </button>
                                <button type="button" id="changeEmailBtn" class="hidden shrink-0 px-2.5 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold text-xs rounded-lg transition-all cursor-pointer">
                                    {{ __('messages.change_email') }}
                                </button>
                            </div>
                        </div>

                        <div class="space-y-1" x-data="{ showPass: false }">
                            <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">{{ __('messages.password') }} <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <input :type="showPass ? 'text' : 'password'" name="password" required minlength="8" class="w-full text-sm font-semibold pl-3 pr-9 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none transition">
                                <button type="button" @click="showPass = !showPass" 
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none cursor-pointer"
                                    title="{{ __('messages.toggle_password_visibility') }}">
                                    <svg x-show="!showPass" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <svg x-show="showPass" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858-5.908a10.018 10.018 0 013.682-.863c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21M3 3l18 18"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div class="space-y-1" x-data="{ showConfirmPass: false }">
                            <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">{{ __('messages.confirm_password') }} <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <input :type="showConfirmPass ? 'text' : 'password'" name="password_confirmation" required minlength="8" class="w-full text-sm font-semibold pl-3 pr-9 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none transition">
                                <button type="button" @click="showConfirmPass = !showConfirmPass" 
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none cursor-pointer"
                                    title="{{ __('messages.toggle_password_visibility') }}">
                                    <svg x-show="!showConfirmPass" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <svg x-show="showConfirmPass" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858-5.908a10.018 10.018 0 013.682-.863c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21M3 3l18 18"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECTION 4: PAYMENT SUMMARY & SUBMISSION -->
                <div>
                    <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
                    @if(($signupFee ?? 1000) > 0)
                        <div class="bg-primary-50/90 border border-primary-200/80 rounded-xl p-3.5 mb-3 flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <span class="text-lg">💳</span>
                                <div>
                                    <h4 class="text-xs sm:text-sm font-bold text-primary-900">{{ __('messages.membership_signup_fee') }}</h4>
                                    <p class="text-xs font-medium text-primary-700">{{ __('messages.payment_processed_securely') }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="text-base font-black text-primary-700">₹{{ number_format($signupFee ?? 1000) }}</span>
                                <span class="block text-xs font-extrabold text-slate-400">{{ __('messages.one_time_fee') }}</span>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Submit Button -->
                <div class="pt-3 border-t border-slate-100 flex items-center justify-end">
                    <button type="submit" id="submitMemberBtn" class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 bg-primary-600 hover:bg-primary-700 font-extrabold text-xs sm:text-sm text-white uppercase tracking-wider rounded-xl transition-all active:scale-95 shadow-xs cursor-pointer">
                        <span>{{ __('messages.pay_and_register_member', ['amount' => number_format($signupFee ?? 1000)]) }}</span> &rarr;
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

<!-- Custom Alert Modal (warnings/errors) -->
<div id="otpAlertModal" class="fixed inset-0 items-center justify-center hidden" style="display: none !important; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; z-index: 999999 !important;" role="dialog" aria-modal="true" aria-labelledby="otpAlertTitle">
    <div id="otpModalBackdrop" style="position: absolute; inset: 0; background-color: rgba(0, 0, 0, 0.65); backdrop-filter: blur(4px);"></div>
    <div class="relative w-full max-w-sm mx-4 bg-white rounded-2xl shadow-2xl overflow-hidden transform transition-all duration-300 scale-95 opacity-0" id="otpModalPanel">
        <div id="otpModalAccent" class="h-1.5 w-full bg-rose-500"></div>
        <div class="p-6">
            <div class="flex items-start gap-3 mb-3">
                <div id="otpModalIcon" class="shrink-0 w-10 h-10 rounded-xl flex items-center justify-center text-xl bg-rose-100 text-rose-600">⚠️</div>
                <div>
                    <h3 id="otpAlertTitle" class="text-sm font-extrabold text-slate-900 leading-tight">{{ __('messages.verification_required') }}</h3>
                    <p id="otpAlertMessage" class="text-xs text-slate-600 mt-1 leading-relaxed"></p>
                </div>
            </div>
            <div class="flex justify-end pt-2">
                <button id="otpModalCloseBtn" type="button" class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white font-extrabold text-xs rounded-xl transition-all active:scale-95 shadow cursor-pointer">{{ __('messages.ok_got_it') }}</button>
            </div>
        </div>
    </div>
</div>

<!-- OTP Entry Popup Modal -->
<div id="otpEntryModal" class="fixed inset-0 items-center justify-center hidden" style="display: none !important; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; z-index: 999998 !important;" role="dialog" aria-modal="true">
    <!-- Backdrop -->
    <div style="position: absolute; inset: 0; background-color: rgba(0, 0, 0, 0.65); backdrop-filter: blur(4px);"></div>
    <!-- Panel -->
    <div class="relative w-full max-w-sm mx-4 bg-white rounded-2xl shadow-2xl overflow-hidden transform transition-all duration-300 scale-95 opacity-0" id="otpEntryPanel">

        <!-- Header (Red theme matching site branding) -->
        <div style="background: linear-gradient(135deg, #dc2626 0%, #dc2626 100%); padding: 20px 24px;">
            <div class="flex items-center gap-3">
                <div style="width:44px;height:44px;background:rgba(255,255,255,0.18);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0;">📧</div>
                <div>
                    <h3 style="color:#fff;font-size:15px;font-weight:800;margin:0;letter-spacing:0.02em;">{{ __('messages.email_verification') }}</h3>
                    <p style="color:#fecdd3;font-size:11px;margin:3px 0 0 0;">{{ __('messages.otp_sent_to_inbox_desc') }}</p>
                </div>
            </div>
        </div>

        <!-- Body -->
        <div class="p-5 space-y-4">

            <!-- Email target -->
            <div class="text-center">
                <p class="text-xs text-slate-500">{{ __('messages.verification_code_sent_to') }}</p>
                <p id="otpTargetEmail" class="text-sm font-extrabold break-all mt-0.5" style="color:#dc2626;"></p>
            </div>

            <!-- OTP Input -->
            <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate-600 uppercase tracking-widest block">{{ __('messages.enter_otp') }} <span class="text-rose-500">*</span></label>
                <input type="text" id="otpInput" inputmode="numeric" maxlength="6" placeholder="0  0  0  0  0  0"
                    style="width:100%;font-size:22px;font-weight:900;letter-spacing:0.35em;text-align:center;padding:12px 16px;background:#f8fafc;border:2px solid #e2e8f0;border-radius:12px;outline:none;transition:border-color 0.2s,box-shadow 0.2s;box-sizing:border-box;"
                    onfocus="this.style.borderColor='#dc2626';this.style.boxShadow='0 0 0 3px rgba(220,38,38,0.15)'"
                    onblur="this.style.borderColor='#e2e8f0';this.style.boxShadow='none'">
                <div id="otpStatusMsg" class="text-xs font-semibold min-h-[18px] text-center"></div>
            </div>

            <!-- Resend -->
            <p class="text-center" style="font-size:11px;color:#94a3b8;margin:0;">
                {{ __('messages.did_not_receive_code') }} &nbsp;
                <button type="button" id="resendOtpBtn" style="font-weight:700;color:#dc2626;text-decoration:underline;cursor:pointer;background:none;border:none;padding:0;outline:none;">{{ __('messages.resend_otp') }}</button>
                <span id="otpResendTimer" style="color:#94a3b8;font-weight:600;"></span>
            </p>
        </div>

        <!-- Footer Buttons -->
        <div style="display:flex;gap:10px;padding:0 20px 20px;">
            <button type="button" id="cancelOtpModalBtn"
                style="flex:1;padding:10px;border:1.5px solid #e2e8f0;background:#f8fafc;color:#475569;font-weight:700;font-size:12px;border-radius:10px;cursor:pointer;transition:background 0.15s;"
                onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#f8fafc'">
                {{ __('messages.cancel') }}
            </button>
            <button type="button" id="verifyOtpBtn"
                style="flex:1;padding:10px;background:#16a34a;color:#fff;font-weight:800;font-size:12px;border-radius:10px;cursor:pointer;border:none;box-shadow:0 2px 8px rgba(22,163,74,0.3);transition:background 0.15s;"
                onmouseover="this.style.background='#15803d'" onmouseout="this.style.background='#16a34a'">
                <span id="verifyOtpBtnText">{{ __('messages.verify_otp') }}</span>
            </button>
        </div>

    </div>
</div>


@if(($signupFee ?? 1000) > 0)
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
@endif
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('memberRegisterForm') || document.querySelector('form[action="{{ route('register.member.submit') }}"]');
    if (!form) return;

    form.id = 'memberRegisterForm';

    // ── Custom Modal Helper ──────────────────────────────────────
    const otpAlertModal = document.getElementById('otpAlertModal');
    const otpModalPanel = document.getElementById('otpModalPanel');
    const otpModalAccent = document.getElementById('otpModalAccent');
    const otpModalIcon = document.getElementById('otpModalIcon');
    const otpAlertTitle = document.getElementById('otpAlertTitle');
    const otpAlertMessage = document.getElementById('otpAlertMessage');
    const otpModalCloseBtn = document.getElementById('otpModalCloseBtn');
    const otpModalBackdrop = document.getElementById('otpModalBackdrop');

    // Move modals to body so they sit above header regardless of container hierarchy
    if (otpAlertModal && otpAlertModal.parentElement !== document.body) {
        document.body.appendChild(otpAlertModal);
    }
    const otpEntryModal   = document.getElementById('otpEntryModal');
    if (otpEntryModal && otpEntryModal.parentElement !== document.body) {
        document.body.appendChild(otpEntryModal);
    }

    const modalConfig = {
        warning: { accent: 'bg-amber-500', icon: '⚠️', iconBg: 'bg-amber-100 text-amber-600', btnBg: 'bg-amber-500 hover:bg-amber-600', title: 'Warning' },
        error:   { accent: 'bg-rose-500',  icon: '❌', iconBg: 'bg-rose-100 text-rose-600',   btnBg: 'bg-rose-600 hover:bg-rose-700',   title: 'Error' },
        success: { accent: 'bg-emerald-500', icon: '✅', iconBg: 'bg-emerald-100 text-emerald-600', btnBg: 'bg-emerald-600 hover:bg-emerald-700', title: 'Success' },
        info:    { accent: 'bg-primary-500', icon: 'ℹ️', iconBg: 'bg-primary-100 text-primary-600', btnBg: 'bg-primary-600 hover:bg-primary-700', title: 'Info' },
    };

    function showModal(message, type = 'warning', title = null) {
        const cfg = modalConfig[type] || modalConfig.warning;
        // Set accent color
        otpModalAccent.className = 'h-1.5 w-full ' + cfg.accent;
        // Set icon
        otpModalIcon.className = 'shrink-0 w-10 h-10 rounded-xl flex items-center justify-center text-xl ' + cfg.iconBg;
        otpModalIcon.textContent = cfg.icon;
        // Set btn color
        otpModalCloseBtn.className = 'px-5 py-2 font-extrabold text-xs text-white rounded-xl transition-all active:scale-95 shadow cursor-pointer ' + cfg.btnBg;
        // Set content
        otpAlertTitle.textContent = title || cfg.title;
        otpAlertMessage.textContent = message;
        // Show
        otpAlertModal.style.setProperty('display', 'flex', 'important');
        otpAlertModal.classList.remove('hidden');
        setTimeout(() => {
            otpModalPanel.classList.remove('scale-95', 'opacity-0');
            otpModalPanel.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function closeModal() {
        otpModalPanel.classList.remove('scale-100', 'opacity-100');
        otpModalPanel.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            otpAlertModal.classList.add('hidden');
            otpAlertModal.style.setProperty('display', 'none', 'important');
        }, 200);
    }

    if (otpModalCloseBtn) otpModalCloseBtn.addEventListener('click', closeModal);
    if (otpModalBackdrop) otpModalBackdrop.addEventListener('click', closeModal);
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });
    // ─────────────────────────────────────────────────────────────

    // Email OTP Variables
    const emailInput      = document.getElementById('emailInput');
    const sendOtpBtn      = document.getElementById('sendOtpBtn');
    const sendOtpBtnText  = document.getElementById('sendOtpBtnText');
    const changeEmailBtn  = document.getElementById('changeEmailBtn');
    const emailVerifiedBadge = document.getElementById('emailVerifiedBadge');

    // OTP Entry Modal elements
    const otpEntryPanel   = document.getElementById('otpEntryPanel');
    const otpTargetEmail  = document.getElementById('otpTargetEmail');
    const otpInput        = document.getElementById('otpInput');
    const verifyOtpBtn    = document.getElementById('verifyOtpBtn');
    const verifyOtpBtnText= document.getElementById('verifyOtpBtnText');
    const cancelOtpModalBtn = document.getElementById('cancelOtpModalBtn');
    const resendOtpBtn    = document.getElementById('resendOtpBtn');
    const otpResendTimer  = document.getElementById('otpResendTimer');
    const otpStatusMsg    = document.getElementById('otpStatusMsg');

    let isEmailVerified = false;
    let resendInterval  = null;

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

    // ── OTP Modal open / close ────────────────────────────────────
    function openOtpModal(email) {
        if (otpTargetEmail) otpTargetEmail.textContent = email;
        if (otpInput) otpInput.value = '';
        otpEntryModal.style.setProperty('display', 'flex', 'important');
        otpEntryModal.classList.remove('hidden');
        setTimeout(() => {
            otpEntryPanel.classList.remove('scale-95', 'opacity-0');
            otpEntryPanel.classList.add('scale-100', 'opacity-100');
            if (otpInput) otpInput.focus();
        }, 10);
    }

    function closeOtpModal() {
        otpEntryPanel.classList.remove('scale-100', 'opacity-100');
        otpEntryPanel.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            otpEntryModal.classList.add('hidden');
            otpEntryModal.style.setProperty('display', 'none', 'important');
        }, 200);
    }

    if (cancelOtpModalBtn) cancelOtpModalBtn.addEventListener('click', closeOtpModal);
    // ─────────────────────────────────────────────────────────────

    // Resend button state management
    function setResendButtonState(enabled, timeLeft = 0) {
        if (!resendOtpBtn) return;
        if (enabled) {
            resendOtpBtn.disabled = false;
            resendOtpBtn.style.color = '#dc2626';
            resendOtpBtn.style.cursor = 'pointer';
            resendOtpBtn.style.textDecoration = 'underline';
            resendOtpBtn.style.opacity = '1';
            if (otpResendTimer) otpResendTimer.textContent = '';
        } else {
            resendOtpBtn.disabled = true;
            resendOtpBtn.style.color = '#94a3b8';
            resendOtpBtn.style.cursor = 'not-allowed';
            resendOtpBtn.style.textDecoration = 'none';
            resendOtpBtn.style.opacity = '0.7';
            if (otpResendTimer) otpResendTimer.textContent = timeLeft > 0 ? ` (${timeLeft}s)` : '';
        }
    }

    function startResendTimer(seconds = 30) {
        let t = seconds;
        clearInterval(resendInterval);
        setResendButtonState(false, t);
        resendInterval = setInterval(() => {
            t--;
            if (t <= 0) {
                clearInterval(resendInterval);
                setResendButtonState(true);
            } else {
                setResendButtonState(false, t);
            }
        }, 1000);
    }

    // ── Localized Messages ───────────────────────────────────────
    const msgSendingOtp        = "{{ __('messages.sending_otp') }}";
    const msgSendingNewOtp     = "{{ __('messages.sending_new_otp') }}";
    const msgOtpSentSuccess    = "{{ __('messages.otp_sent_success') }}";
    const msgNewOtpSentSuccess = "{{ __('messages.new_otp_sent_success') }}";
    const msgEnter6Digit       = "{{ __('messages.enter_6_digit_otp_error') }}";
    const msgNetworkError      = "{{ __('messages.network_error_retry') }}";

    // ── Core send OTP function ────────────────────────────────────
    function doSendOtp(email) {
        sendOtpBtn.disabled = true;
        sendOtpBtnText.textContent = msgSendingOtp;

        fetch("{{ route('register.member.send_otp') }}", {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: JSON.stringify({ email: email })
        })
        .then(res => res.json())
        .then(data => {
            sendOtpBtnText.textContent = "{{ __('messages.send_otp') }}";
            sendOtpBtn.disabled = false;
            if (data.success) {
                openOtpModal(email);
                if (otpStatusMsg) {
                    otpStatusMsg.innerHTML = '<span style="color:#16a34a;font-weight:600;">✓ ' + (data.message || msgOtpSentSuccess) + '</span>';
                }
                startResendTimer(30);
            } else {
                showModal(data.message || "{{ __('messages.failed_to_send_otp') }}", 'error', "{{ __('messages.failed_to_send_otp') }}");
            }
        })
        .catch(() => {
            sendOtpBtn.disabled = false;
            sendOtpBtnText.textContent = "{{ __('messages.send_otp') }}";
            showModal(msgNetworkError, 'error', 'Connection Error');
        });
    }

    if (sendOtpBtn) {
        sendOtpBtn.addEventListener('click', function () {
            const email = emailInput ? emailInput.value.trim() : '';
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!email || !emailRegex.test(email)) {
                showModal("{{ __('messages.please_enter_valid_email') }}", 'warning', "{{ __('messages.please_enter_valid_email') }}");
                if (emailInput) emailInput.focus();
                return;
            }
            doSendOtp(email);
        });
    }

    // ── Resend OTP inside Modal ───────────────────────────────────
    if (resendOtpBtn) {
        resendOtpBtn.addEventListener('click', function () {
            if (resendOtpBtn.disabled) return;
            const email = emailInput ? emailInput.value.trim() : '';
            if (!email) return;

            setResendButtonState(false, 0);
            if (otpStatusMsg) {
                otpStatusMsg.innerHTML = '<span style="color:#dc2626;font-weight:600;display:inline-flex;align-items:center;gap:4px;"><svg class="animate-spin" style="width:14px;height:14px;display:inline;" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> ' + msgSendingNewOtp + '</span>';
            }

            fetch("{{ route('register.member.send_otp') }}", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify({ email: email })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (otpInput) {
                        otpInput.value = '';
                        otpInput.focus();
                    }
                    if (otpStatusMsg) {
                        otpStatusMsg.innerHTML = '<span style="color:#16a34a;font-weight:700;">✓ ' + msgNewOtpSentSuccess + '</span>';
                    }
                    startResendTimer(30);
                } else {
                    setResendButtonState(true);
                    if (otpStatusMsg) {
                        otpStatusMsg.innerHTML = '<span style="color:#dc2626;font-weight:700;">❌ ' + (data.message || "{{ __('messages.failed_to_send_otp') }}") + '</span>';
                    }
                }
            })
            .catch(() => {
                setResendButtonState(true);
                if (otpStatusMsg) {
                    otpStatusMsg.innerHTML = '<span style="color:#dc2626;font-weight:700;">❌ ' + msgNetworkError + '</span>';
                }
            });
        });
    }

    // ── Verify OTP ───────────────────────────────────────────────
    if (verifyOtpBtn) {
        verifyOtpBtn.addEventListener('click', function () {
            const email = emailInput ? emailInput.value.trim() : '';
            const otp   = otpInput ? otpInput.value.trim() : '';

            if (!otp || otp.length !== 6) {
                if (otpStatusMsg) {
                    otpStatusMsg.innerHTML = '<span style="color:#dc2626;font-weight:700;">' + msgEnter6Digit + '</span>';
                }
                if (otpInput) otpInput.focus();
                return;
            }

            verifyOtpBtn.disabled = true;
            verifyOtpBtnText.textContent = '...';

            fetch("{{ route('register.member.verify_otp') }}", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify({ email: email, otp: otp })
            })
            .then(res => res.json())
            .then(data => {
                verifyOtpBtn.disabled = false;
                verifyOtpBtnText.textContent = "{{ __('messages.verify_otp') }}";

                if (data.success) {
                    isEmailVerified = true;
                    closeOtpModal();
                    clearInterval(resendInterval);
                    emailVerifiedBadge.classList.remove('hidden');
                    changeEmailBtn.classList.remove('hidden');
                    sendOtpBtn.classList.add('hidden');
                    emailInput.readOnly = true;
                    emailInput.classList.add('bg-slate-100', 'text-slate-600', 'cursor-not-allowed');
                } else {
                    if (otpStatusMsg) {
                        otpStatusMsg.innerHTML = '<span style="color:#dc2626;font-weight:700;">' + (data.message || "{{ __('messages.otp_invalid') }}") + '</span>';
                    }
                }
            })
            .catch(() => {
                verifyOtpBtn.disabled = false;
                verifyOtpBtnText.textContent = "{{ __('messages.verify_otp') }}";
                if (otpStatusMsg) {
                    otpStatusMsg.innerHTML = '<span style="color:#dc2626;font-weight:700;">' + msgNetworkError + '</span>';
                }
            });
        });
    }

    // Allow Enter key in OTP input to trigger verify
    if (otpInput) {
        otpInput.addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); verifyOtpBtn?.click(); } });
        otpInput.addEventListener('input', function () { this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6); });
    }

    document.addEventListener('keydown', e => { if (e.key === 'Escape') { closeOtpModal(); closeModal(); } });

    if (changeEmailBtn) {
        changeEmailBtn.addEventListener('click', function () {
            isEmailVerified = false;
            emailInput.readOnly = false;
            emailInput.classList.remove('bg-slate-100', 'text-slate-600', 'cursor-not-allowed');
            emailVerifiedBadge.classList.add('hidden');
            changeEmailBtn.classList.add('hidden');
            sendOtpBtn.classList.remove('hidden');
            sendOtpBtn.disabled = false;
            otpTimerText.textContent = '';
        });
    }

    // ── Prevent Razorpay re-opening on page refresh ──────────
    sessionStorage.removeItem('mem_rzp_inprogress');

    // Form submit listener
    form.addEventListener('submit', async function (e) {
        // Block if this is a browser-replayed POST (F5 after Razorpay opened)
        if (sessionStorage.getItem('mem_rzp_inprogress') === '1') {
            e.preventDefault();
            sessionStorage.removeItem('mem_rzp_inprogress');
            return false;
        }
        // 1. HTML5 validation check
        if (!form.checkValidity()) {
            e.preventDefault();
            form.reportValidity();
            return false;
        }

        // 2. Email OTP verification check
        if (!isEmailVerified) {
            e.preventDefault();
            showModal("{{ __('messages.please_verify_email') }}", 'warning', "{{ __('messages.verification_required') }}");
            if (!sendOtpBtn.classList.contains('hidden')) {
                sendOtpBtn.focus();
            } else if (otpInput) {
                otpInput.focus();
            }
            return false;
        }

        // 3. Explicit client validation checks
        const phone = form.querySelector('[name="phone"]')?.value.trim() || '';
        if (phone.length !== 10 || !/^\d{10}$/.test(phone)) {
            e.preventDefault();
            showModal("{{ __('messages.mobile_10_digits_required') ?? 'મોબાઈલ નંબર બરાબર ૧૦ અંકનો હોવો જરૂરી છે.' }}", 'warning', "{{ __('messages.invalid_mobile') ?? 'અમાન્ય મોબાઈલ નંબર' }}");
            form.querySelector('[name="phone"]')?.focus();
            return false;
        }

        const password = form.querySelector('[name="password"]')?.value || '';
        const passwordConfirmation = form.querySelector('[name="password_confirmation"]')?.value || '';
        if (password.length < 8) {
            e.preventDefault();
            showModal("{{ __('messages.password_min_8') ?? 'પાસવર્ડ ઓછામાં ઓછો ૮ અક્ષરનો હોવો જરૂરી છે.' }}", 'warning', "{{ __('messages.invalid_password') ?? 'અમાન્ય પાસવર્ડ' }}");
            form.querySelector('[name="password"]')?.focus();
            return false;
        }
        if (password !== passwordConfirmation) {
            e.preventDefault();
            showModal("{{ __('messages.password_confirmation_mismatch') ?? 'પાસવર્ડ અને કન્ફર્મ પાસવર્ડ સરખા નથી.' }}", 'warning', "{{ __('messages.password_mismatch') ?? 'પાસવર્ડ મેળ ખાતો નથી' }}");
            form.querySelector('[name="password_confirmation"]')?.focus();
            return false;
        }

        const paymentIdInput = document.getElementById('razorpay_payment_id');
        if (paymentIdInput && paymentIdInput.value) {
            return true; // Already paid, allow normal submission
        }

        @if(($signupFee ?? 1000) > 0)
            e.preventDefault();

            const submitBtn = document.getElementById('submitMemberBtn');
            const originalBtnHtml = submitBtn ? submitBtn.innerHTML : '';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="inline-block animate-spin mr-2">⏳</span> {{ __("messages.validating") ?? "ચકાસણી ચાલુ છે..." }}';
            }

            // 4. Server-Side Pre-Validation before opening Razorpay
            try {
                const formData = new FormData(form);
                const preValRes = await fetch("{{ route('register.member.pre_validate') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                const preValData = await preValRes.json();

                if (!preValRes.ok || !preValData.success) {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnHtml;
                    }
                    const errorsList = preValData.errors ? preValData.errors.join('<br>• ') : (preValData.message || 'વિગતો ચકાસવામાં ભૂલ આવી છે.');
                    showModal('• ' + errorsList, 'error', "{{ __('messages.please_correct_errors') }}");
                    return false;
                }
            } catch (err) {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnHtml;
                }
                showModal("{{ __('messages.network_error_retry') ?? 'સર્વર સાથે સંપર્ક કરવામાં સમસ્યા આવી. કૃપા કરીને ફરી પ્રયાસ કરો.' }}", 'error', "{{ __('messages.network_error') }}");
                return false;
            }

            // 5. Open Razorpay Gateway
            const razorpayKey = "{{ $razorpayKeyId ?? '' }}";
            const feeAmountPaise = {{ ($signupFee ?? 1000) * 100 }};
            const firstName = form.querySelector('[name="first_name"]')?.value || '';
            const lastName = form.querySelector('[name="last_name"]')?.value || '';
            const email = form.querySelector('[name="email"]')?.value || '';

            const options = {
                "key": razorpayKey || "rzp_test_key",
                "amount": feeAmountPaise,
                "currency": "INR",
                "name": "{{ config('app.name', 'Shree Satwara Gnati Mandal, Ahmedabad') }}",
                "description": "Membership Registration Fee",
                "handler": function (response) {
                    sessionStorage.removeItem('mem_rzp_inprogress');
                    paymentIdInput.value = response.razorpay_payment_id;
                    window.dispatchEvent(new CustomEvent('show-loader'));
                    form.submit();
                },
                "modal": {
                    "ondismiss": function() {
                        sessionStorage.removeItem('mem_rzp_inprogress');
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalBtnHtml;
                        }
                    }
                },
                "prefill": {
                    "name": firstName + " " + lastName,
                    "email": email,
                    "contact": phone
                },
                "theme": {
                    "color": "#2563EB"
                }
            };

            if (window.Razorpay) {
                sessionStorage.setItem('mem_rzp_inprogress', '1');
                const rzp = new Razorpay(options);
                rzp.on('payment.failed', function (response) {
                    sessionStorage.removeItem('mem_rzp_inprogress');
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnHtml;
                    }
                    showModal(response.error.description || 'પેમેન્ટ અસફળ રહ્યું. કૃપા કરીને ફરી પ્રયાસ કરો.', 'error', 'પેમેન્ટ નિષ્ફળ');
                });
                rzp.open();
            } else {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnHtml;
                }
                showModal('Razorpay Payment Gateway failed to load. Your application will be submitted without payment.', 'error', 'Payment Gateway Error');
                setTimeout(() => form.submit(), 2000);
            }
        @else
            return true;
        @endif
    });
});
</script>
@endsection


