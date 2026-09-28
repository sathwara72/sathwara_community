@extends('layouts.public')

@section('content')
    <!-- Business Banner / Header -->
    <section class="bg-gradient-to-br from-slate-900 via-slate-950 to-slate-900 py-8 text-white relative overflow-hidden">
        <div
            class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-primary-500/10 via-transparent to-transparent">
        </div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <!-- Back Button -->
            <div class="mb-4">
                <a href="{{ route('business.directory') }}"
                    class="inline-flex items-center gap-1.5 text-xs text-slate-400 hover:text-white font-bold transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    {{ __('messages.back_to_directory') }}
                </a>
            </div>

            <div class="flex flex-col md:flex-row items-center gap-5">
                <div class="w-20 h-20 rounded-2xl bg-white border-2 border-slate-700 shadow-xl shrink-0 p-1 flex items-center justify-center overflow-hidden">
                    <img class="w-full h-full object-contain"
                        src="{{ str_starts_with($business->logo_path, 'http') ? $business->logo_path : asset('storage/' . $business->logo_path) }}"
                        alt="{{ $business->business_name }}">
                </div>
                <div class="text-center md:text-left space-y-1.5">
                    <span
                        class="text-[10px] font-black text-primary-400 bg-primary-500/10 border border-primary-500/20 px-3 py-0.5 rounded-full uppercase tracking-wider inline-block">
                        {{ $business->category?->name ?? __('messages.general') }}
                    </span>
                    <h1 class="text-2xl md:text-3xl font-black tracking-tight">{{ $business->business_name }}</h1>
                    <p class="text-xs text-slate-400 font-semibold">{{ __('messages.owned_by') }}: <span
                            class="text-white font-bold">{{ $business->owner_name }}</span></p>
                </div>
            </div>
        </div>
    </section>

    <!-- Details Body -->
    <section class="py-6 bg-slate-50/50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-5">
            
            @if($business->status !== 'approved')
                <div class="p-4 rounded-2xl flex items-center justify-between gap-3 flex-wrap {{ $business->status === 'pending' ? 'bg-amber-50 border border-amber-200 text-amber-900' : 'bg-rose-50 border border-rose-200 text-rose-900' }}">
                    <div class="flex items-center gap-2.5 text-xs font-bold">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider {{ $business->status === 'pending' ? 'bg-amber-200 text-amber-900' : 'bg-rose-200 text-rose-900' }}">
                            {{ strtoupper($business->status) }}
                        </span>
                        <span>
                            @if($business->status === 'pending')
                                {{ (app()->getLocale() === 'gu') ? 'આ વ્યવસાય એડમિન મંજૂરી માટે પેન્ડિંગ છે અને માત્ર તમને (માલિકને) પ્રિવ્યુ તરીકે દેખાય છે.' : 'This business listing is pending admin approval and is currently visible only to you in preview mode.' }}
                            @else
                                {{ (app()->getLocale() === 'gu') ? 'આ વ્યવસાય માન્ય કરેલ નથી.' : 'This business listing has not been approved.' }}
                            @endif
                        </span>
                    </div>
                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-500 bg-white/70 px-2 py-0.5 rounded-md border border-slate-200/60">
                        Owner Preview
                    </span>
                </div>
            @endif

            <!-- SECTION 1: ABOUT THE BUSINESS -->
            <div class="bg-white border border-slate-200/60 rounded-2xl p-5 shadow-xs space-y-3">
                <h2
                    class="text-xs font-black text-slate-800 uppercase tracking-wider pb-2 border-b border-slate-100 flex items-center gap-2">
                    <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                        </path>
                    </svg>
                    {{ __('messages.about_the_business') }}
                </h2>
                @if($business->description)
                    <p class="text-xs text-slate-600 leading-relaxed whitespace-pre-line font-medium">
                        {!! e($business->description) !!}</p>
                @else
                    <div
                        class="text-center py-4 text-slate-400 font-bold text-xs bg-slate-50 rounded-xl border border-slate-100">
                        {{ __('messages.no_business_description') }}
                    </div>
                @endif
            </div>

            <!-- SECTION 2: PRODUCT / WORK GALLERY -->
            @if(is_array($business->gallery_images) && count($business->gallery_images) > 0)
                <div class="bg-white border border-slate-200/60 rounded-2xl p-5 shadow-xs space-y-4">
                    <h2
                        class="text-xs font-black text-slate-800 uppercase tracking-wider pb-2 border-b border-slate-100 flex items-center gap-2">
                        <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                            </path>
                        </svg>
                        {{ __('messages.product_portfolio_gallery') }}
                    </h2>
                    <div class="grid grid-cols-3 sm:grid-cols-6 gap-3" x-data="{ 
                                 lightbox: false, 
                                 lightboxIndex: 0,
                                 galleryImages: [
                                     @foreach($business->gallery_images as $img)
                                         {
                                             src: '{{ str_starts_with($img, 'http') ? $img : asset('storage/' . $img) }}',
                                             caption: '{{ addslashes($business->business_name) }} {{ __('messages.portfolio_image') }}'
                                         },
                                     @endforeach
                                 ],
                                 init() {
                                     this.$nextTick(() => {
                                         if (this.$refs.lightboxModal) {
                                             document.body.appendChild(this.$refs.lightboxModal);
                                         }
                                     });
                                 },
                                 nextImage() {
                                     if (this.galleryImages.length > 0) {
                                         this.lightboxIndex = (this.lightboxIndex + 1) % this.galleryImages.length;
                                     }
                                 },
                                 prevImage() {
                                     if (this.galleryImages.length > 0) {
                                         this.lightboxIndex = (this.lightboxIndex - 1 + this.galleryImages.length) % this.galleryImages.length;
                                     }
                                 }
                             }" 
                             @keydown.window.escape="lightbox = false"
                             @keydown.window.right="if(lightbox) nextImage()"
                             @keydown.window.left="if(lightbox) prevImage()">
                        @foreach($business->gallery_images as $idx => $img)
                            @php $bImgUrl = str_starts_with($img, 'http') ? $img : asset('storage/' . $img); @endphp
                            <div @click="lightboxIndex = {{ $idx }}; lightbox = true"
                                class="relative aspect-square rounded-xl overflow-hidden bg-slate-950 border border-slate-200/80 group cursor-pointer shadow-2xs hover:shadow-md transition-all duration-300 flex items-center justify-center">
                                <!-- Blurred Background Image -->
                                <img src="{{ $bImgUrl }}"
                                    alt=""
                                    aria-hidden="true"
                                    class="absolute inset-0 w-full h-full pointer-events-none select-none"
                                    style="object-fit: cover; object-position: center; filter: blur(14px) brightness(0.45); transform: scale(1.12); z-index: 0;">

                                <!-- Main Image (Full object-contain, never cropped) -->
                                <img src="{{ $bImgUrl }}"
                                    alt="Gallery image"
                                    class="relative w-full h-full object-contain group-hover:scale-105 transition-transform duration-500"
                                    style="z-index: 1;">

                                <div
                                    class="absolute inset-0 bg-black/30 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center" style="z-index: 10;">
                                    <span
                                        class="p-2 rounded-full bg-slate-900/80 text-white border border-white/20 backdrop-blur-xs transform translate-y-2 group-hover:translate-y-0 transition-transform shadow-md">
                                        <svg class="w-4 h-4 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7">
                                            </path>
                                        </svg>
                                    </span>
                                </div>
                            </div>
                        @endforeach

                        <!-- LIGHTBOX MODAL -->
                        <div x-ref="lightboxModal" x-show="lightbox" x-transition.opacity.duration.300ms
                            class="fixed inset-0 bg-slate-950/95 backdrop-blur-md flex flex-col justify-between"
                            style="z-index: 9999999 !important;"
                            @click="lightbox = false" x-cloak>

                            <!-- Prev/Next Navigation Controls -->
                            <button @click.stop="prevImage()"
                                class="absolute left-3 sm:left-6 top-1/2 -translate-y-1/2 z-[10000000] group p-3 rounded-full bg-slate-900/80 hover:bg-white text-white hover:text-slate-900 border border-white/20 hover:border-white transition-all duration-300 shadow-2xl hover:scale-110 active:scale-95"
                                title="Previous Image (←)">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path>
                                </svg>
                            </button>

                            <button @click.stop="nextImage()"
                                class="absolute right-3 sm:right-6 top-1/2 -translate-y-1/2 z-[10000000] group p-3 rounded-full bg-slate-900/80 hover:bg-white text-white hover:text-slate-900 border border-white/20 hover:border-white transition-all duration-300 shadow-2xl hover:scale-110 active:scale-95"
                                title="Next Image (→)">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </button>

                            <!-- Top Bar: Close Button -->
                            <div class="w-full flex items-center justify-end z-[10000000] max-w-7xl mx-auto pt-4 px-4 sm:px-6"
                                @click.stop>
                                <button @click="lightbox = false"
                                    class="group p-2.5 rounded-full bg-slate-900/80 hover:bg-rose-500 text-white border border-white/20 hover:border-rose-400 transition-all duration-300 cursor-pointer shadow-xl hover:rotate-90 hover:scale-110 active:scale-95"
                                    title="Close (Esc)">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>

                            <!-- Main Section: Centered Image -->
                            <div class="relative w-full flex-1 flex items-center justify-center my-auto max-w-5xl mx-auto px-4 pb-14"
                                @click.stop>
                                <div
                                    class="relative flex flex-col items-center justify-center max-w-full max-h-full">
                                    <div
                                        class="relative rounded-2xl overflow-hidden shadow-2xl border border-white/20 bg-slate-900/50">
                                        <img :src="galleryImages[lightboxIndex]?.src"
                                            :alt="galleryImages[lightboxIndex]?.caption || 'Gallery Image'"
                                            class="w-auto max-w-full object-contain rounded-xl shadow-2xl transition-all duration-300"
                                            style="max-height: 75vh; max-width: 85vw;">
                                    </div>
                                </div>
                            </div>

                            <!-- Bottom Bar: Image Numbers Counter -->
                            <div x-show="galleryImages.length > 0"
                                style="position: absolute; bottom: 1rem; left: 50%; transform: translateX(-50%); z-index: 10000000;"
                                class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full bg-slate-900/90 border border-white/20 text-white text-xs font-bold shadow-2xl backdrop-blur-md">
                                <span class="text-primary-400">🖼️</span>
                                <span>
                                    <span class="text-white" x-text="lightboxIndex + 1"></span>
                                    <span class="text-white/40"> / </span>
                                    <span class="text-white/70" x-text="galleryImages.length"></span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- SECTION 3: CONTACT INFORMATION (DIRECT DETAILS IN 4 COLUMNS, NO INNER CARD BOXES) -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200/60 shadow-xs space-y-4">
                <h3
                    class="text-xs font-black text-slate-800 uppercase tracking-wider pb-2 border-b border-slate-100 flex items-center gap-2">
                    <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                            d="M3 19v-8.93a2 2 0 01.89-1.664l8-4.666a2 2 0 012.22 0l8 4.666A2 2 0 0121 10.07V19M3 19a2 2 0 002 2h14a2 2 0 002-2M3 19l6.75-4.5M21 19l-6.75-4.5M3 10l6.75 4.5M21 10l-6.75 4.5m0 0l-2.25-1.5a2 2 0 00-2.22 0l-2.25 1.5">
                        </path>
                    </svg>
                    {{ __('messages.contact_information') }}
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 text-xs font-semibold text-slate-700">
                    
                    <!-- COLUMN 1: MEMBER ID -->
                    <div class="space-y-3">
                        @php
                            $memberName = null;
                            if ($business->user && $business->user->memberProfile) {
                                $p = $business->user->memberProfile;
                                $memberName = trim(collect([$p->first_name, $p->middle_name ?? null, $p->last_name])->filter()->implode(' '));
                            } elseif ($business->user) {
                                $memberName = $business->user->name;
                            }
                        @endphp
                        @if($memberName)
                            <div class="flex items-start gap-2.5">
                                <svg class="w-4 h-4 text-slate-400 mt-0.5 shrink-0" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                <div>
                                    <h4 class="text-[9px] text-slate-400 font-extrabold uppercase tracking-wide">{{ __('messages.member') }}</h4>
                                    <p class="text-slate-800 font-bold mt-0.5">{{ $memberName }}</p>
                                </div>
                            </div>
                        @endif
                    </div>



                    <!-- COLUMN 2: LOCATION & ADDRESS DETAILS -->
                    <div class="space-y-3">
                        @if($business->area)
                            <div class="flex items-start gap-2.5">
                                <svg class="w-4 h-4 text-slate-400 mt-0.5 shrink-0" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7">
                                    </path>
                                </svg>
                                <div>
                                    <h4 class="text-[9px] text-slate-400 font-extrabold uppercase tracking-wide">{{ __('messages.area_location') }}</h4>
                                    <p class="text-slate-800 font-bold mt-0.5">{{ $business->area->name }}</p>
                                </div>
                            </div>
                        @endif

                        <div class="flex items-start gap-2.5">
                            <svg class="w-4 h-4 text-slate-400 mt-0.5 shrink-0" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                </path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <div>
                                <h4 class="text-[9px] text-slate-400 font-extrabold uppercase tracking-wide">{{ __('messages.address') }}</h4>
                                <p class="text-slate-800 font-medium mt-0.5 leading-snug">{{ $business->address }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- COLUMN 3: PHONE & WHATSAPP -->
                    <div class="space-y-3">
                        @php
                            $cleanPhone = preg_replace('/[^0-9]/', '', $business->phone ?? '');
                            $cleanWhatsapp = preg_replace('/[^0-9]/', '', $business->whatsapp ?? '');
                            $isSameNumber = empty($cleanWhatsapp) || ($cleanPhone === $cleanWhatsapp);
                        @endphp

                        @if($isSameNumber)
                            <div class="flex items-start gap-2.5">
                                <svg class="w-4 h-4 text-emerald-500 mt-0.5 shrink-0" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.988-.321l2.2.548a1 1 0 01.725.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z">
                                    </path>
                                </svg>
                                <div class="space-y-1">
                                    <div class="flex items-center gap-1">
                                        <h4 class="text-[9px] text-slate-400 font-extrabold uppercase tracking-wide">{{ __('messages.phone_and_whatsapp') }}</h4>
                                        <span class="text-[8px] font-extrabold text-emerald-700 bg-emerald-50 px-1 py-0.2 rounded border border-emerald-100">{{ __('messages.same') }}</span>
                                    </div>
                                    <p class="text-slate-900 font-black text-xs">{{ $business->phone }}</p>

                                    <div class="flex items-center gap-1.5 pt-1">
                                        <a href="tel:{{ $business->phone }}"
                                            class="text-[9px] font-extrabold text-primary-600 bg-primary-50 hover:bg-primary-100 px-2 py-1 rounded transition-colors inline-flex items-center gap-1">
                                            📞 {{ __('messages.call') }}
                                        </a>
                                        <a href="https://wa.me/{{ $cleanPhone }}" target="_blank"
                                            class="text-[9px] font-extrabold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 px-2 py-1 rounded transition-colors inline-flex items-center gap-1">
                                            💬 {{ __('messages.whatsapp') }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="flex items-start gap-2.5">
                                <svg class="w-4 h-4 text-slate-400 mt-0.5 shrink-0" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.725.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z">
                                    </path>
                                </svg>
                                <div>
                                    <h4 class="text-[9px] text-slate-400 font-extrabold uppercase tracking-wide">{{ __('messages.phone_number') }}</h4>
                                    <a href="tel:{{ $business->phone }}"
                                        class="text-primary-500 font-bold hover:underline mt-0.5 inline-block text-xs">{{ $business->phone }}</a>
                                </div>
                            </div>

                            @if($business->whatsapp)
                                <div class="flex items-start gap-2.5">
                                    <svg class="w-4 h-4 text-emerald-500 mt-0.5 shrink-0" fill="currentColor"
                                        viewBox="0 0 24 24">
                                        <path
                                            d="M12.004 2C6.51 2 2.014 6.5 2.014 12c0 1.89.52 3.65 1.43 5.17L2.06 22l5.02-1.32c1.47.8 3.14 1.26 4.92 1.26 5.5 0 9.99-4.5 9.99-10S17.5 2 12.004 2zm4.8 13.9c-.2.58-1.16 1.09-1.6 1.15-.42.06-.9.1-2.9-.73-2.58-1.07-4.22-3.7-4.35-3.87-.13-.17-1.11-1.48-1.11-2.82 0-1.35.7-2 .95-2.27.26-.26.56-.33.74-.33h.53c.17 0 .39-.06.6.45.2.5.7 1.76.77 1.9.07.13.11.3.02.48-.09.18-.13.3-.27.46-.14.16-.3.35-.43.47-.15.15-.3.32-.13.62.17.3.74 1.22 1.59 1.97.92.8 1.69 1.05 1.93 1.17.24.12.38.1.52-.06.14-.16.6-7.01.76-.94.16-.14.32-.12.54-.04.22.08 1.4.66 1.64.78.25.12.41.18.47.28.06.11.06.63-.14 1.21z" />
                                    </svg>
                                    <div>
                                        <h4 class="text-[9px] text-slate-400 font-extrabold uppercase tracking-wide">{{ __('messages.whatsapp_number') }}</h4>
                                        <a href="https://wa.me/{{ $cleanWhatsapp }}" target="_blank"
                                            class="text-emerald-500 font-bold hover:underline mt-0.5 inline-block text-xs">{{ $business->whatsapp }}</a>
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>

                    <!-- COLUMN 4: EMAIL, WEBSITE & SOCIAL CONNECTIONS -->
                    <div class="space-y-3">
                        @if($business->email)
                            <div class="flex items-start gap-2.5">
                                <svg class="w-4 h-4 text-slate-400 mt-0.5 shrink-0" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                    </path>
                                </svg>
                                <div>
                                    <h4 class="text-[9px] text-slate-400 font-extrabold uppercase tracking-wide">{{ __('messages.email_address') }}</h4>
                                    <a href="mailto:{{ $business->email }}"
                                        class="text-slate-700 hover:underline mt-0.5 inline-block truncate max-w-[150px] font-bold">{{ $business->email }}</a>
                                </div>
                            </div>
                        @endif

                        @if($business->website)
                            <div class="flex items-start gap-2.5">
                                <svg class="w-4 h-4 text-slate-400 mt-0.5 shrink-0" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9">
                                    </path>
                                </svg>
                                <div>
                                    <h4 class="text-[9px] text-slate-400 font-extrabold uppercase tracking-wide">{{ __('messages.website') }}</h4>
                                    <a href="{{ $business->website }}" target="_blank"
                                        class="text-primary-500 hover:underline mt-0.5 inline-block truncate max-w-[150px] font-bold">{{ $business->website }}</a>
                                </div>
                            </div>
                        @endif

                        @if($business->facebook || $business->instagram || $business->youtube || $business->linkedin)
                            <div class="pt-1">
                                <h4 class="text-[9px] text-slate-400 font-extrabold uppercase tracking-wide mb-1.5">{{ __('messages.social_connections') }}</h4>
                                <div class="flex flex-wrap gap-1.5">
                                    @if($business->facebook)
                                        <a href="{{ $business->facebook }}" target="_blank"
                                            class="w-6 h-6 rounded-md bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors flex items-center justify-center"
                                            title="Facebook">
                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                                                <path
                                                    d="M22 12c0-5.52-4.48-10-10-10S2 6.48 2 12c0 4.84 3.44 8.87 8 9.8V15H8v-3h2V9.5C10 7.57 11.57 6 13.5 6H16v3h-2c-.55 0-1 .45-1 1v2h3v3h-3v6.95c4.56-.93 8-4.96 8-9.75z" />
                                            </svg>
                                        </a>
                                    @endif
                                    @if($business->instagram)
                                        <a href="{{ $business->instagram }}" target="_blank"
                                            class="w-6 h-6 rounded-md bg-pink-50 text-pink-600 hover:bg-pink-100 transition-colors flex items-center justify-center"
                                            title="Instagram">
                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                                                <path
                                                    d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.051.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z" />
                                            </svg>
                                        </a>
                                    @endif
                                    @if($business->youtube)
                                        <a href="{{ $business->youtube }}" target="_blank"
                                            class="w-6 h-6 rounded-md bg-rose-50 text-rose-600 hover:bg-rose-100 transition-colors flex items-center justify-center"
                                            title="YouTube">
                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                                                <path
                                                    d="M23.498 6.163a3.003 3.003 0 00-2.11-2.11C19.518 3.545 12 3.545 12 3.545s-7.518 0-9.388.508a3.003 3.003 0 00-2.11 2.11C0 8.033 0 12 0 12s0 3.967.502 5.837a3.003 3.003 0 002.11 2.11c1.87.508 9.388.508 9.388.508s7.518 0 9.388-.508a3.003 3.003 0 002.11-2.11C24 15.967 24 12 24 12s0-3.967-.502-5.837zM9.545 15.568V8.432L15.818 12l-6.273 3.568z" />
                                            </svg>
                                        </a>
                                    @endif
                                    @if($business->linkedin)
                                        <a href="{{ $business->linkedin }}" target="_blank"
                                            class="w-6 h-6 rounded-md bg-indigo-50 text-indigo-600 hover:bg-indigo-100 transition-colors flex items-center justify-center"
                                            title="LinkedIn">
                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                                                <path
                                                    d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.779-1.75-1.75s.784-1.75 1.75-1.75 1.75.779 1.75 1.75-.784 1.75-1.75 1.75zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z" />
                                            </svg>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </section>
@endsection