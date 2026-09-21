@extends('layouts.public')

@section('content')
@include('partials.page_header', [
    'title' => __('messages.register_your_business'),
    'subtitle' => __('messages.promote_your_work'),
    'breadcrumb' => __('messages.business_registration_breadcrumb')
])

<!-- Form Body -->
<section class="py-6 bg-slate-50/50">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white border border-slate-200/60 rounded-2xl p-5 md:p-6 shadow-xs">
            
            @if(isset($existingBusiness) && $existingBusiness)
                <div class="mb-5 p-4 bg-amber-50 border border-amber-200 text-amber-900 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-xs">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2 font-black text-xs text-amber-900">
                            <span>⚠️</span>
                            <span>{{ __('messages.business_limit_exceeded') }}</span>
                        </div>
                        <p class="text-xs text-amber-800 font-medium">
                            {{ __('messages.business_limit_desc', ['name' => $existingBusiness->business_name, 'status' => strtoupper($existingBusiness->status)]) }}
                        </p>
                    </div>
                    <a href="{{ route('member.businesses.my') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-xl transition-all shrink-0">
                        <span>{{ __('messages.my_businesses') }}</span> &rarr;
                    </a>
                </div>
            @endif

            <!-- Validation errors -->
            @if ($errors->any())
                <div class="mb-4 p-3 bg-rose-50 border border-rose-100 text-rose-800 rounded-xl">
                    <p class="text-xs font-bold mb-2">{{ __('messages.please_correct_errors') }}</p>
                    <ul class="list-disc pl-4 text-[11px] font-medium space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('register.business.submit') }}" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-3 gap-x-4 gap-y-4">
                @csrf
                
                <!-- Row 1: Member ID & Business Name -->
                <div class="space-y-1" 
                     x-data="{ 
                         memberId: '{{ old('member_id') }}', 
                         memberStatus: '', 
                         isFound: null, 
                         loading: false,
                         checkMember() {
                             if(!this.memberId || this.memberId.trim() === '') {
                                 this.memberStatus = '';
                                 this.isFound = null;
                                 return;
                             }
                             this.loading = true;
                             fetch('{{ route('api.check_member_id') }}?member_id=' + encodeURIComponent(this.memberId))
                                 .then(res => res.json())
                                 .then(data => {
                                     this.loading = false;
                                     this.isFound = data.found;
                                     this.memberStatus = data.message;
                                 })
                                 .catch(() => { this.loading = false; });
                         }
                     }"
                     x-init="if(memberId) checkMember()">
                    <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">{{ __('messages.member_id_label') }}</label>
                    <div class="relative">
                        <input type="text" name="member_id" x-model="memberId" @input.debounce.400ms="checkMember()" placeholder="{{ __('messages.member_id_placeholder') }}" class="w-full text-sm font-semibold px-3 py-2 bg-slate-50 border rounded-lg focus:bg-white focus:ring-0 transition-colors" :class="isFound === true ? 'border-emerald-400' : (isFound === false ? 'border-rose-400' : 'border-slate-200')">
                        <span x-show="loading" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-xs text-slate-400 font-bold">{{ __('messages.checking') }}</span>
                    </div>
                    <div x-show="memberStatus" class="mt-0.5 text-xs font-bold" :class="isFound ? 'text-emerald-700' : 'text-rose-600'" x-text="memberStatus"></div>
                    @error('member_id')
                        <p class="text-xs text-rose-600 font-bold mt-0.5">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-1 md:col-span-2">
                    <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">{{ __('messages.firm_name_label') }} <span class="text-rose-500">*</span></label>
                    <input type="text" name="business_name" required value="{{ old('business_name') }}" placeholder="{{ __('messages.firm_name_placeholder') }}" class="w-full text-sm font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:border-primary-500 focus:ring-0">
                </div>

                <!-- Row 2: Owner, Category, Area -->
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">{{ __('messages.contact_person_label') }} <span class="text-rose-500">*</span></label>
                    <input type="text" name="owner_name" required value="{{ old('owner_name') }}" placeholder="{{ __('messages.contact_person_placeholder') }}" class="w-full text-sm font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:border-primary-500 focus:ring-0">
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">{{ __('messages.business_category_label') }}</label>
                    <select name="category_id" class="w-full text-sm font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:border-primary-500 focus:ring-0">
                        <option value="">{{ __('messages.select_category') }}</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">{{ __('messages.area_label') }} <span class="text-rose-500">*</span></label>
                    <select name="area_id" required class="w-full text-sm font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:border-primary-500 focus:ring-0">
                        <option value="">{{ __('messages.select_area') }}</option>
                        @foreach($areas as $area)
                            <option value="{{ $area->id }}" {{ old('area_id') == $area->id ? 'selected' : '' }}>{{ $area->name }}{{ $area->pincode ? ' (' . $area->pincode . ')' : '' }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Row 3: Contacts with Phone & WhatsApp Toggle Switch -->
                <div class="md:col-span-3 bg-slate-50/80 border border-slate-200/80 rounded-xl p-3.5 space-y-3" 
                     x-data="{ 
                         sameWhatsapp: {{ old('whatsapp') && old('whatsapp') !== old('phone') ? 'false' : 'true' }}, 
                         phoneNum: '{{ old('phone') }}', 
                         whatsappNum: '{{ old('whatsapp') }}' 
                     }">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 border-b border-slate-200/60 pb-2.5">
                        <div class="flex items-center gap-2">
                            <span class="text-xs sm:text-sm font-bold text-slate-800 uppercase tracking-wide">{{ __('messages.contact_details_sec') }}</span>
                            <span class="text-xs font-bold text-slate-500">({{ __('messages.phone_whatsapp_label') }})</span>
                        </div>

                        <!-- Toggle Switch UI -->
                        <div class="flex items-center gap-2 select-none cursor-pointer" @click="sameWhatsapp = !sameWhatsapp; if(sameWhatsapp) whatsappNum = phoneNum">
                            <span class="text-xs font-bold text-slate-700">{{ __('messages.whatsapp_same_as_phone') }}</span>
                            <button type="button" 
                                    :class="sameWhatsapp ? 'bg-emerald-500' : 'bg-slate-300'" 
                                    class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none">
                                <span :class="sameWhatsapp ? 'translate-x-4' : 'translate-x-0'" 
                                      class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow-md ring-0 transition duration-200 ease-in-out"></span>
                            </button>
                            <span x-text="sameWhatsapp ? '{{ __('messages.yes') }}' : '{{ __('messages.no') }}'" :class="sameWhatsapp ? 'text-emerald-700 font-extrabold' : 'text-slate-500 font-bold'" class="text-xs w-6"></span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                        <!-- Phone Field -->
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">{{ __('messages.phone_mobile') }} <span class="text-rose-500">*</span></label>
                            <input type="text" name="phone" x-model="phoneNum" @input="if(sameWhatsapp) whatsappNum = phoneNum" required minlength="10" maxlength="10" pattern="[0-9]{10}" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)" placeholder="{{ __('messages.ten_digits') }}" class="w-full text-sm font-semibold px-3 py-2 bg-white border border-slate-200 rounded-lg focus:border-primary-500 focus:ring-0">
                        </div>

                        <!-- WhatsApp Field -->
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">
                                {{ __('messages.whatsapp_number') }} 
                                <template x-if="sameWhatsapp">
                                    <span class="text-emerald-600 font-bold text-xs lowercase">({{ __('messages.same_as_phone') }})</span>
                                </template>
                            </label>
                            <input type="text" name="whatsapp" x-model="whatsappNum" :readonly="sameWhatsapp" :class="sameWhatsapp ? 'bg-slate-100 text-slate-500 cursor-not-allowed border-slate-200' : 'bg-white border-emerald-400 focus:border-emerald-500'" maxlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)" placeholder="{{ __('messages.ten_digit_whatsapp_placeholder') }}" class="w-full text-sm font-semibold px-3 py-2 rounded-lg focus:ring-0">
                        </div>

                        <!-- Email Field with OTP Verification (Modal-based) -->
                        <div class="space-y-1">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">
                                    {{ __('messages.email_address_label') }} <span class="text-rose-500">*</span>
                                </label>
                                <span id="bizEmailVerifiedBadge" class="hidden text-[11px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">
                                    ✓ Email Verified
                                </span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <input type="email" id="bizEmailInput" name="email"
                                       value="{{ old('email') }}"
                                       required
                                       placeholder="{{ __('messages.email_placeholder') }}"
                                       class="w-full text-sm font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none transition @error('email') border-rose-400 @enderror">
                                <button type="button" id="bizSendOtpBtn"
                                        class="shrink-0 px-3 py-2 bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs rounded-lg shadow transition-all active:scale-95 cursor-pointer">
                                    <span id="bizSendOtpBtnText">Send OTP</span>
                                </button>
                                <button type="button" id="bizChangeEmailBtn"
                                        class="hidden shrink-0 px-2.5 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold text-xs rounded-lg transition-all cursor-pointer">
                                    Change
                                </button>
                            </div>
                            @error('email')
                                <p class="text-xs text-rose-600 font-bold mt-0.5">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Password & Confirm Password (for Business Panel Login) -->
                    <div class="mt-3 pt-3 border-t border-slate-200/60">
                        <p class="text-xs font-bold text-slate-600 mb-2.5">🔐 Set a password to access your <strong>Business Panel</strong> after registration.</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="space-y-1" x-data="{ show: false }">
                                <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">Password <span class="text-rose-500">*</span></label>
                                <div class="relative">
                                    <input :type="show ? 'text' : 'password'" name="password"
                                           placeholder="Min 6 characters"
                                           class="w-full text-sm font-semibold px-3 py-2 pr-9 bg-white border border-slate-200 rounded-lg focus:border-indigo-400 focus:ring-0 @error('password') border-rose-400 @enderror"
                                           required minlength="6">
                                    <button type="button" @click="show = !show"
                                            class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700 text-sm">
                                        <svg x-show="!show" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        <svg x-show="show" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                    </button>
                                </div>
                                @error('password') <p class="text-xs text-rose-600 font-bold mt-0.5">{{ $message }}</p> @enderror
                            </div>
                            <div class="space-y-1" x-data="{ show: false }">
                                <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">Confirm Password <span class="text-rose-500">*</span></label>
                                <div class="relative">
                                    <input :type="show ? 'text' : 'password'" name="password_confirmation"
                                           placeholder="Repeat password"
                                           class="w-full text-sm font-semibold px-3 py-2 pr-9 bg-white border border-slate-200 rounded-lg focus:border-indigo-400 focus:ring-0"
                                           required minlength="6">
                                    <button type="button" @click="show = !show"
                                            class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700 text-sm">
                                        <svg x-show="!show" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        <svg x-show="show" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Row 4: Links -->
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">{{ __('messages.website_url_label') }}</label>
                    <input type="url" name="website" value="{{ old('website') }}" placeholder="https://example.com" class="w-full text-sm font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:border-primary-500 focus:ring-0">
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">{{ __('messages.facebook_link_label') }}</label>
                    <input type="text" name="facebook" value="{{ old('facebook') }}" placeholder="https://facebook.com/username" class="w-full text-sm font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:border-primary-500 focus:ring-0">
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">{{ __('messages.instagram_link_label') }}</label>
                    <input type="text" name="instagram" value="{{ old('instagram') }}" placeholder="https://instagram.com/username" class="w-full text-sm font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:border-primary-500 focus:ring-0">
                </div>

                <!-- Row 5: Links Continued & File -->
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">{{ __('messages.youtube_link_label') }}</label>
                    <input type="text" name="youtube" value="{{ old('youtube') }}" placeholder="https://youtube.com/@username" class="w-full text-sm font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:border-primary-500 focus:ring-0">
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">{{ __('messages.linkedin_link_label') }}</label>
                    <input type="text" name="linkedin" value="{{ old('linkedin') }}" placeholder="https://linkedin.com/in/username" class="w-full text-sm font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:border-primary-500 focus:ring-0">
                </div>

                <div class="space-y-1" x-data="{ logoPreview: null }">
                    <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">{{ __('messages.business_logo_label') }} <span class="text-rose-500">*</span></label>
                    <div class="flex items-center gap-3">
                        <!-- Small thumbnail preview -->
                        <div class="relative w-11 h-11 rounded-lg border border-slate-200 bg-slate-100 overflow-hidden shrink-0 flex items-center justify-center shadow-2xs">
                            <template x-if="logoPreview">
                                <img :src="logoPreview" alt="Logo Preview" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!logoPreview">
                                <svg class="w-5 h-5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </template>
                        </div>
                        <input type="file" name="logo" required accept="image/*" 
                               @change="const file = $event.target.files[0]; if (file) { const r = new FileReader(); r.onload = e => logoPreview = e.target.result; r.readAsDataURL(file); }"
                               class="text-xs font-semibold block w-full text-slate-600 bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 file:mr-2 file:py-1 file:px-2.5 file:rounded-md file:border-0 file:text-xs file:font-bold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 cursor-pointer">
                    </div>
                </div>

                <!-- Row 6: Showcase Photos & Description -->
                <div class="space-y-2 md:col-span-3 border border-slate-100 rounded-xl p-3.5 bg-slate-50/50" x-data="multiShowcaseUploader()">
                    <div class="flex items-center justify-between gap-3 flex-wrap">
                        <label class="text-xs font-bold text-slate-700 uppercase tracking-wider block">
                            {{ __('messages.showcase_photos_label') }} <span class="text-slate-500 font-normal">({{ __('messages.select_multiple_append') }})</span>
                        </label>
                        <div class="flex items-center gap-2">
                            <button type="button" x-show="files.length < 6" @click="$refs.hiddenFileInput.click()" 
                                class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs rounded-lg shadow-2xs transition-all flex items-center gap-1 cursor-pointer" x-cloak>
                                <span>{{ __('messages.select_files') }}</span>
                            </button>
                            <button type="button" @click="clearAll()" x-show="files.length > 0" x-cloak
                                class="px-3 py-1.5 bg-rose-100 hover:bg-rose-200 text-rose-700 font-bold text-xs rounded-lg transition-all cursor-pointer">
                                <span>{{ __('messages.clear') }}</span>
                            </button>
                        </div>
                    </div>

                    <input type="file" x-ref="hiddenFileInput" name="gallery[]" accept="image/*" multiple @change="selectFiles($event)" class="hidden">

                    <div 
                        @dragover.prevent="isDragging = true"
                        @dragleave.prevent="isDragging = false"
                        @drop.prevent="dropFiles($event)"
                        :class="isDragging ? 'border-blue-500 bg-blue-50/60' : 'border-slate-300 bg-white'"
                        class="border border-dashed rounded-xl p-4 text-center transition-all cursor-pointer"
                        @click="$refs.hiddenFileInput.click()">
                        
                        <div x-show="files.length === 0" class="py-2 space-y-1">
                            <p class="text-xs font-bold text-slate-700">{{ __('messages.drop_files_here_or') }} <span class="text-blue-600 underline">{{ __('messages.browse') }}</span></p>
                            <p class="text-xs text-slate-400 font-medium">{{ __('messages.drop_files_subtitle') }}</p>
                        </div>

                        <div x-show="files.length > 0" class="space-y-2" @click.stop x-cloak>
                            <div class="flex items-center justify-between text-xs font-bold text-slate-600 px-1 border-b border-slate-100 pb-1.5">
                                <span>{{ __('messages.selected_showcase_photos') }}</span>
                                <span class="text-blue-600 font-extrabold bg-blue-50 px-2.5 py-0.5 rounded-md border border-blue-200/80" x-text="files.length + '/6 {{ __('messages.selected') }}'"></span>
                            </div>
                            <div class="flex flex-wrap items-center gap-3 pt-1">
                                <template x-for="(f, idx) in files" :key="f.id">
                                    <div class="relative group w-24 h-24 rounded-xl overflow-hidden border border-slate-200 bg-slate-100 shadow-2xs shrink-0">
                                        <img :src="f.url" class="w-full h-full object-cover">
                                        
                                        <!-- Remove Button -->
                                        <button type="button" @click.stop="removeFile(idx)" 
                                            class="absolute top-1 right-1 w-5 h-5 rounded-full bg-rose-600 text-white flex items-center justify-center shadow-md hover:bg-rose-700 transition-colors text-xs font-black" title="Remove photo">
                                            ✕
                                        </button>
                                        
                                        <!-- File Name Bar -->
                                        <div class="absolute bottom-0 inset-x-0 bg-slate-900/80 text-white p-0.5 text-[9px] truncate font-semibold backdrop-blur-xs text-center">
                                            <span x-text="f.name"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-1 md:col-span-3">
                    <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">{{ __('messages.business_desc_label') }} <span class="text-slate-400 font-normal">({{ __('messages.optional') }})</span></label>
                    <textarea name="description" rows="2" placeholder="{{ __('messages.business_desc_placeholder') }}" class="w-full text-sm font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:border-primary-500 focus:ring-0">{{ old('description') }}</textarea>
                </div>

                <!-- Row 7: Address -->
                <div class="space-y-1 md:col-span-3">
                    <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">{{ __('messages.office_address_label') }} <span class="text-rose-500">*</span></label>
                    <textarea name="address" rows="2" required placeholder="{{ __('messages.office_address_placeholder') }}" class="w-full text-sm font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:border-primary-500 focus:ring-0">{{ old('address') }}</textarea>
                </div>

                <!-- Payment Summary & Submit Button -->
                <div class="md:col-span-3 space-y-3 pt-2">
                    <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
                    @if(($businessFee ?? 500) > 0)
                        <div class="bg-primary-50/90 border border-primary-200/80 rounded-xl p-3.5 flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <span class="text-lg">💳</span>
                                <div>
                                    <h4 class="text-xs sm:text-sm font-bold text-primary-900">{{ __('messages.business_listing_fee') }}</h4>
                                    <p class="text-xs font-medium text-primary-700">{{ __('messages.payment_processed_securely') }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="text-base font-black text-primary-700">₹{{ number_format($businessFee ?? 500) }}</span>
                                <span class="block text-xs font-extrabold text-slate-400">{{ __('messages.business_yearly_fee') }}</span>
                            </div>
                        </div>
                    @endif

                    <div class="flex justify-end border-t border-slate-100 pt-3">
                        <button type="submit" id="submitBusinessBtn" class="inline-flex items-center justify-center px-6 py-3 bg-primary-600 hover:bg-primary-700 font-extrabold text-xs sm:text-sm text-white uppercase tracking-wider rounded-xl shadow-xs transition-all active:scale-95 cursor-pointer">
                            <span>{{ __('messages.pay_and_register_business', ['amount' => number_format($businessFee ?? 500)]) }}</span> &rarr;
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>

<!-- ══ Business OTP Alert Modal (warnings/errors) ══ -->
<div id="bizOtpAlertModal" class="fixed inset-0 items-center justify-center hidden" style="display:none !important;position:fixed;top:0;left:0;width:100vw;height:100vh;z-index:999999 !important;" role="dialog" aria-modal="true">
    <div id="bizOtpModalBackdrop" style="position:absolute;inset:0;background-color:rgba(0,0,0,0.65);backdrop-filter:blur(4px);"></div>
    <div class="relative w-full max-w-sm mx-4 bg-white rounded-2xl shadow-2xl overflow-hidden transform transition-all duration-300 scale-95 opacity-0" id="bizOtpModalPanel">
        <div id="bizOtpModalAccent" class="h-1.5 w-full bg-rose-500"></div>
        <div class="p-6">
            <div class="flex items-start gap-3 mb-3">
                <div id="bizOtpModalIcon" class="shrink-0 w-10 h-10 rounded-xl flex items-center justify-center text-xl bg-rose-100 text-rose-600">⚠️</div>
                <div>
                    <h3 id="bizOtpAlertTitle" class="text-sm font-extrabold text-slate-900 leading-tight">Notice</h3>
                    <p id="bizOtpAlertMessage" class="text-xs text-slate-600 mt-1 leading-relaxed"></p>
                </div>
            </div>
            <div class="flex justify-end pt-2">
                <button id="bizOtpModalCloseBtn" type="button" class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white font-extrabold text-xs rounded-xl transition-all active:scale-95 shadow cursor-pointer">OK, Got It</button>
            </div>
        </div>
    </div>
</div>

<!-- ══ Business OTP Entry Popup Modal ══ -->
<div id="bizOtpEntryModal" class="fixed inset-0 items-center justify-center hidden" style="display:none !important;position:fixed;top:0;left:0;width:100vw;height:100vh;z-index:999998 !important;" role="dialog" aria-modal="true">
    <div style="position:absolute;inset:0;background-color:rgba(0,0,0,0.65);backdrop-filter:blur(4px);"></div>
    <div class="relative w-full max-w-sm mx-4 bg-white rounded-2xl shadow-2xl overflow-hidden transform transition-all duration-300 scale-95 opacity-0" id="bizOtpEntryPanel">

        <!-- Header -->
        <div style="background:linear-gradient(135deg,#6366f1 0%,#4f46e5 100%);padding:20px 24px;">
            <div class="flex items-center gap-3">
                <div style="width:44px;height:44px;background:rgba(255,255,255,0.18);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0;">📧</div>
                <div>
                    <h3 style="color:#fff;font-size:15px;font-weight:800;margin:0;">Email Verification</h3>
                    <p style="color:#c7d2fe;font-size:11px;margin:3px 0 0 0;">We sent a 6-digit OTP to your inbox</p>
                </div>
            </div>
        </div>

        <!-- Body -->
        <div class="p-5 space-y-4">
            <!-- Target email -->
            <div class="text-center">
                <p class="text-xs text-slate-500">Verification code sent to</p>
                <p id="bizOtpTargetEmail" class="text-sm font-extrabold break-all mt-0.5" style="color:#6366f1;"></p>
            </div>

            <!-- OTP Input -->
            <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate-600 uppercase tracking-widest block">Enter OTP <span class="text-rose-500">*</span></label>
                <input type="text" id="bizOtpInput" inputmode="numeric" maxlength="6" placeholder="0  0  0  0  0  0"
                    style="width:100%;font-size:22px;font-weight:900;letter-spacing:0.35em;text-align:center;padding:12px 16px;background:#f8fafc;border:2px solid #e2e8f0;border-radius:12px;outline:none;transition:border-color 0.2s,box-shadow 0.2s;box-sizing:border-box;"
                    onfocus="this.style.borderColor='#6366f1';this.style.boxShadow='0 0 0 3px rgba(99,102,241,0.15)'"
                    onblur="this.style.borderColor='#e2e8f0';this.style.boxShadow='none'">
                <div id="bizOtpStatusMsg" class="text-xs font-semibold min-h-[18px] text-center"></div>
            </div>

            <!-- Resend -->
            <p class="text-center" style="font-size:11px;color:#94a3b8;margin:0;">
                Didn't receive the code? &nbsp;
                <button type="button" id="bizResendOtpBtn" style="font-weight:700;color:#6366f1;text-decoration:underline;cursor:pointer;background:none;border:none;padding:0;outline:none;">Resend OTP</button>
                <span id="bizOtpResendTimer" style="color:#94a3b8;font-weight:600;"></span>
            </p>
        </div>

        <!-- Footer -->
        <div style="display:flex;gap:10px;padding:0 20px 20px;">
            <button type="button" id="bizCancelOtpModalBtn"
                style="flex:1;padding:10px;border:1.5px solid #e2e8f0;background:#f8fafc;color:#475569;font-weight:700;font-size:12px;border-radius:10px;cursor:pointer;transition:background 0.15s;"
                onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#f8fafc'">Cancel</button>
            <button type="button" id="bizVerifyOtpBtn"
                style="flex:1;padding:10px;background:#16a34a;color:#fff;font-weight:800;font-size:12px;border-radius:10px;cursor:pointer;border:none;box-shadow:0 2px 8px rgba(22,163,74,0.3);transition:background 0.15s;"
                onmouseover="this.style.background='#15803d'" onmouseout="this.style.background='#16a34a'">
                <span id="bizVerifyOtpBtnText">Verify OTP</span>
            </button>
        </div>
    </div>
</div>

<script>
function multiShowcaseUploader() {
    return {
        files: [],
        isDragging: false,
        maxFiles: 6,
        showLimitModal: false,
        
        selectFiles(e) {
            if (e.target.files && e.target.files.length > 0) {
                this.addFiles(Array.from(e.target.files));
            }
        },
        
        dropFiles(e) {
            this.isDragging = false;
            if (e.dataTransfer && e.dataTransfer.files) {
                this.addFiles(Array.from(e.dataTransfer.files));
            }
        },
        
        addFiles(newFiles) {
            let overflow = false;
            newFiles.forEach(file => {
                if (file.type.startsWith('image/')) {
                    const exists = this.files.some(f => f.name === file.name && f.file.size === file.size);
                    if (!exists) {
                        if (this.files.length < this.maxFiles) {
                            this.files.push({
                                id: Math.random().toString(36).substring(2, 9),
                                file: file,
                                name: file.name,
                                size: (file.size / (1024 * 1024)).toFixed(2) + ' MB',
                                url: URL.createObjectURL(file)
                            });
                        } else {
                            overflow = true;
                        }
                    }
                }
            });

            if (overflow) {
                this.showLimitModal = true;
            }

            this.syncInput();
        },
        
        removeFile(index) {
            if (this.files[index]) {
                URL.revokeObjectURL(this.files[index].url);
                this.files.splice(index, 1);
                this.syncInput();
            }
        },
        
        clearAll() {
            this.files.forEach(f => URL.revokeObjectURL(f.url));
            this.files = [];
            this.syncInput();
        },
        
        syncInput() {
            const input = this.$refs.hiddenFileInput;
            if (input) {
                const dt = new DataTransfer();
                this.files.forEach(f => dt.items.add(f.file));
                input.files = dt.files;
            }
        }
    };
}
</script>


<script>
// ════════════════════════════════════════════════
// Business Registration – OTP Email Verification
// ════════════════════════════════════════════════
document.addEventListener('DOMContentLoaded', function () {

    // ── DOM refs ─────────────────────────────────
    const emailInput        = document.getElementById('bizEmailInput');
    const sendOtpBtn        = document.getElementById('bizSendOtpBtn');
    const sendOtpBtnText    = document.getElementById('bizSendOtpBtnText');
    const changeEmailBtn    = document.getElementById('bizChangeEmailBtn');
    const emailVerifiedBadge= document.getElementById('bizEmailVerifiedBadge');

    // OTP Entry Modal
    const otpEntryModal  = document.getElementById('bizOtpEntryModal');
    const otpEntryPanel  = document.getElementById('bizOtpEntryPanel');
    const otpTargetEmail = document.getElementById('bizOtpTargetEmail');
    const otpInput       = document.getElementById('bizOtpInput');
    const verifyOtpBtn   = document.getElementById('bizVerifyOtpBtn');
    const verifyOtpBtnText = document.getElementById('bizVerifyOtpBtnText');
    const otpStatusMsg   = document.getElementById('bizOtpStatusMsg');
    const cancelOtpBtn   = document.getElementById('bizCancelOtpModalBtn');
    const resendOtpBtn   = document.getElementById('bizResendOtpBtn');
    const otpResendTimer = document.getElementById('bizOtpResendTimer');

    // Alert Modal
    const alertModal     = document.getElementById('bizOtpAlertModal');
    const alertPanel     = document.getElementById('bizOtpModalPanel');
    const alertAccent    = document.getElementById('bizOtpModalAccent');
    const alertIcon      = document.getElementById('bizOtpModalIcon');
    const alertTitle     = document.getElementById('bizOtpAlertTitle');
    const alertMessage   = document.getElementById('bizOtpAlertMessage');
    const alertCloseBtn  = document.getElementById('bizOtpModalCloseBtn');
    const alertBackdrop  = document.getElementById('bizOtpModalBackdrop');

    // Move modals to <body> so they sit above everything
    [alertModal, otpEntryModal].forEach(el => {
        if (el && el.parentElement !== document.body) document.body.appendChild(el);
    });

    let isEmailVerified = false;
    let resendInterval  = null;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

    // ── Alert Modal ───────────────────────────────
    const alertConfig = {
        warning: { accent:'bg-amber-500',   icon:'⚠️',  iconBg:'bg-amber-100 text-amber-600',   btnBg:'bg-amber-500 hover:bg-amber-600' },
        error:   { accent:'bg-rose-500',    icon:'❌',  iconBg:'bg-rose-100 text-rose-600',     btnBg:'bg-rose-600 hover:bg-rose-700' },
        success: { accent:'bg-emerald-500', icon:'✅',  iconBg:'bg-emerald-100 text-emerald-600',btnBg:'bg-emerald-600 hover:bg-emerald-700' },
        info:    { accent:'bg-indigo-500',  icon:'ℹ️', iconBg:'bg-indigo-100 text-indigo-600', btnBg:'bg-indigo-600 hover:bg-indigo-700' },
    };
    function showAlert(message, type = 'warning', title = null) {
        const cfg = alertConfig[type] || alertConfig.warning;
        alertAccent.className  = 'h-1.5 w-full ' + cfg.accent;
        alertIcon.className    = 'shrink-0 w-10 h-10 rounded-xl flex items-center justify-center text-xl ' + cfg.iconBg;
        alertIcon.textContent  = cfg.icon;
        alertCloseBtn.className= 'px-5 py-2 font-extrabold text-xs text-white rounded-xl transition-all active:scale-95 shadow cursor-pointer ' + cfg.btnBg;
        alertTitle.textContent = title || type.charAt(0).toUpperCase() + type.slice(1);
        alertMessage.textContent = message;
        alertModal.style.setProperty('display', 'flex', 'important');
        alertModal.classList.remove('hidden');
        setTimeout(() => { alertPanel.classList.remove('scale-95','opacity-0'); alertPanel.classList.add('scale-100','opacity-100'); }, 10);
    }
    function closeAlert() {
        alertPanel.classList.remove('scale-100','opacity-100'); alertPanel.classList.add('scale-95','opacity-0');
        setTimeout(() => {
            alertModal.classList.add('hidden');
            alertModal.style.setProperty('display', 'none', 'important');
        }, 200);
    }
    if (alertCloseBtn)  alertCloseBtn.addEventListener('click', closeAlert);
    if (alertBackdrop)  alertBackdrop.addEventListener('click', closeAlert);

    // ── OTP Entry Modal ───────────────────────────
    function openOtpModal(email) {
        if (otpTargetEmail) otpTargetEmail.textContent = email;
        if (otpInput)       otpInput.value = '';
        if (otpStatusMsg)   otpStatusMsg.innerHTML = '';
        otpEntryModal.style.setProperty('display', 'flex', 'important');
        otpEntryModal.classList.remove('hidden');
        setTimeout(() => {
            otpEntryPanel.classList.remove('scale-95','opacity-0');
            otpEntryPanel.classList.add('scale-100','opacity-100');
            if (otpInput) otpInput.focus();
        }, 10);
    }
    function closeOtpModal() {
        otpEntryPanel.classList.remove('scale-100','opacity-100'); otpEntryPanel.classList.add('scale-95','opacity-0');
        setTimeout(() => {
            otpEntryModal.classList.add('hidden');
            otpEntryModal.style.setProperty('display', 'none', 'important');
        }, 200);
    }
    if (cancelOtpBtn) cancelOtpBtn.addEventListener('click', closeOtpModal);

    // ── Resend timer ──────────────────────────────
    function setResendState(enabled, t = 0) {
        if (!resendOtpBtn) return;
        resendOtpBtn.disabled = !enabled;
        resendOtpBtn.style.color       = enabled ? '#6366f1' : '#94a3b8';
        resendOtpBtn.style.cursor      = enabled ? 'pointer' : 'not-allowed';
        resendOtpBtn.style.textDecoration = enabled ? 'underline' : 'none';
        resendOtpBtn.style.opacity     = enabled ? '1' : '0.7';
        if (otpResendTimer) otpResendTimer.textContent = (!enabled && t > 0) ? ` (${t}s)` : '';
    }
    function startResendTimer(sec = 30) {
        let t = sec;
        clearInterval(resendInterval);
        setResendState(false, t);
        resendInterval = setInterval(() => {
            t--;
            if (t <= 0) { clearInterval(resendInterval); setResendState(true); }
            else        { setResendState(false, t); }
        }, 1000);
    }

    // ── Send OTP ──────────────────────────────────
    function doSendOtp(email) {
        sendOtpBtn.disabled = true;
        sendOtpBtnText.textContent = 'Sending...';
        fetch('{{ route('register.business.send_otp') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: JSON.stringify({ email })
        })
        .then(r => r.json())
        .then(data => {
            sendOtpBtnText.textContent = 'Send OTP';
            sendOtpBtn.disabled = false;
            if (data.success) {
                openOtpModal(email);
                if (otpStatusMsg) otpStatusMsg.innerHTML = '<span style="color:#16a34a;font-weight:600;">✓ ' + (data.message || 'OTP sent successfully.') + '</span>';
                startResendTimer(30);
            } else {
                showAlert(data.message || 'Failed to send OTP.', 'error', 'Error');
            }
        })
        .catch(() => {
            sendOtpBtnText.textContent = 'Send OTP';
            sendOtpBtn.disabled = false;
            showAlert('Network error. Please try again.', 'error', 'Connection Error');
        });
    }

    if (sendOtpBtn) {
        sendOtpBtn.addEventListener('click', function () {
            const email = emailInput?.value.trim() || '';
            if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                showAlert('Please enter a valid email address.', 'warning', 'Email Required');
                emailInput?.focus();
                return;
            }
            doSendOtp(email);
        });
    }

    // ── Resend inside modal ───────────────────────
    if (resendOtpBtn) {
        resendOtpBtn.addEventListener('click', function () {
            if (resendOtpBtn.disabled) return;
            const email = emailInput?.value.trim() || '';
            if (!email) return;
            setResendState(false, 0);
            if (otpStatusMsg) otpStatusMsg.innerHTML = '<span style="color:#6366f1;font-weight:600;">Sending new OTP...</span>';
            fetch('{{ route('register.business.send_otp') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify({ email })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    if (otpInput) { otpInput.value = ''; otpInput.focus(); }
                    if (otpStatusMsg) otpStatusMsg.innerHTML = '<span style="color:#16a34a;font-weight:700;">✓ New OTP sent!</span>';
                    startResendTimer(30);
                } else {
                    setResendState(true);
                    if (otpStatusMsg) otpStatusMsg.innerHTML = '<span style="color:#dc2626;font-weight:700;">❌ ' + (data.message || 'Failed') + '</span>';
                }
            })
            .catch(() => {
                setResendState(true);
                if (otpStatusMsg) otpStatusMsg.innerHTML = '<span style="color:#dc2626;">Network error.</span>';
            });
        });
    }

    // ── Verify OTP ────────────────────────────────
    if (verifyOtpBtn) {
        verifyOtpBtn.addEventListener('click', function () {
            const email = emailInput?.value.trim() || '';
            const otp   = otpInput?.value.trim() || '';
            if (!otp || otp.length !== 6) {
                if (otpStatusMsg) otpStatusMsg.innerHTML = '<span style="color:#dc2626;font-weight:700;">Please enter the 6-digit OTP.</span>';
                otpInput?.focus();
                return;
            }
            verifyOtpBtn.disabled = true;
            verifyOtpBtnText.textContent = '...';
            fetch('{{ route('register.business.verify_otp') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify({ email, otp })
            })
            .then(r => r.json())
            .then(data => {
                verifyOtpBtn.disabled = false;
                verifyOtpBtnText.textContent = 'Verify OTP';
                if (data.success) {
                    isEmailVerified = true;
                    closeOtpModal();
                    clearInterval(resendInterval);
                    // Update email field UI
                    emailVerifiedBadge.classList.remove('hidden');
                    changeEmailBtn.classList.remove('hidden');
                    sendOtpBtn.classList.add('hidden');
                    emailInput.readOnly = true;
                    emailInput.classList.add('bg-slate-100','text-slate-600','cursor-not-allowed');
                } else {
                    if (otpStatusMsg) otpStatusMsg.innerHTML = '<span style="color:#dc2626;font-weight:700;">' + (data.message || 'Invalid OTP.') + '</span>';
                }
            })
            .catch(() => {
                verifyOtpBtn.disabled = false;
                verifyOtpBtnText.textContent = 'Verify OTP';
                if (otpStatusMsg) otpStatusMsg.innerHTML = '<span style="color:#dc2626;">Network error. Try again.</span>';
            });
        });
    }

    // Enter key in OTP input
    if (otpInput) {
        otpInput.addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); verifyOtpBtn?.click(); } });
        otpInput.addEventListener('input', function () { this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6); });
    }

    // Change email button
    if (changeEmailBtn) {
        changeEmailBtn.addEventListener('click', function () {
            isEmailVerified = false;
            emailInput.readOnly = false;
            emailInput.classList.remove('bg-slate-100','text-slate-600','cursor-not-allowed');
            emailVerifiedBadge.classList.add('hidden');
            changeEmailBtn.classList.add('hidden');
            sendOtpBtn.classList.remove('hidden');
            sendOtpBtn.disabled = false;
            if (otpResendTimer) otpResendTimer.textContent = '';
        });
    }

    // Escape key closes modals
    document.addEventListener('keydown', e => { if (e.key === 'Escape') { closeOtpModal(); closeAlert(); } });

    // ── Block form submit if email not verified ───
    const bizForm = document.querySelector('form[action="{{ route('register.business.submit') }}"]');
    if (bizForm) {
        bizForm.addEventListener('submit', function (e) {
            // Allow if payment already done
            const paymentIdInput = document.getElementById('razorpay_payment_id');
            if (paymentIdInput && paymentIdInput.value) return true;

            if (!isEmailVerified) {
                e.preventDefault();
                showAlert('Please verify your email address using OTP before submitting.', 'warning', 'Email Verification Required');
                sendOtpBtn?.focus();
                return false;
            }
        }, true); // capture phase so it fires before the Razorpay listener
    }
});
</script>

@if(($businessFee ?? 500) > 0)
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form[action="{{ route('register.business.submit') }}"]');
    if (!form) return;

    // ── Prevent Razorpay re-opening on page refresh ──────────────
    // If sessionStorage shows a payment was already initiated, do NOT
    // re-trigger Razorpay when the page is refreshed (F5 / browser back).
    sessionStorage.removeItem('biz_rzp_inprogress');

    const submitBtn = document.getElementById('submitBusinessBtn');

    form.addEventListener('submit', function (e) {
        const paymentIdInput = document.getElementById('razorpay_payment_id');

        // Already captured payment_id — allow normal form submit to server
        if (paymentIdInput && paymentIdInput.value) {
            return true;
        }

        // If browser is replaying a cached POST (refresh), just abort silently
        if (sessionStorage.getItem('biz_rzp_inprogress') === '1') {
            e.preventDefault();
            sessionStorage.removeItem('biz_rzp_inprogress');
            return false;
        }

        e.preventDefault();

        // HTML5 validation
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const razorpayKey    = "{{ $razorpayKeyId ?? '' }}";
        const feeAmountPaise = {{ ($businessFee ?? 500) * 100 }};
        const businessName   = form.querySelector('[name="business_name"]')?.value || '';
        const ownerName      = form.querySelector('[name="owner_name"]')?.value || '';
        const email          = form.querySelector('[name="email"]')?.value || '';
        const phone          = (form.querySelector('[name="phone"]')?.value || '').trim();

        if (phone.length !== 10 || !/^\d{10}$/.test(phone)) {
            alert("{{ __('messages.mobile_10_digits_required') ?? 'મોબાઈલ નંબર બરાબર ૧૦ અંકનો હોવો જરૂરી છે.' }}");
            form.querySelector('[name="phone"]')?.focus();
            return;
        }

        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span style="display:inline-flex;align-items:center;gap:6px;"><svg style="width:14px;height:14px;animation:spin 1s linear infinite;" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Opening payment...</span>';
        }

        const options = {
            "key":         razorpayKey || "rzp_test_key",
            "amount":      feeAmountPaise,
            "currency":    "INR",
            "name":        "{{ config('app.name', 'Shree Satwara Gnati Mandal, Ahmedabad') }}",
            "description": "Business Registration Fee - " + businessName,
            "handler": function (response) {
                // Mark payment done — clear session flag
                sessionStorage.removeItem('biz_rzp_inprogress');
                paymentIdInput.value = response.razorpay_payment_id;
                form.submit();
            },
            "modal": {
                "ondismiss": function () {
                    // User closed Razorpay without paying — reset button
                    sessionStorage.removeItem('biz_rzp_inprogress');
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<span>{{ __('messages.pay_and_register_business', ['amount' => number_format($businessFee ?? 500)]) }}</span> &rarr;';
                    }
                }
            },
            "prefill": {
                "name":    ownerName,
                "email":   email,
                "contact": phone
            },
            "theme": { "color": "#2563EB" }
        };

        function launchRazorpay() {
            sessionStorage.setItem('biz_rzp_inprogress', '1');
            const rzp = new Razorpay(options);
            rzp.on('payment.failed', function () {
                sessionStorage.removeItem('biz_rzp_inprogress');
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<span>{{ __('messages.pay_and_register_business', ['amount' => number_format($businessFee ?? 500)]) }}</span> &rarr;';
                }
            });
            rzp.open();
        }

        if (window.Razorpay) {
            launchRazorpay();
        } else {
            const script = document.createElement('script');
            script.src = 'https://checkout.razorpay.com/v1/checkout.js';
            script.onload = launchRazorpay;
            script.onerror = function () {
                sessionStorage.removeItem('biz_rzp_inprogress');
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<span>{{ __('messages.pay_and_register_business', ['amount' => number_format($businessFee ?? 500)]) }}</span> &rarr;';
                }
                alert('Razorpay Payment Gateway failed to load. Please check your internet connection.');
            };
            document.head.appendChild(script);
        }
    });
});
</script>
@endif
@endsection
