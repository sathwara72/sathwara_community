@extends('layouts.business')

@section('title', __('My Business Profile'))
@section('page-title', __('My Business Profile'))

@section('content')
    <script>
        function togglePasswordVisibility(inputId, btn) {
            const input = document.getElementById(inputId);
            if (!input) return;
            const icon = btn.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                if (icon) {
                    icon.className = 'fas fa-eye-slash';
                }
            } else {
                input.type = 'password';
                if (icon) {
                    icon.className = 'fas fa-eye';
                }
            }
        }

        function profileGalleryUploader(existingImages = []) {
            return {
                existing: Array.isArray(existingImages) ? existingImages : [],
                toDelete: [],
                newFiles: [],
                isDragging: false,
                get totalCount() {
                    return (this.existing.length - this.toDelete.length) + this.newFiles.length;
                },
                markDelete(img) {
                    if (!this.toDelete.includes(img)) {
                        this.toDelete.push(img);
                    }
                },
                unmarkDelete(img) {
                    this.toDelete = this.toDelete.filter(i => i !== img);
                },
                selectFiles(e) {
                    const files = Array.from(e.target.files || []);
                    this.appendFiles(files);
                    e.target.value = '';
                },
                dropFiles(e) {
                    this.isDragging = false;
                    const files = Array.from(e.dataTransfer.files || []).filter(f => f.type.startsWith('image/'));
                    this.appendFiles(files);
                },
                appendFiles(files) {
                    const currentTotal = (this.existing.length - this.toDelete.length) + this.newFiles.length;
                    const allowed = 6 - currentTotal;
                    if (allowed <= 0) return;
                    const toAdd = files.slice(0, allowed);
                    toAdd.forEach(f => {
                        this.newFiles.push({
                            id: Math.random().toString(36).substring(2),
                            file: f,
                            name: f.name,
                            url: URL.createObjectURL(f)
                        });
                    });
                    this.syncInput();
                },
                removeNewFile(idx) {
                    if (this.newFiles[idx]) {
                        URL.revokeObjectURL(this.newFiles[idx].url);
                        this.newFiles.splice(idx, 1);
                        this.syncInput();
                    }
                },
                clearNewFiles() {
                    this.newFiles.forEach(f => URL.revokeObjectURL(f.url));
                    this.newFiles = [];
                    this.syncInput();
                },
                syncInput() {
                    const input = this.$refs.hiddenFileInput;
                    if (input) {
                        const dt = new DataTransfer();
                        this.newFiles.forEach(f => dt.items.add(f.file));
                        input.files = dt.files;
                    }
                }
            };
        }
    </script>

    <div class="max-w-5xl mx-auto space-y-4">

        <!-- Validation Errors -->
        @if ($errors->any())
            <div class="p-4 bg-rose-50 border border-rose-100 text-rose-800 rounded-2xl shadow-xs">
                <p class="text-xs font-bold mb-2">{{ __('messages.please_correct_errors') }}</p>
                <ul class="list-disc pl-4 text-[11px] font-medium space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Main Form Card (Matches Public Business Registration Form) -->
        <div class="bg-white border border-slate-200/60 rounded-2xl p-5 md:p-6 shadow-xs">

            <!-- Header with Status Badge -->
            <div
                class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-4 mb-4">
                <div>
                    <h3 class="text-base sm:text-lg font-extrabold text-slate-900 flex items-center gap-2">
                        <span>🏢</span>
                        <span>{{ __('messages.business_details_sec') }}</span>
                    </h3>
                    <p class="text-xs text-slate-500 font-medium mt-0.5">
                        {{ __('Manage and update your business details, contacts, links, and showcase photos.') }}
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    @if($business->status === 'approved')
                        <span
                            class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full text-xs font-bold">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            {{ __('Approved') }}
                        </span>
                    @elseif($business->status === 'pending')
                        <span
                            class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-50 text-amber-700 border border-amber-200 rounded-full text-xs font-bold">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                            {{ __('Pending Review') }}
                        </span>
                    @else
                        <span
                            class="inline-flex items-center gap-1.5 px-3 py-1 bg-slate-100 text-slate-700 border border-slate-200 rounded-full text-xs font-bold">
                            {{ __($business->status) }}
                        </span>
                    @endif
                </div>
            </div>

            <form method="POST" action="{{ route('business.profile.update') }}" enctype="multipart/form-data"
                class="grid grid-cols-1 md:grid-cols-3 gap-x-4 gap-y-4" id="profileForm" x-data="{ isSubmitting: false }"
                @submit="if($el.checkValidity()) { isSubmitting = true }">
                @csrf

                <!-- Row 1: Member ID & Business Name -->
                <div class="space-y-1" x-data="{ 
                         memberId: '{{ old('member_id', $business->member_id) }}', 
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
                     }" x-init="if(memberId) checkMember()">
                    <label
                        class="text-xs font-bold text-slate-700 uppercase tracking-wider">{{ __('messages.member_id_label') }}</label>
                    <div class="relative">
                        <input type="text" name="member_id" x-model="memberId" @input.debounce.400ms="checkMember()"
                            placeholder="{{ __('messages.member_id_placeholder') }}"
                            class="w-full text-sm font-semibold px-3 py-2 bg-slate-50 border rounded-lg focus:bg-white focus:ring-0 transition-colors"
                            :class="isFound === true ? 'border-emerald-400' : (isFound === false ? 'border-rose-400' : 'border-slate-200')">
                        <span x-show="loading"
                            class="absolute right-2.5 top-1/2 -translate-y-1/2 text-xs text-slate-400 font-bold">{{ __('messages.checking') }}</span>
                    </div>
                    <div x-show="memberStatus" class="mt-0.5 text-xs font-bold"
                        :class="isFound ? 'text-emerald-700' : 'text-rose-600'" x-text="memberStatus"></div>
                    @error('member_id')
                        <p class="text-xs text-rose-600 font-bold mt-0.5">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-1 md:col-span-2">
                    <label
                        class="text-xs font-bold text-slate-700 uppercase tracking-wider">{{ __('messages.firm_name_label') }}
                        <span class="text-rose-500">*</span></label>
                    <input type="text" name="business_name" required
                        value="{{ old('business_name', $business->business_name) }}"
                        placeholder="{{ __('messages.firm_name_placeholder') }}"
                        class="w-full text-sm font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:border-primary-500 focus:ring-0">
                    @error('business_name')
                        <p class="text-xs text-rose-600 font-bold mt-0.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Row 2: Owner, Category, Area -->
                <div class="space-y-1">
                    <label
                        class="text-xs font-bold text-slate-700 uppercase tracking-wider">{{ __('messages.contact_person_label') }}
                        <span class="text-rose-500">*</span></label>
                    <input type="text" name="owner_name" required value="{{ old('owner_name', $business->owner_name) }}"
                        placeholder="{{ __('messages.contact_person_placeholder') }}"
                        class="w-full text-sm font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:border-primary-500 focus:ring-0">
                    @error('owner_name')
                        <p class="text-xs text-rose-600 font-bold mt-0.5">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-1">
                    <label
                        class="text-xs font-bold text-slate-700 uppercase tracking-wider">{{ __('messages.business_category_label') }}</label>
                    <select name="category_id"
                        class="w-full text-sm font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:border-primary-500 focus:ring-0">
                        <option value="">{{ __('messages.select_category') }}</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $business->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <p class="text-xs text-rose-600 font-bold mt-0.5">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">{{ __('messages.area_label') }}
                        <span class="text-rose-500">*</span></label>
                    <select name="area_id" required
                        class="w-full text-sm font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:border-primary-500 focus:ring-0">
                        <option value="">{{ __('messages.select_area') }}</option>
                        @foreach($areas as $area)
                            <option value="{{ $area->id }}" {{ old('area_id', $business->area_id) == $area->id ? 'selected' : '' }}>{{ $area->name }}{{ $area->pincode ? ' (' . $area->pincode . ')' : '' }}</option>
                        @endforeach
                    </select>
                    @error('area_id')
                        <p class="text-xs text-rose-600 font-bold mt-0.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Row 3: Contacts with Phone & WhatsApp Toggle Switch -->
                <div class="md:col-span-3 bg-slate-50/80 border border-slate-200/80 rounded-xl p-3.5 space-y-3" x-data="{ 
                         sameWhatsapp: {{ old('whatsapp', $business->whatsapp) && old('whatsapp', $business->whatsapp) !== old('phone', $business->phone) ? 'false' : 'true' }}, 
                         phoneNum: '{{ old('phone', $business->phone) }}', 
                         whatsappNum: '{{ old('whatsapp', $business->whatsapp) }}' 
                     }">
                    <div
                        class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 border-b border-slate-200/60 pb-2.5">
                        <div class="flex items-center gap-2">
                            <span
                                class="text-xs sm:text-sm font-bold text-slate-800 uppercase tracking-wide">{{ __('messages.contact_details_sec') }}</span>
                            <span
                                class="text-xs font-bold text-slate-500">({{ __('messages.phone_whatsapp_label') }})</span>
                        </div>

                        <!-- Toggle Switch UI -->
                        <div class="flex items-center gap-2 select-none cursor-pointer"
                            @click="sameWhatsapp = !sameWhatsapp; if(sameWhatsapp) whatsappNum = phoneNum">
                            <span
                                class="text-xs font-bold text-slate-700">{{ __('messages.whatsapp_same_as_phone') }}</span>
                            <button type="button" :class="sameWhatsapp ? 'bg-emerald-500' : 'bg-slate-300'"
                                class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none">
                                <span :class="sameWhatsapp ? 'translate-x-4' : 'translate-x-0'"
                                    class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow-md ring-0 transition duration-200 ease-in-out"></span>
                            </button>
                            <span x-text="sameWhatsapp ? '{{ __('messages.yes') }}' : '{{ __('messages.no') }}'"
                                :class="sameWhatsapp ? 'text-emerald-700 font-extrabold' : 'text-slate-500 font-bold'"
                                class="text-xs w-6"></span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                        <!-- Phone Field -->
                        <div class="space-y-1">
                            <label
                                class="text-xs font-bold text-slate-700 uppercase tracking-wider">{{ __('messages.phone_mobile') }}
                                <span class="text-rose-500">*</span></label>
                            <input type="text" name="phone" x-model="phoneNum"
                                @input="if(sameWhatsapp) whatsappNum = phoneNum" required minlength="10" maxlength="10"
                                pattern="[0-9]{10}" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)"
                                placeholder="{{ __('messages.ten_digits') }}"
                                class="w-full text-sm font-semibold px-3 py-2 bg-white border border-slate-200 rounded-lg focus:border-primary-500 focus:ring-0">
                            @error('phone')
                                <p class="text-xs text-rose-600 font-bold mt-0.5">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- WhatsApp Field -->
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">
                                {{ __('messages.whatsapp_number') }}
                                <template x-if="sameWhatsapp">
                                    <span
                                        class="text-emerald-600 font-bold text-xs lowercase">({{ __('messages.same_as_phone') }})</span>
                                </template>
                            </label>
                            <input type="text" name="whatsapp" x-model="whatsappNum" :readonly="sameWhatsapp"
                                :class="sameWhatsapp ? 'bg-slate-100 text-slate-500 cursor-not-allowed border-slate-200' : 'bg-white border-emerald-400 focus:border-emerald-500'"
                                maxlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)"
                                placeholder="{{ __('messages.ten_digit_whatsapp_placeholder') }}"
                                class="w-full text-sm font-semibold px-3 py-2 rounded-lg focus:ring-0">
                            @error('whatsapp')
                                <p class="text-xs text-rose-600 font-bold mt-0.5">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Email Field with OTP Verification (Modal-based) -->
                        <div class="space-y-1">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">
                                    {{ __('messages.email_address_label') }} <span class="text-rose-500">*</span>
                                </label>
                                <span id="bizEmailVerifiedBadge"
                                    class="{{ $business->email_verified_at ? '' : 'hidden' }} text-[11px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">
                                    ✓ {{ __('Email Verified') }}
                                </span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <input type="email" id="bizEmailInput" name="email_display" value="{{ $business->email }}"
                                    {{ $business->email_verified_at ? 'readonly' : '' }}
                                    placeholder="{{ __('messages.email_placeholder') }}"
                                    class="w-full text-sm font-semibold px-3 py-2 {{ $business->email_verified_at ? 'bg-slate-100 text-slate-600 cursor-not-allowed' : 'bg-white' }} border border-slate-200 rounded-lg focus:bg-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none transition">
                                <button type="button" id="bizSendOtpBtn"
                                    class="{{ $business->email_verified_at ? 'hidden' : '' }} shrink-0 px-3 py-2 bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs rounded-lg shadow transition-all active:scale-95 cursor-pointer">
                                    <span id="bizSendOtpBtnText">{{ __('Send OTP') }}</span>
                                </button>
                                <button type="button" id="bizChangeEmailBtn"
                                    class="{{ $business->email_verified_at ? '' : 'hidden' }} shrink-0 px-2.5 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold text-xs rounded-lg transition-all cursor-pointer">
                                    {{ __('Change') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Row 4: Links -->
                <div class="space-y-1">
                    <label
                        class="text-xs font-bold text-slate-700 uppercase tracking-wider">{{ __('messages.website_url_label') }}</label>
                    <input type="url" name="website" value="{{ old('website', $business->website) }}"
                        placeholder="https://example.com"
                        class="w-full text-sm font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:border-primary-500 focus:ring-0">
                    @error('website')
                        <p class="text-xs text-rose-600 font-bold mt-0.5">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-1">
                    <label
                        class="text-xs font-bold text-slate-700 uppercase tracking-wider">{{ __('messages.facebook_link_label') }}</label>
                    <input type="text" name="facebook" value="{{ old('facebook', $business->facebook) }}"
                        placeholder="https://facebook.com/username"
                        class="w-full text-sm font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:border-primary-500 focus:ring-0">
                </div>

                <div class="space-y-1">
                    <label
                        class="text-xs font-bold text-slate-700 uppercase tracking-wider">{{ __('messages.instagram_link_label') }}</label>
                    <input type="text" name="instagram" value="{{ old('instagram', $business->instagram) }}"
                        placeholder="https://instagram.com/username"
                        class="w-full text-sm font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:border-primary-500 focus:ring-0">
                </div>

                <!-- Row 5: Links Continued & Logo File -->
                <div class="space-y-1">
                    <label
                        class="text-xs font-bold text-slate-700 uppercase tracking-wider">{{ __('messages.youtube_link_label') }}</label>
                    <input type="text" name="youtube" value="{{ old('youtube', $business->youtube) }}"
                        placeholder="https://youtube.com/@username"
                        class="w-full text-sm font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:border-primary-500 focus:ring-0">
                </div>

                <div class="space-y-1">
                    <label
                        class="text-xs font-bold text-slate-700 uppercase tracking-wider">{{ __('messages.linkedin_link_label') }}</label>
                    <input type="text" name="linkedin" value="{{ old('linkedin', $business->linkedin) }}"
                        placeholder="https://linkedin.com/in/username"
                        class="w-full text-sm font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:border-primary-500 focus:ring-0">
                </div>

                <div class="space-y-1"
                    x-data="{ logoPreview: '{{ $business->logo_path && !str_ends_with($business->logo_path, '.pdf') ? Storage::url($business->logo_path) : '' }}' }">
                    <label
                        class="text-xs font-bold text-slate-700 uppercase tracking-wider">{{ __('messages.business_logo_label') }}</label>
                    <div class="flex items-center gap-3">
                        <!-- Thumbnail preview -->
                        <div
                            class="relative w-11 h-11 rounded-lg border border-slate-200 bg-slate-100 overflow-hidden shrink-0 flex items-center justify-center shadow-2xs">
                            <template x-if="logoPreview">
                                <img :src="logoPreview" alt="Logo Preview" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!logoPreview">
                                @if($business->logo_path && str_ends_with($business->logo_path, '.pdf'))
                                    <span class="text-[10px] font-black text-rose-500">PDF</span>
                                @else
                                    <svg class="w-5 h-5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                @endif
                            </template>
                        </div>
                        <input type="file" name="logo" accept=".jpg,.jpeg,.png,.webp,.gif,.bmp,.pdf"
                            @change="const file = $event.target.files[0]; if (file && file.type.startsWith('image/')) { const r = new FileReader(); r.onload = e => logoPreview = e.target.result; r.readAsDataURL(file); } else if(file) { logoPreview = null; }"
                            class="text-xs font-semibold block w-full text-slate-600 bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 file:mr-2 file:py-1 file:px-2.5 file:rounded-md file:border-0 file:text-xs file:font-bold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 cursor-pointer">
                    </div>
                    @error('logo')
                        <p class="text-xs text-rose-600 font-bold mt-0.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Row 6: Showcase Photos (Gallery) with Drag-Drop & Delete Support -->
                <div class="space-y-2 md:col-span-3 border border-slate-100 rounded-xl p-3.5 bg-slate-50/50"
                    x-data="profileGalleryUploader(@json($business->gallery_images ?? []))">
                    <div class="flex items-center justify-between gap-3 flex-wrap">
                        <div>
                            <label class="text-xs font-bold text-slate-700 uppercase tracking-wider block">
                                {{ __('messages.showcase_photos_label') }} <span
                                    class="text-slate-500 font-normal">({{ __('messages.select_multiple_append') }})</span>
                            </label>
                            <span class="text-[11px] text-slate-400 font-medium">{{ __('Max 6 photos allowed') }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" x-show="totalCount < 6" @click="$refs.hiddenFileInput.click()"
                                class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs rounded-lg shadow-2xs transition-all flex items-center gap-1 cursor-pointer"
                                x-cloak>
                                <span>{{ __('messages.select_files') }}</span>
                            </button>
                            <button type="button" @click="clearNewFiles()" x-show="newFiles.length > 0" x-cloak
                                class="px-3 py-1.5 bg-rose-100 hover:bg-rose-200 text-rose-700 font-bold text-xs rounded-lg transition-all cursor-pointer">
                                <span>{{ __('messages.clear') }}</span>
                            </button>
                        </div>
                    </div>

                    <!-- Hidden file input for new gallery uploads -->
                    <input type="file" x-ref="hiddenFileInput" name="gallery[]" accept="image/*" multiple
                        @change="selectFiles($event)" class="hidden">

                    <!-- Hidden inputs for images marked to delete -->
                    <template x-for="delPath in toDelete" :key="delPath">
                        <input type="hidden" name="delete_gallery[]" :value="delPath">
                    </template>

                    <!-- Existing Gallery Images -->
                    <template x-if="existing.length > 0">
                        <div class="space-y-1.5 pt-1">
                            <div
                                class="flex items-center justify-between text-xs font-bold text-slate-600 px-1 border-b border-slate-200/60 pb-1">
                                <span>{{ __('Current Photos') }}</span>
                                <span class="text-slate-500 text-[11px]"
                                    x-text="(existing.length - toDelete.length) + ' {{ __('active') }}'"></span>
                            </div>
                            <div class="flex flex-wrap items-center gap-3 pt-1">
                                <template x-for="img in existing" :key="img">
                                    <div class="relative group w-24 h-24 rounded-xl overflow-hidden border border-slate-200 bg-slate-100 shadow-2xs shrink-0 transition-all"
                                        :class="toDelete.includes(img) ? 'opacity-40 grayscale border-rose-300' : ''">
                                        <img :src="'/storage/' + img" class="w-full h-full object-cover">

                                        <!-- If not marked to delete: show remove button -->
                                        <button type="button" x-show="!toDelete.includes(img)" @click="markDelete(img)"
                                            class="absolute top-1 right-1 w-5 h-5 rounded-full bg-rose-600 text-white flex items-center justify-center shadow-md hover:bg-rose-700 transition-colors text-xs font-black cursor-pointer"
                                            title="Delete photo">
                                            ✕
                                        </button>

                                        <!-- If marked to delete: show restore button -->
                                        <button type="button" x-show="toDelete.includes(img)" @click="unmarkDelete(img)"
                                            class="absolute inset-0 bg-rose-900/75 text-white flex flex-col items-center justify-center gap-1 text-[10px] font-extrabold cursor-pointer">
                                            <span>{{ __('Marked to Delete') }}</span>
                                            <span class="underline">{{ __('Undo') }}</span>
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    <!-- Drag & Drop Zone for New Photos -->
                    <div x-show="totalCount < 6" @dragover.prevent="isDragging = true"
                        @dragleave.prevent="isDragging = false" @drop.prevent="dropFiles($event)"
                        :class="isDragging ? 'border-blue-500 bg-blue-50/60' : 'border-slate-300 bg-white'"
                        class="border border-dashed rounded-xl p-4 text-center transition-all cursor-pointer mt-2"
                        @click="$refs.hiddenFileInput.click()">

                        <div x-show="newFiles.length === 0" class="py-2 space-y-1">
                            <p class="text-xs font-bold text-slate-700">{{ __('messages.drop_files_here_or') }} <span
                                    class="text-blue-600 underline">{{ __('messages.browse') }}</span></p>
                            <p class="text-xs text-slate-400 font-medium">{{ __('messages.drop_files_subtitle') }}</p>
                        </div>

                        <div x-show="newFiles.length > 0" class="space-y-2" @click.stop x-cloak>
                            <div
                                class="flex items-center justify-between text-xs font-bold text-slate-600 px-1 border-b border-slate-100 pb-1.5">
                                <span>{{ __('messages.selected_showcase_photos') }}</span>
                                <span
                                    class="text-blue-600 font-extrabold bg-blue-50 px-2.5 py-0.5 rounded-md border border-blue-200/80"
                                    x-text="totalCount + '/6 {{ __('messages.selected') }}'"></span>
                            </div>
                            <div class="flex flex-wrap items-center gap-3 pt-1">
                                <template x-for="(f, idx) in newFiles" :key="f.id">
                                    <div
                                        class="relative group w-24 h-24 rounded-xl overflow-hidden border border-slate-200 bg-slate-100 shadow-2xs shrink-0">
                                        <img :src="f.url" class="w-full h-full object-cover">

                                        <!-- Remove Button -->
                                        <button type="button" @click.stop="removeNewFile(idx)"
                                            class="absolute top-1 right-1 w-5 h-5 rounded-full bg-rose-600 text-white flex items-center justify-center shadow-md hover:bg-rose-700 transition-colors text-xs font-black cursor-pointer"
                                            title="Remove photo">
                                            ✕
                                        </button>

                                        <!-- File Name Bar -->
                                        <div
                                            class="absolute bottom-0 inset-x-0 bg-slate-900/80 text-white p-0.5 text-[9px] truncate font-semibold backdrop-blur-xs text-center">
                                            <span x-text="f.name"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Row 7: Description & Address -->
                <div class="space-y-1 md:col-span-3">
                    <label
                        class="text-xs font-bold text-slate-700 uppercase tracking-wider">{{ __('messages.business_desc_label') }}
                        <span class="text-slate-400 font-normal">({{ __('messages.optional') }})</span></label>
                    <textarea name="description" rows="2" placeholder="{{ __('messages.business_desc_placeholder') }}"
                        class="w-full text-sm font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:border-primary-500 focus:ring-0">{{ old('description', $business->description) }}</textarea>
                    @error('description')
                        <p class="text-xs text-rose-600 font-bold mt-0.5">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-1 md:col-span-3">
                    <label
                        class="text-xs font-bold text-slate-700 uppercase tracking-wider">{{ __('messages.office_address_label') }}
                        <span class="text-rose-500">*</span></label>
                    <textarea name="address" rows="2" required placeholder="{{ __('messages.office_address_placeholder') }}"
                        class="w-full text-sm font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:border-primary-500 focus:ring-0">{{ old('address', $business->address) }}</textarea>
                    @error('address')
                        <p class="text-xs text-rose-600 font-bold mt-0.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Row 8: Optional Password Change Section -->
                <div class="md:col-span-3 bg-slate-50/80 border border-slate-200/80 rounded-xl p-3.5 space-y-3">
                    <div class="flex items-center justify-between border-b border-slate-200/60 pb-2">
                        <div>
                            <span class="text-xs sm:text-sm font-bold text-slate-800 uppercase tracking-wide">🔐 {{ __('Change Password') }}</span>
                            <p class="text-[11px] text-slate-500 font-medium">{{ __('Leave password fields empty to keep your current password.') }}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="space-y-1">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">{{ __('Current Password') }}</label>
                                @if(empty($business->password) && (!$business->user || empty($business->user->password)))
                                    <span class="text-[10px] text-amber-600 font-bold">{{ __('(optional - not set yet)') }}</span>
                                @endif
                            </div>
                            <div class="relative">
                                <input type="password" id="current_password" name="current_password"
                                    placeholder="{{ __('Current password') }}"
                                    class="w-full text-sm font-semibold px-3 py-2 pr-9 bg-white border border-slate-200 rounded-lg focus:border-primary-500 focus:ring-0 @error('current_password') border-rose-400 @enderror">
                                <button type="button" onclick="togglePasswordVisibility('current_password', this)"
                                    class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700 text-sm cursor-pointer p-1"
                                    title="Show/Hide Password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            @error('current_password')
                            <p class="text-xs text-rose-600 font-bold mt-0.5">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">{{ __('New Password') }}</label>
                            <div class="relative">
                                <input type="password" id="new_password" name="password" placeholder="{{ __('Min 6 characters') }}"
                                    class="w-full text-sm font-semibold px-3 py-2 pr-9 bg-white border border-slate-200 rounded-lg focus:border-primary-500 focus:ring-0 @error('password') border-rose-400 @enderror"
                                    minlength="6">
                                <button type="button" onclick="togglePasswordVisibility('new_password', this)"
                                    class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700 text-sm cursor-pointer p-1"
                                    title="Show/Hide Password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            @error('password')
                            <p class="text-xs text-rose-600 font-bold mt-0.5">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">{{ __('Confirm New Password') }}</label>
                            <div class="relative">
                                <input type="password" id="confirm_password" name="password_confirmation"
                                    placeholder="{{ __('Repeat new password') }}"
                                    class="w-full text-sm font-semibold px-3 py-2 pr-9 bg-white border border-slate-200 rounded-lg focus:border-primary-500 focus:ring-0"
                                    minlength="6">
                                <button type="button" onclick="togglePasswordVisibility('confirm_password', this)"
                                    class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700 text-sm cursor-pointer p-1"
                                    title="Show/Hide Password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button Row -->
                <div class="md:col-span-3 flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <a href="{{ route('business.profile.edit') }}"
                        class="px-5 py-2.5 rounded-xl border border-slate-200 text-xs font-extrabold text-slate-600 hover:bg-slate-50 transition-all">
                        {{ __('Cancel') }}
                    </a>
                    <button type="submit" id="saveProfileBtn" :disabled="isSubmitting"
                        :class="isSubmitting ? 'opacity-75 cursor-wait pointer-events-none' : 'active:scale-95 cursor-pointer'"
                        class="inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-extrabold text-xs uppercase tracking-wider rounded-xl shadow-xs transition-all min-w-[150px]">
                        <span x-show="!isSubmitting" class="inline-flex items-center gap-2">
                            <i class="fas fa-save"></i>
                            <span>{{ __('Save Changes') }}</span>
                        </span>
                        <span x-show="isSubmitting" x-cloak class="inline-flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4 text-white shrink-0" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                                </circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            <span>{{ __('Saving...') }}</span>
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ══ Business OTP Alert Modal (warnings/errors) ══ -->
    <div id="bizOtpAlertModal" class="fixed inset-0 items-center justify-center hidden"
        style="display:none !important;position:fixed;top:0;left:0;width:100vw;height:100vh;z-index:999999 !important;"
        role="dialog" aria-modal="true">
        <div id="bizOtpModalBackdrop"
            style="position:absolute;inset:0;background-color:rgba(0,0,0,0.65);backdrop-filter:blur(4px);"></div>
        <div class="relative w-full max-w-sm mx-4 bg-white rounded-2xl shadow-2xl overflow-hidden transform transition-all duration-300 scale-95 opacity-0"
            id="bizOtpModalPanel">
            <div id="bizOtpModalAccent" class="h-1.5 w-full bg-rose-500"></div>
            <div class="p-6">
                <div class="flex items-start gap-3 mb-3">
                    <div id="bizOtpModalIcon"
                        class="shrink-0 w-10 h-10 rounded-xl flex items-center justify-center text-xl bg-rose-100 text-rose-600">
                        ⚠️</div>
                    <div>
                        <h3 id="bizOtpAlertTitle" class="text-sm font-extrabold text-slate-900 leading-tight">{{ __('Notice') }}</h3>
                        <p id="bizOtpAlertMessage" class="text-xs text-slate-600 mt-1 leading-relaxed"></p>
                    </div>
                </div>
                <div class="flex justify-end pt-2">
                    <button id="bizOtpModalCloseBtn" type="button"
                        class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white font-extrabold text-xs rounded-xl transition-all active:scale-95 shadow cursor-pointer">{{ __('OK, Got It') }}</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ══ Business OTP Entry Popup Modal ══ -->
    <div id="bizOtpEntryModal" class="fixed inset-0 items-center justify-center hidden"
        style="display:none !important;position:fixed;top:0;left:0;width:100vw;height:100vh;z-index:999998 !important;"
        role="dialog" aria-modal="true">
        <div style="position:absolute;inset:0;background-color:rgba(0,0,0,0.65);backdrop-filter:blur(4px);"></div>
        <div class="relative w-full max-w-sm mx-4 bg-white rounded-2xl shadow-2xl overflow-hidden transform transition-all duration-300 scale-95 opacity-0"
            id="bizOtpEntryPanel">

            <!-- Header -->
            <div style="background:linear-gradient(135deg,#6366f1 0%,#4f46e5 100%);padding:20px 24px;">
                <div class="flex items-center gap-3">
                    <div
                        style="width:44px;height:44px;background:rgba(255,255,255,0.18);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0;">
                        📧</div>
                    <div>
                        <h3 style="color:#fff;font-size:15px;font-weight:800;margin:0;">{{ __('Email Verification') }}</h3>
                        <p style="color:#c7d2fe;font-size:11px;margin:3px 0 0 0;">{{ __('We sent a 6-digit OTP to your inbox') }}</p>
                    </div>
                </div>
            </div>

            <!-- Body -->
            <div class="p-5 space-y-4">
                <!-- Target email -->
                <div class="text-center">
                    <p class="text-xs text-slate-500">{{ __('Verification code sent to') }}</p>
                    <p id="bizOtpTargetEmail" class="text-sm font-extrabold break-all mt-0.5" style="color:#6366f1;"></p>
                </div>

                <!-- OTP Input -->
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-600 uppercase tracking-widest block">{{ __('Enter OTP') }} <span
                            class="text-rose-500">*</span></label>
                    <input type="text" id="bizOtpInput" inputmode="numeric" maxlength="6" placeholder="0  0  0  0  0  0"
                        style="width:100%;font-size:22px;font-weight:900;letter-spacing:0.35em;text-align:center;padding:12px 16px;background:#f8fafc;border:2px solid #e2e8f0;border-radius:12px;outline:none;transition:border-color 0.2s,box-shadow 0.2s;box-sizing:border-box;"
                        onfocus="this.style.borderColor='#6366f1';this.style.boxShadow='0 0 0 3px rgba(99,102,241,0.15)'"
                        onblur="this.style.borderColor='#e2e8f0';this.style.boxShadow='none'">
                    <div id="bizOtpStatusMsg" class="text-xs font-semibold min-h-[18px] text-center"></div>
                </div>

                <!-- Resend -->
                <p class="text-center" style="font-size:11px;color:#94a3b8;margin:0;">
                    {{ __("Didn't receive the code?") }} &nbsp;
                    <button type="button" id="bizResendOtpBtn"
                        style="font-weight:700;color:#6366f1;text-decoration:underline;cursor:pointer;background:none;border:none;padding:0;outline:none;">{{ __('Resend OTP') }}</button>
                    <span id="bizOtpResendTimer" style="color:#94a3b8;font-weight:600;"></span>
                </p>
            </div>

            <!-- Footer -->
            <div style="display:flex;gap:10px;padding:0 20px 20px;">
                <button type="button" id="bizCancelOtpModalBtn"
                    style="flex:1;padding:10px;border:1.5px solid #e2e8f0;background:#f8fafc;color:#475569;font-weight:700;font-size:12px;border-radius:10px;cursor:pointer;transition:background 0.15s;"
                    onmouseover="this.style.background='#f1f5f9'"
                    onmouseout="this.style.background='#f8fafc'">{{ __('Cancel') }}</button>
                <button type="button" id="bizVerifyOtpBtn"
                    style="flex:1;padding:10px;background:#16a34a;color:#fff;font-weight:800;font-size:12px;border-radius:10px;cursor:pointer;border:none;box-shadow:0 2px 8px rgba(22,163,74,0.3);transition:background 0.15s;"
                    onmouseover="this.style.background='#15803d'" onmouseout="this.style.background='#16a34a'">
                    <span id="bizVerifyOtpBtnText">{{ __('Verify OTP') }}</span>
                </button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // ════════════════════════════════════════════════════════════════════
        // Business Profile – Email Change & OTP Verification Modal Handlers
        // ════════════════════════════════════════════════════════════════════
        document.addEventListener('DOMContentLoaded', function () {
            const emailInput = document.getElementById('bizEmailInput');
            const sendOtpBtn = document.getElementById('bizSendOtpBtn');
            const sendOtpBtnText = document.getElementById('bizSendOtpBtnText');
            const changeEmailBtn = document.getElementById('bizChangeEmailBtn');
            const emailVerifiedBadge = document.getElementById('bizEmailVerifiedBadge');

            // OTP Entry Modal
            const otpEntryModal = document.getElementById('bizOtpEntryModal');
            const otpEntryPanel = document.getElementById('bizOtpEntryPanel');
            const otpTargetEmail = document.getElementById('bizOtpTargetEmail');
            const otpInput = document.getElementById('bizOtpInput');
            const verifyOtpBtn = document.getElementById('bizVerifyOtpBtn');
            const verifyOtpBtnText = document.getElementById('bizVerifyOtpBtnText');
            const otpStatusMsg = document.getElementById('bizOtpStatusMsg');
            const cancelOtpBtn = document.getElementById('bizCancelOtpModalBtn');
            const resendOtpBtn = document.getElementById('bizResendOtpBtn');
            const otpResendTimer = document.getElementById('bizOtpResendTimer');

            // Alert Modal
            const alertModal = document.getElementById('bizOtpAlertModal');
            const alertPanel = document.getElementById('bizOtpModalPanel');
            const alertAccent = document.getElementById('bizOtpModalAccent');
            const alertIcon = document.getElementById('bizOtpModalIcon');
            const alertTitle = document.getElementById('bizOtpAlertTitle');
            const alertMessage = document.getElementById('bizOtpAlertMessage');
            const alertCloseBtn = document.getElementById('bizOtpModalCloseBtn');
            const alertBackdrop = document.getElementById('bizOtpModalBackdrop');

            // Move modals to <body> so they sit above all sidebars and sticky headers
            [alertModal, otpEntryModal].forEach(el => {
                if (el && el.parentElement !== document.body) document.body.appendChild(el);
            });

            let resendInterval = null;
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

            // Alert modal helpers
            const alertConfig = {
                warning: { accent: 'bg-amber-500', icon: '⚠️', iconBg: 'bg-amber-100 text-amber-600', btnBg: 'bg-amber-500 hover:bg-amber-600' },
                error: { accent: 'bg-rose-500', icon: '❌', iconBg: 'bg-rose-100 text-rose-600', btnBg: 'bg-rose-600 hover:bg-rose-700' },
                success: { accent: 'bg-emerald-500', icon: '✅', iconBg: 'bg-emerald-100 text-emerald-600', btnBg: 'bg-emerald-600 hover:bg-emerald-700' },
                info: { accent: 'bg-indigo-500', icon: 'ℹ️', iconBg: 'bg-indigo-100 text-indigo-600', btnBg: 'bg-indigo-600 hover:bg-indigo-700' },
            };

            function showAlert(message, type = 'warning', title = null) {
                const cfg = alertConfig[type] || alertConfig.warning;
                alertAccent.className = 'h-1.5 w-full ' + cfg.accent;
                alertIcon.className = 'shrink-0 w-10 h-10 rounded-xl flex items-center justify-center text-xl ' + cfg.iconBg;
                alertIcon.textContent = cfg.icon;
                alertCloseBtn.className = 'px-5 py-2 font-extrabold text-xs text-white rounded-xl transition-all active:scale-95 shadow cursor-pointer ' + cfg.btnBg;
                alertTitle.textContent = title || type.charAt(0).toUpperCase() + type.slice(1);
                alertMessage.textContent = message;
                alertModal.style.setProperty('display', 'flex', 'important');
                alertModal.classList.remove('hidden');
                setTimeout(() => { alertPanel.classList.remove('scale-95', 'opacity-0'); alertPanel.classList.add('scale-100', 'opacity-100'); }, 10);
            }

            function closeAlert() {
                alertPanel.classList.remove('scale-100', 'opacity-100'); alertPanel.classList.add('scale-95', 'opacity-0');
                setTimeout(() => {
                    alertModal.classList.add('hidden');
                    alertModal.style.setProperty('display', 'none', 'important');
                }, 200);
            }

            if (alertCloseBtn) alertCloseBtn.addEventListener('click', closeAlert);
            if (alertBackdrop) alertBackdrop.addEventListener('click', closeAlert);

            // "Change Email" button clicked
            if (changeEmailBtn) {
                changeEmailBtn.addEventListener('click', function () {
                    emailInput.readOnly = false;
                    emailInput.classList.remove('bg-slate-100', 'text-slate-600', 'cursor-not-allowed');
                    emailInput.classList.add('bg-white');
                    emailInput.focus();
                    changeEmailBtn.classList.add('hidden');
                    sendOtpBtn.classList.remove('hidden');
                    emailVerifiedBadge.classList.add('hidden');
                });
            }

            // OTP Modal helpers
            function openOtpModal(email) {
                if (otpTargetEmail) otpTargetEmail.textContent = email;
                if (otpInput) otpInput.value = '';
                if (otpStatusMsg) otpStatusMsg.innerHTML = '';
                otpEntryModal.style.setProperty('display', 'flex', 'important');
                otpEntryModal.classList.remove('hidden');
                setTimeout(() => {
                    otpEntryPanel.classList.remove('scale-95', 'opacity-0');
                    otpEntryPanel.classList.add('scale-100', 'opacity-100');
                    if (otpInput) otpInput.focus();
                }, 10);
            }

            function closeOtpModal() {
                otpEntryPanel.classList.remove('scale-100', 'opacity-100'); otpEntryPanel.classList.add('scale-95', 'opacity-0');
                setTimeout(() => {
                    otpEntryModal.classList.add('hidden');
                    otpEntryModal.style.setProperty('display', 'none', 'important');
                }, 200);
            }

            if (cancelOtpBtn) cancelOtpBtn.addEventListener('click', closeOtpModal);

            function setResendState(enabled, t = 0) {
                if (!resendOtpBtn) return;
                resendOtpBtn.disabled = !enabled;
                resendOtpBtn.style.color = enabled ? '#6366f1' : '#94a3b8';
                resendOtpBtn.style.cursor = enabled ? 'pointer' : 'not-allowed';
                resendOtpBtn.style.textDecoration = enabled ? 'underline' : 'none';
                resendOtpBtn.style.opacity = enabled ? '1' : '0.7';
                if (otpResendTimer) otpResendTimer.textContent = (!enabled && t > 0) ? ` (${t}s)` : '';
            }

            function startResendTimer(sec = 30) {
                let t = sec;
                clearInterval(resendInterval);
                setResendState(false, t);
                resendInterval = setInterval(() => {
                    t--;
                    if (t <= 0) { clearInterval(resendInterval); setResendState(true); }
                    else { setResendState(false, t); }
                }, 1000);
            }

            const i18n = {
                sending: "{{ __('Sending...') }}",
                sendOtp: "{{ __('Send OTP') }}",
                networkError: "{{ __('Network error. Please try again.') }}",
                connErrorTitle: "{{ __('Connection Error') }}",
                errorTitle: "{{ __('Error') }}",
                emailReqTitle: "{{ __('Email Required') }}",
                validEmailReq: "{{ __('Please enter a valid email address.') }}",
                sendingNewOtp: "{{ __('Sending new OTP...') }}",
                newOtpSent: "{{ __('✓ New OTP sent!') }}",
                failed: "{{ __('Failed') }}",
                enterSixDigit: "{{ __('Please enter the 6-digit OTP.') }}",
                verifyOtp: "{{ __('Verify OTP') }}",
                verifiedSuccess: "{{ __('Your business email has been updated and verified successfully.') }}",
                verifiedTitle: "{{ __('Email Verified') }}",
                invalidOtp: "{{ __('Invalid OTP.') }}"
            };

            function doSendOtp(email) {
                sendOtpBtn.disabled = true;
                sendOtpBtnText.textContent = i18n.sending;
                fetch('{{ route('business.profile.email.send_otp') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    body: JSON.stringify({ email })
                })
                    .then(r => r.json())
                    .then(data => {
                        sendOtpBtnText.textContent = i18n.sendOtp;
                        sendOtpBtn.disabled = false;
                        if (data.success) {
                            openOtpModal(email);
                            if (otpStatusMsg) otpStatusMsg.innerHTML = '<span style="color:#16a34a;font-weight:600;">✓ ' + (data.message || 'OTP sent successfully.') + '</span>';
                            startResendTimer(30);
                        } else {
                            showAlert(data.message || 'Failed to send OTP.', 'error', i18n.errorTitle);
                        }
                    })
                    .catch(() => {
                        sendOtpBtnText.textContent = i18n.sendOtp;
                        sendOtpBtn.disabled = false;
                        showAlert(i18n.networkError, 'error', i18n.connErrorTitle);
                    });
            }

            if (sendOtpBtn) {
                sendOtpBtn.addEventListener('click', function () {
                    const email = emailInput?.value.trim() || '';
                    if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                        showAlert(i18n.validEmailReq, 'warning', i18n.emailReqTitle);
                        emailInput?.focus();
                        return;
                    }
                    doSendOtp(email);
                });
            }

            if (resendOtpBtn) {
                resendOtpBtn.addEventListener('click', function () {
                    if (resendOtpBtn.disabled) return;
                    const email = emailInput?.value.trim() || '';
                    if (!email) return;
                    setResendState(false, 0);
                    if (otpStatusMsg) otpStatusMsg.innerHTML = '<span style="color:#6366f1;font-weight:600;">' + i18n.sendingNewOtp + '</span>';
                    fetch('{{ route('business.profile.email.send_otp') }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                        body: JSON.stringify({ email })
                    })
                        .then(r => r.json())
                        .then(data => {
                            if (data.success) {
                                if (otpInput) { otpInput.value = ''; otpInput.focus(); }
                                if (otpStatusMsg) otpStatusMsg.innerHTML = '<span style="color:#16a34a;font-weight:700;">' + i18n.newOtpSent + '</span>';
                                startResendTimer(30);
                            } else {
                                setResendState(true);
                                if (otpStatusMsg) otpStatusMsg.innerHTML = '<span style="color:#dc2626;font-weight:700;">❌ ' + (data.message || i18n.failed) + '</span>';
                            }
                        })
                        .catch(() => {
                            setResendState(true);
                            if (otpStatusMsg) otpStatusMsg.innerHTML = '<span style="color:#dc2626;">' + i18n.networkError + '</span>';
                        });
                });
            }

            if (verifyOtpBtn) {
                verifyOtpBtn.addEventListener('click', function () {
                    const email = emailInput?.value.trim() || '';
                    const otp = otpInput?.value.trim() || '';
                    if (!otp || otp.length !== 6) {
                        if (otpStatusMsg) otpStatusMsg.innerHTML = '<span style="color:#dc2626;font-weight:700;">' + i18n.enterSixDigit + '</span>';
                        otpInput?.focus();
                        return;
                    }
                    verifyOtpBtn.disabled = true;
                    verifyOtpBtnText.textContent = '...';
                    fetch('{{ route('business.profile.email.verify_otp') }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                        body: JSON.stringify({ email, otp })
                    })
                        .then(r => r.json())
                        .then(data => {
                            verifyOtpBtn.disabled = false;
                            verifyOtpBtnText.textContent = i18n.verifyOtp;
                            if (data.success) {
                                closeOtpModal();
                                clearInterval(resendInterval);
                                emailVerifiedBadge.classList.remove('hidden');
                                changeEmailBtn.classList.remove('hidden');
                                sendOtpBtn.classList.add('hidden');
                                emailInput.readOnly = true;
                                emailInput.classList.remove('bg-white');
                                emailInput.classList.add('bg-slate-100', 'text-slate-600', 'cursor-not-allowed');
                                showAlert(i18n.verifiedSuccess, 'success', i18n.verifiedTitle);
                            } else {
                                if (otpStatusMsg) otpStatusMsg.innerHTML = '<span style="color:#dc2626;font-weight:700;">' + (data.message || i18n.invalidOtp) + '</span>';
                            }
                        })
                        .catch(() => {
                            verifyOtpBtn.disabled = false;
                            verifyOtpBtnText.textContent = i18n.verifyOtp;
                            if (otpStatusMsg) otpStatusMsg.innerHTML = '<span style="color:#dc2626;">' + i18n.networkError + '</span>';
                        });
                });
            }

            // Form submission spinner fallback handler
            const profileForm = document.getElementById('profileForm');
            const saveProfileBtn = document.getElementById('saveProfileBtn');
            if (profileForm && saveProfileBtn) {
                profileForm.addEventListener('submit', function () {
                    if (profileForm.checkValidity()) {
                        saveProfileBtn.disabled = true;
                        saveProfileBtn.classList.add('opacity-75', 'cursor-wait', 'pointer-events-none');
                        saveProfileBtn.innerHTML = `
                        <span class="inline-flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4 text-white shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Saving...</span>
                        </span>
                    `;
                    }
                });
            }
        });
    </script>
@endpush