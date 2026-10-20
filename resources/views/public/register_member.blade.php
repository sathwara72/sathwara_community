@extends('layouts.public')

@section('content')
{{-- @include('partials.page_header', [
    'title' => 'Membership Registration',
    'subtitle' => 'Join the Satwara Social Community Network',
    'breadcrumb' => 'Member Registration'
]) --}}

<!-- Registration Body -->
<section class="relative overflow-hidden py-4 bg-slate-50/50">
    @include('partials.auth_background', ['variant' => 'member'])
    <div class="relative z-10 max-w-6xl mx-auto px-3 sm:px-4">
        <!-- Registration Type Heading -->
        @include('partials.registration_hero', ['variant' => 'member'])

        <!-- Member / Business Registration Switch -->
        <div class="max-w-md mx-auto mb-5 shadow-lg rounded-xl">
            @include('partials.auth_type_tabs', ['active' => 'member', 'mode' => 'register'])
        </div>

        
        <div class="bg-white border border-slate-200/80 rounded-2xl p-4 md:p-5 shadow-xl" style="border-top: 4px solid #dc2626;">
            
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
                            <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">{{ __('messages.member_email_id') }} <span class="text-rose-500">*</span></label>
                            <input type="email" id="emailInput" name="email" value="{{ old('email') }}" required class="w-full text-sm font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none transition">
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
                    <p id="otpAlertMessage" class="text-xs text-slate-600 mt-1 leading-relaxed" style="white-space:pre-line;"></p>
                </div>
            </div>
            <div class="flex justify-end pt-2">
                <button id="otpModalCloseBtn" type="button" class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white font-extrabold text-xs rounded-xl transition-all active:scale-95 shadow cursor-pointer">{{ __('messages.ok_got_it') }}</button>
            </div>
        </div>
    </div>
</div>


@if(($signupFee ?? 1000) > 0)
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
@endif
<script>
    window.MemberRegisterConfig = {{ \Illuminate\Support\Js::from([
        'csrf' => csrf_token(),
        'feeRequired' => ($signupFee ?? 1000) > 0,
        'feePaise' => (int) round(($signupFee ?? 1000) * 100),
        'razorpayKey' => $razorpayKeyId ?? '',
        'appName' => config('app.name', 'Shree Satwara Gnati Mandal, Ahmedabad'),
        'urls' => ['submit' => route('register.member.submit'), 'preValidate' => route('register.member.pre_validate')],
        'i18n' => [
            'mobile10' => __('messages.mobile_10_digits_required'),
            'invalidMobile' => __('messages.invalid_mobile'),
            'passwordMin8' => __('messages.password_min_8'),
            'invalidPassword' => __('messages.invalid_password'),
            'passwordConfirmMismatch' => __('messages.password_confirmation_mismatch'),
            'passwordMismatch' => __('messages.password_mismatch'),
            'validating' => __('messages.validating'),
            'correctErrors' => __('messages.please_correct_errors'),
            'networkRetry' => __('messages.network_error_retry'),
            'networkError' => __('messages.network_error'),
        ],
    ]) }};
</script>
<script src="{{ asset('js/register-member.js') }}?v={{ filemtime(public_path('js/register-member.js')) }}"></script>
@endsection


