@extends('layouts.public')

@section('content')
    <style>
        .hero-slider-container {
            width: 100%;
            height: 48vh;
            min-height: 240px;
            max-height: 420px;
            position: relative;
        }

        .hero-slider-controls {
            position: absolute !important;
            bottom: 12px !important;
            right: 12px !important;
            top: auto !important;
            left: auto !important;
            z-index: 40 !important;
            display: flex !important;
            flex-direction: column !important;
            align-items: flex-end !important;
            gap: 6px !important;
        }

        @media (min-width: 640px) {
            .hero-slider-container {
                height: 55vh;
                min-height: 360px;
                max-height: 520px;
            }
            .hero-slider-controls {
                bottom: 24px !important;
                right: 32px !important;
                flex-direction: row !important;
                align-items: center !important;
                gap: 12px !important;
            }
        }

        @media (min-width: 1024px) {
            .hero-slider-container {
                height: calc(100vh - 80px);
                min-height: 500px;
                max-height: 680px;
            }
        }

        /* =============================================
           HOME PAGE — FONT SIZE UPGRADES
           ============================================= */

        /* Section label (e.g. "AGENDA", "UPCOMING EVENTS") */
        .home-section-label {
            font-size: 13px !important;
        }

        /* Section h2 headings */
        section h2 {
            font-size: 1.75rem !important; /* 28px */
            line-height: 1.2 !important;
        }
        @media (min-width: 640px) {
            section h2 {
                font-size: 2.1rem !important; /* ~34px */
            }
        }

        /* Card / item h3 titles */
        section h3 {
            font-size: 15px !important;
            line-height: 1.4 !important;
        }

        /* Body / description paragraphs */
        section p {
            font-size: 13.5px !important;
            line-height: 1.7 !important;
        }

        /* Tiny badge-like spans (was text-[9px], text-[10px], text-[11px]) */
        section span[style*="font-size:10px"],
        section span[style*="font-size:8px"],
        section span[style*="font-size:9px"] {
            font-size: 12px !important;
        }

        /* Inline meta / venue / date small text */
        section .text-\[10px\],
        section .text-\[11px\],
        section .text-\[9px\],
        section .text-\[8px\] {
            font-size: 12px !important;
        }

        /* text-xs in sections */
        section .text-xs {
            font-size: 13px !important;
        }

        /* text-sm in sections */
        section .text-sm {
            font-size: 14.5px !important;
        }

        /* text-base in sections */
        section .text-base {
            font-size: 16px !important;
        }

        /* Gallery caption text */
        section p.text-sm,
        section p.text-base {
            font-size: 15px !important;
        }

        /* "View all" / action links */
        section a.text-xs {
            font-size: 13px !important;
        }

        /* Section label spans (sm:text-base) */
        section span.text-sm,
        section span.text-base {
            font-size: 13.5px !important;
        }

        /* ==============================================
           UPCOMING EVENTS & LATEST UPDATES — Font Boost
           ============================================== */

        /* Card h3 titles — bigger & bolder */
        section h3 {
            font-size: 16px !important;
            font-weight: 700 !important;
            line-height: 1.4 !important;
            color: #0f172a !important;
        }

        /* Card description paragraph */
        section p.line-clamp-2,
        section .text-\[11px\] {
            font-size: 13.5px !important;
            color: #475569 !important;
        }

        /* Venue / meta info */
        section span.text-\[10px\],
        section span.text-\[9px\],
        section span.text-\[8px\] {
            font-size: 12.5px !important;
        }

        /* Date badge, last-date badge */
        section span.text-\[9px\].font-extrabold,
        section span.text-\[8px\].font-black,
        section span.text-rose-600 {
            font-size: 12px !important;
        }

        /* "Read full post" link */
        section a.text-\[10px\] {
            font-size: 13px !important;
        }

        /* Update date meta badge */
        section span.text-\[9px\].font-extrabold.text-primary-700 {
            font-size: 12px !important;
        }
    </style>

    <!-- Edge-to-Edge Full Screen Hero Slider Section -->
    <section class="relative w-full overflow-hidden bg-slate-950 py-0 border-b border-slate-900" x-data="{ 
            activeSlide: 0,
            slides: {{ json_encode($sliders->toArray()) }},
            timer: null,
            next() { this.activeSlide = (this.activeSlide + 1) % (this.slides.length || 1) },
            prev() { this.activeSlide = (this.activeSlide - 1 + (this.slides.length || 1)) % (this.slides.length || 1) },
            startTimer() { this.timer = setInterval(() => this.next(), 6000) },
            resetTimer() { clearInterval(this.timer); this.startTimer(); },
            init() { if(this.slides.length > 0) this.startTimer(); }
        }">
        @if($sliders->count() > 0)
            <!-- Responsive Edge-to-Edge Slider Wrapper -->
            <div class="hero-slider-container relative w-full overflow-hidden bg-slate-950">

                @foreach($sliders as $idx => $slide)
                    @php $slideImgUrl = str_starts_with($slide->image_path, 'http') ? $slide->image_path : asset('storage/' . $slide->image_path); @endphp
                    <div x-show="activeSlide === {{ $idx }}" x-transition:enter="transition ease-out duration-700 transform"
                        x-transition:enter-start="opacity-0 scale-105" x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-500 transform absolute inset-0"
                        x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                        class="absolute inset-0 w-full h-full flex items-center">

                        <!-- Blurred Background Image (same image, scaled + blurred) -->
                        <img src="{{ $slideImgUrl }}"
                            alt=""
                            aria-hidden="true"
                            class="absolute inset-0 w-full h-full pointer-events-none select-none"
                            style="object-fit: cover; object-position: center; filter: blur(18px) brightness(0.45); transform: scale(1.12); z-index: 0;">

                        <!-- Main Slide Image (full, object-contain, on top) -->
                        <img src="{{ $slideImgUrl }}"
                            alt="{{ $slide->title ?? 'Banner Slide' }}"
                            class="relative w-full h-full transition-transform duration-1000 scale-100"
                            style="object-fit: contain; object-position: center; z-index: 1;">

                    </div>
                @endforeach

                <!-- Hero Slider Controls & Action Button (Bottom Right Corner on Mobile and Desktop) -->
                <div class="hero-slider-controls pointer-events-auto">

                    <!-- Dynamic Action Button (Shows if button_text is set) -->
                    <template
                        x-if="slides[activeSlide] && slides[activeSlide].button_text && slides[activeSlide].button_text.trim() !== ''">
                        <a :href="slides[activeSlide].button_link || '#'"
                            style="color: #ffffff !important;"
                            class="px-3.5 py-1.5 sm:px-6 sm:py-3 bg-primary-600 hover:bg-primary-500 !text-white font-extrabold text-[11px] sm:text-sm rounded-full shadow-lg border border-white/20 backdrop-blur-md transition-all duration-300 flex items-center gap-1.5 sm:gap-2 shrink-0 active:scale-95">
                            <span x-text="slides[activeSlide].button_text" style="color: #ffffff !important;"></span>
                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-white shrink-0" style="color: #ffffff !important; stroke: #ffffff !important;" fill="none" stroke="currentColor" stroke-width="2.5"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                            </svg>
                        </a>
                    </template>

                    <!-- Slider Arrow Navigation Buttons (Prev / Next & Counter) -->
                    <div
                        class="flex items-center gap-1 sm:gap-1.5 bg-slate-950/80 backdrop-blur-xl p-1 sm:p-1.5 rounded-full border border-white/20 text-white shadow-2xl shrink-0">
                        <!-- Prev Arrow Button -->
                        <button @click="prev(); resetTimer();"
                            class="w-7 h-7 sm:w-10 sm:h-10 bg-white/10 hover:bg-white/25 text-white rounded-full flex items-center justify-center transition-all duration-200 cursor-pointer active:scale-90"
                            aria-label="Previous slide">
                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" stroke-width="3"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                            </svg>
                        </button>

                        <!-- Slide Counter -->
                        <span
                            class="px-1.5 sm:px-2 text-[10px] sm:text-sm font-black tracking-widest text-slate-200 select-none"
                            x-text="(activeSlide + 1) + ' / ' + slides.length"></span>

                        <!-- Next Arrow Button -->
                        <button @click="next(); resetTimer();"
                            class="w-7 h-7 sm:w-10 sm:h-10 bg-white/10 hover:bg-white/25 text-white rounded-full flex items-center justify-center transition-all duration-200 cursor-pointer active:scale-90"
                            aria-label="Next slide">
                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" stroke-width="3"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                            </svg>
                        </button>
                    </div>

                </div>

            </div>
        @else
            <!-- Full Screen Fallback Hero Banner -->
            <div
                class="w-full h-[calc(100vh-80px)] min-h-[500px] flex items-center justify-center bg-gradient-to-br from-slate-900 via-slate-950 to-slate-900 text-center text-white px-4">
                <div class="max-w-4xl space-y-6">
                    <div
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/10 text-white text-xs font-bold border border-white/15 mx-auto">
                        <span class="w-2.5 h-2.5 rounded-full bg-primary-500"></span>
                        <span class="uppercase tracking-widest text-[11px]">{{ App\Models\Setting::get('website_name', 'Shree Satwara Gnati Mandal, Ahmedabad') }}</span>
                    </div>
                    <h1 class="text-4xl sm:text-6xl font-black text-white tracking-tight">
                        {{ __('messages.welcome') }} - {{ App\Models\Setting::get('website_name', 'Shree Satwara Gnati Mandal, Ahmedabad') }}
                    </h1>
                    <p class="text-slate-300 text-base sm:text-xl max-w-2xl mx-auto leading-relaxed">
                        {{ __('messages.home_fallback_desc') }}
                    </p>
                    <div class="pt-4">
                        <a href="{{ route('business.directory') }}"
                            class="inline-flex items-center justify-center gap-2.5 px-8 py-4 bg-primary-600 hover:bg-primary-500 text-white font-extrabold text-base rounded-2xl transition-all shadow-xl">
                            {{ __('messages.business_directory') }}
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </section>

    <!-- Agendas / Our Core Mission & Values Section -->
    <section class="py-6 sm:py-8 bg-transparent">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Clean Section Header -->
            <div class="text-center max-w-2xl mx-auto mb-10 space-y-2">
                <span
                    class="text-sm sm:text-base font-extrabold text-primary-600 uppercase tracking-widest">{{ __('messages.agenda') }}</span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                    {{ __('messages.core_mission_values') }}
                </h2>
                <p class="text-sm sm:text-base text-slate-600 font-medium">
                    {{ __('messages.core_mission_subtitle') }}
                </p>
            </div>

            <!-- Agendas Clean Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($agendas as $index => $agenda)
                    <div
                        class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-xs hover:border-slate-300 hover:shadow-md transition-all duration-300 flex flex-col justify-between space-y-4">
                        <div class="space-y-3">
                            <div
                                class="w-10 h-10 rounded-xl bg-primary-50 text-primary-600 flex items-center justify-center font-bold border border-primary-100">
                                @if($agenda->icon == 'users')
                                    <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                @elseif($agenda->icon == 'academic-cap')
                                    <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 14l9-5-9-5-9 5 9 5z"/><path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/></svg>
                                @elseif($agenda->icon == 'briefcase')
                                    <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                @else
                                    <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/></svg>
                                @endif
                            </div>

                            <h3 class="text-base font-bold text-slate-900">
                                {{ $agenda->localized_title }}
                            </h3>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                {{ $agenda->localized_description }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>


    <!-- Business & Member Join Highlight Sections -->
    @php
        $joinSections = [
            [
                'key' => 'business',
                'image' => collect(['jpg', 'png', 'webp', 'svg'])->map(fn ($ext) => 'images/home-business.' . $ext)->first(fn ($path) => file_exists(public_path($path))),
                'reverse' => false,
                'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
                'chips' => [__('messages.business_directory'), 'WhatsApp', __('messages.gallery')],
                'primary' => ['url' => route('register.business'), 'label' => __('messages.business_registration')],
                'secondary' => ['url' => route('business.login'), 'label' => __('messages.business_login')],
            ],
            [
                'key' => 'member',
                'image' => collect(['jpg', 'png', 'webp', 'svg'])->map(fn ($ext) => 'images/home-member.' . $ext)->first(fn ($path) => file_exists(public_path($path))),
                'reverse' => true,
                'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
                'chips' => [__('messages.member_directory'), __('messages.family_members'), __('messages.events')],
                'primary' => auth()->check()
                    ? ['url' => route('member.dashboard'), 'label' => __('messages.dashboard')]
                    : ['url' => route('register.member'), 'label' => __('messages.member_registration')],
                'secondary' => auth()->check() ? null : ['url' => route('login'), 'label' => __('messages.member_login')],
            ],
        ];
    @endphp

    @foreach($joinSections as $join)
        <section class="py-6 sm:py-10 bg-transparent">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Business: image left / content right. Member: content left / image right -->
                <div class="flex flex-col {{ $join['reverse'] ? 'md:flex-row-reverse' : 'md:flex-row' }} md:items-center gap-8 md:gap-10">

                    <!-- Visual -->
                    <div class="w-full md:w-1/2">
                        @if($join['image'])
                            <div class="rounded-3xl overflow-hidden shadow-xl border border-slate-200/80" style="aspect-ratio: 4 / 3;">
                                <img src="{{ asset($join['image']) }}" alt="{{ __('messages.home_' . $join['key'] . '_title') }}" class="w-full h-full object-cover" loading="lazy">
                            </div>
                        @else
                            <div class="relative rounded-3xl overflow-hidden bg-gradient-to-br {{ $join['key'] === 'business' ? 'from-slate-900 via-slate-950 to-slate-900' : 'from-primary-700 via-primary-600 to-primary-500' }} shadow-xl flex items-center justify-center" style="aspect-ratio: 4 / 3;">
                                <div class="absolute -right-12 -bottom-12 w-56 h-56 {{ $join['key'] === 'business' ? 'bg-primary-500/20' : 'bg-white/15' }} rounded-full blur-3xl pointer-events-none"></div>
                                <div class="absolute -left-12 -top-12 w-48 h-48 bg-amber-400/15 rounded-full blur-3xl pointer-events-none"></div>

                                <div class="relative w-24 h-24 sm:w-28 sm:h-28 rounded-3xl bg-white/10 border border-white/20 backdrop-blur-sm flex items-center justify-center shadow-2xl">
                                    <svg class="w-12 h-12 sm:w-14 sm:h-14 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="{{ $join['icon'] }}"/>
                                    </svg>
                                </div>

                                @foreach($join['chips'] as $i => $chip)
                                    <span class="absolute {{ ['top-6 left-6', 'top-10 right-6', 'bottom-8 left-1/2 -translate-x-1/2'][$i] }} inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white shadow-lg text-[11px] sm:text-xs font-extrabold text-slate-800 whitespace-nowrap">
                                        <span class="w-1.5 h-1.5 rounded-full {{ ['bg-emerald-500', 'bg-amber-500', 'bg-primary-500'][$i] }}"></span>
                                        {{ $chip }}
                                    </span>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- Content -->
                    <div class="w-full md:w-1/2 space-y-5">
                        <div class="space-y-2">
                            <span class="text-sm font-extrabold text-primary-600 uppercase tracking-widest">{{ __('messages.home_' . $join['key'] . '_tag') }}</span>
                            <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">{{ __('messages.home_' . $join['key'] . '_title') }}</h2>
                            <p class="text-sm sm:text-base text-slate-600 font-medium leading-relaxed">{{ __('messages.home_' . $join['key'] . '_desc') }}</p>
                        </div>

                        <ul class="space-y-2.5">
                            @foreach(range(1, 4) as $n)
                                <li class="flex items-start gap-3">
                                    <span class="mt-0.5 w-5 h-5 rounded-full bg-emerald-50 border border-emerald-200 flex items-center justify-center shrink-0">
                                        <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    </span>
                                    <span class="text-sm font-semibold text-slate-700">{{ __('messages.home_' . $join['key'] . '_point_' . $n) }}</span>
                                </li>
                            @endforeach
                        </ul>

                        <div class="flex flex-col sm:flex-row gap-3 pt-1">
                            <a href="{{ $join['primary']['url'] }}"
                               class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-primary-600 to-primary-500 hover:from-primary-500 hover:to-primary-400 text-white font-extrabold text-xs uppercase tracking-wider rounded-xl shadow-md shadow-primary-500/25 transition-all duration-200 hover:-translate-y-0.5 whitespace-nowrap">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                <span>{{ $join['primary']['label'] }}</span>
                            </a>
                            @if($join['secondary'])
                                <a href="{{ $join['secondary']['url'] }}"
                                   class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs uppercase tracking-wider rounded-xl transition-all duration-200 hover:-translate-y-0.5 whitespace-nowrap">
                                    <span>{{ $join['secondary']['label'] }}</span>
                                    <span class="text-amber-400">&rarr;</span>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endforeach

    <!-- Upcoming Events Section -->
    <section class="py-6 sm:py-8 bg-transparent">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Clean Section Header -->
            <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-8 gap-4">
                <div class="space-y-1.5 text-center sm:text-left">
                    <span
                        class="text-sm sm:text-base font-extrabold text-primary-600 uppercase tracking-widest">{{ __('messages.upcoming_events') }}</span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                        {{ __('messages.gatherings_activities') }}
                    </h2>
                </div>
                <a href="{{ route('events') }}"
                    class="hidden sm:inline-flex items-center gap-2 text-xs font-extrabold text-primary-600 hover:text-primary-700 transition-colors">
                    <span>{{ __('messages.view_all_events') }}</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                        </path>
                    </svg>
                </a>
            </div>

            <!-- Event Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse(($events ?? $upcomingEvents) as $event)
                    <a href="{{ route('event.details', $event->id) }}"
                        class="group flex flex-col bg-white rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-md hover:border-slate-300 transition-all duration-300 overflow-hidden cursor-pointer">
                        <!-- Top Banner / Fallback Container -->
                        <div class="relative h-48 w-full overflow-hidden shrink-0 bg-white border-b border-slate-100">
                            {{-- Always-visible Red Background + Calendar Icon (base layer) --}}
                            <div style="position:absolute; inset:0; display:flex; flex-direction:column; align-items:center; justify-content:center; padding: 16px 12px; background: linear-gradient(135deg, #dc2626 0%, #e11d48 60%, #be123c 100%);">
                                {{-- Dot grid texture --}}
                                <div style="position:absolute; inset:0; background-image: radial-gradient(circle, rgba(255,255,255,0.12) 1px, transparent 1px); background-size: 14px 14px; pointer-events:none;"></div>

                                {{-- Calendar card --}}
                                <div style="position:relative; display:flex; flex-direction:column; align-items:center; gap:8px;">
                                    <div style="width:68px; height:72px; border-radius:14px; background:#fff; overflow:hidden; display:flex; flex-direction:column; box-shadow: 0 6px 20px rgba(0,0,0,0.28), 0 0 0 2px rgba(255,255,255,0.35);">
                                        <div style="background: linear-gradient(90deg, #dc2626, #e11d48); padding: 5px 0; text-align:center;">
                                            <span style="font-size:12px; font-weight:900; color:#fff; letter-spacing:0.15em; text-transform:uppercase; display:block; line-height:1;">
                                                {{ date('M', strtotime($event->date)) }}
                                            </span>
                                        </div>
                                        <div style="flex:1; display:flex; flex-direction:column; align-items:center; justify-content:center; padding: 4px 0;">
                                            <span style="font-size:28px; font-weight:900; color:#1e293b; line-height:1;">
                                                {{ date('d', strtotime($event->date)) }}
                                            </span>
                                            <span style="font-size:9px; font-weight:700; color:#94a3b8; margin-top:1px; line-height:1;">
                                                {{ date('Y', strtotime($event->date)) }}
                                            </span>
                                        </div>
                                    </div>
                                    <span style="font-size:10px; font-weight:800; text-transform:uppercase; letter-spacing:0.12em; color:#fff; background:rgba(0,0,0,0.35); border:1px solid rgba(255,255,255,0.15); padding:3px 12px; border-radius:999px;">{{ __('messages.community_event') }}</span>
                                </div>
                            </div>

                            {{-- Actual image on top (covers calendar when loaded successfully) --}}
                            @if(!empty($event->banner_path))
                                <img class="absolute inset-0 w-full h-full object-contain bg-white group-hover:scale-105 transition-transform duration-500"
                                    src="{{ str_starts_with($event->banner_path, 'http') ? $event->banner_path : asset('storage/' . $event->banner_path) }}"
                                    alt="{{ $event->title }}" onerror="this.style.display='none'">
                            @endif

                            {{-- Date Badge (Top-Left overlay) --}}
                            <div class="absolute top-3.5 left-3.5 z-10">
                                <span class="text-xs font-black text-white px-3 py-1.5 rounded-xl uppercase tracking-wider shadow-lg flex items-center gap-1.5"
                                    style="background-color: #0f172a !important; color: #ffffff !important; box-shadow: 0 4px 12px rgba(0,0,0,0.35); border: 1px solid rgba(255,255,255,0.25);">
                                    <svg class="w-3.5 h-3.5 text-slate-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <span>{{ date('d M, Y', strtotime($event->date)) }}</span>
                                </span>
                            </div>

                            {{-- Status Badge (Top-Right overlay) --}}
                            <div class="absolute top-3.5 right-3.5 z-10">
                                @if($event->date < now()->toDateString())
                                    <span
                                        class="text-xs font-black text-white bg-slate-600 px-3 py-1 rounded-xl uppercase tracking-wider shadow-md">{{ __('messages.passed') }}</span>
                                @else
                                    <span
                                        class="text-xs font-black text-white bg-emerald-600 px-3 py-1 rounded-xl uppercase tracking-wider shadow-md">{{ __('messages.upcoming') }}</span>
                                @endif
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="p-5 flex-1 flex flex-col justify-between space-y-3">
                            <div class="space-y-1.5">
                                <h3 class="text-sm font-bold text-slate-900 group-hover:text-primary-600 transition-colors line-clamp-1"
                                    title="{{ $event->title }}">
                                    {{ $event->title }}
                                </h3>
                                @php
                                    $cleanDesc = preg_replace('/<span class="ql-ui"[^>]*>.*?<\/span>/i', '', $event->description);
                                    $cleanDesc = strip_tags(str_replace(['<br>', '</p>', '</li>', '</div>'], ' ', $cleanDesc));
                                    $cleanDesc = trim(preg_replace('/\s+/', ' ', $cleanDesc));
                                @endphp
                                <p class="text-[11px] text-slate-500 line-clamp-2 leading-relaxed break-words">
                                    {{ \Illuminate\Support\Str::limit($cleanDesc, 85, '...') }}
                                </p>
                            </div>

                            <div
                                class="pt-3 border-t border-slate-100 flex items-center justify-between gap-2 min-w-0 flex-wrap">
                                <div class="min-w-0 flex-1">
                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-slate-400 truncate max-w-full"
                                        title="{{ $event->venue }}">
                                        <svg class="w-3 h-3 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        <span class="truncate">{{ $event->venue }}</span>
                                    </span>
                                </div>
                                @if(!empty($event->registration_end_date) && ($event->event_type ?? 'normal') !== 'normal')
                                    <span
                                        class="inline-flex items-center gap-1 text-[9px] font-extrabold text-rose-600 bg-rose-50 px-1.5 py-0.5 rounded border border-rose-100 whitespace-nowrap">
                                        <svg class="w-2.5 h-2.5 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span>{{ __('messages.last_date') }}: {{ date('d-M-Y', strtotime($event->registration_end_date)) }}</span>
                                    </span>
                                @endif
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="col-span-3 text-center py-10 bg-white rounded-2xl border border-slate-200/80 text-slate-500">
                        <p class="text-xs font-bold">{{ __('messages.no_events_scheduled') }}</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- Latest Updates / Community Bulletins Section -->
    <section class="py-6 sm:py-8 bg-transparent">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Clean Section Header -->
            <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-8 gap-4">
                <div class="space-y-1.5 text-center sm:text-left">
                    <span
                        class="text-sm sm:text-base font-extrabold text-primary-600 uppercase tracking-widest">{{ __('messages.latest_updates') }}</span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                        {{ __('messages.community_bulletins') }}
                    </h2>
                </div>
                <a href="{{ route('updates') }}"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-700 !text-white text-white font-bold text-xs sm:text-sm transition-all duration-200 shadow-sm shrink-0 group">
                    <span class="!text-white text-white">{{ __('messages.all_announcements') }}</span>
                    <svg class="w-4 h-4 !text-white text-white group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                            d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </a>
            </div>

            <!-- Updates Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @forelse($latestUpdates as $update)
                    <div
                        class="group bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs hover:border-slate-300 hover:shadow-md transition-all duration-300 flex flex-col justify-between space-y-3">

                        <!-- Top Meta -->
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-[9px] font-extrabold text-primary-700 bg-primary-50 px-2 py-0.5 rounded uppercase tracking-wider">
                                {{ date('M d, Y', strtotime($update->publish_date)) }}
                            </span>
                        </div>

                        <!-- Content -->
                        <div class="space-y-1.5 flex-1">
                            <h3 class="text-sm font-bold text-slate-900 group-hover:text-primary-600 transition-colors line-clamp-1" title="{{ $update->title }}">
                                {{ $update->title }}
                            </h3>
                            @php
                                $cleanDesc = preg_replace('/<span class="ql-ui"[^>]*>.*?<\/span>/i', '', $update->description);
                                $cleanDesc = strip_tags(str_replace(['<br>', '</p>', '</li>', '</div>'], ' ', $cleanDesc));
                                $cleanDesc = trim(preg_replace('/\s+/', ' ', $cleanDesc));
                            @endphp
                            <p class="text-[11px] text-slate-500 line-clamp-2 leading-relaxed break-words font-medium">
                                {{ \Illuminate\Support\Str::limit($cleanDesc, 100, '...') }}
                            </p>
                        </div>

                        <!-- Footer Link -->
                        <div class="pt-2 border-t border-slate-100 flex justify-end">
                            <a href="{{ route('update.details', $update->id) }}"
                                class="text-[10px] font-bold text-primary-600 hover:text-primary-700 inline-flex items-center gap-1">
                                <span>{{ __('messages.read_full_post') }}</span> &rarr;
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="col-span-3 text-center py-10 bg-white rounded-2xl border border-slate-200/80 text-slate-500">
                        <p class="text-xs font-bold">{{ __('messages.no_updates_found') }}</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>



    <!-- Gallery Preview Section -->
    <section class="py-6 sm:py-8 bg-transparent" x-data="{ 
            lightbox: false,
            lightboxIndex: 0,
            currentGallery: {{ json_encode(collect($galleryPreview)->map(function ($item) {
        return ['src' => $item->url, 'caption' => $item->caption ?? '', 'isVideo' => $item->isVideo()]; })->values()) }},
            openLightbox(index) {
                this.lightboxIndex = index;
                this.lightbox = true;
            },
            nextImage() {
                if (this.currentGallery.length > 1) {
                    this.lightboxIndex = (this.lightboxIndex + 1) % this.currentGallery.length;
                }
            },
            prevImage() {
                if (this.currentGallery.length > 1) {
                    this.lightboxIndex = (this.lightboxIndex - 1 + this.currentGallery.length) % this.currentGallery.length;
                }
            }
        }" @keydown.window.escape="lightbox = false" @keydown.window.right="if(lightbox) nextImage()"
        @keydown.window.left="if(lightbox) prevImage()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Clean Section Header -->
            <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-8 gap-4">
                <div class="space-y-1.5 text-center sm:text-left">
                    <span
                        class="text-sm sm:text-base font-extrabold text-primary-600 uppercase tracking-widest">{{ __('messages.gallery_preview') }}</span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                        {{ __('messages.moments_togetherness') }}
                    </h2>
                </div>
                <a href="{{ route('gallery') }}"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-700 !text-white text-white font-bold text-xs sm:text-sm transition-all duration-200 shadow-sm shrink-0 group">
                    <span class="!text-white text-white">{{ __('messages.view_full_gallery') }}</span>
                    <svg class="w-4 h-4 !text-white text-white group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                            d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </a>
            </div>

            @if($galleryPreview->count() > 0)
                <!-- Staggered Masonry Collage (Random Pinterest layout, zero empty gaps) -->
                <div class="columns-1 sm:columns-2 lg:columns-3 gap-6 space-y-6">
                    @foreach($galleryPreview as $index => $item)
                        @php
                            $shapes = [
                                0 => 'h-72 sm:h-80 rounded-[2.2rem]',
                                1 => 'h-52 sm:h-60 rounded-3xl',
                                2 => 'h-96 sm:h-[420px] rounded-[2.5rem]',
                                3 => 'h-64 sm:h-72 rounded-3xl',
                                4 => 'h-80 sm:h-88 rounded-[2rem]',
                                5 => 'h-60 sm:h-64 rounded-3xl',
                            ];
                            $shapeClass = $shapes[$index % count($shapes)];
                            $imageUrl = $item->url;
                            $isVideo = $item->isVideo();
                        @endphp
                        <div @click="openLightbox({{ $index }})"
                            class="break-inside-avoid group relative w-full overflow-hidden bg-slate-950 border border-slate-200/80 shadow-md hover:shadow-xl transition-all duration-500 cursor-pointer flex items-center justify-center {{ $shapeClass }}">

                            @if($isVideo)
                                <video class="w-full h-full object-cover pointer-events-none" preload="metadata" muted playsinline>
                                    <source src="{{ $imageUrl }}">
                                </video>
                                <div class="absolute inset-0 flex items-center justify-center pointer-events-none" style="z-index: 2;">
                                    <div class="w-12 h-12 rounded-full bg-slate-900/85 border border-white/30 text-white flex items-center justify-center shadow-xl backdrop-blur-xs group-hover:scale-110 transition-transform">
                                        <svg class="w-6 h-6 fill-current ml-0.5 text-primary-400" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                    </div>
                                </div>
                                <span class="absolute top-3 left-3 z-10 px-2.5 py-0.5 rounded-lg bg-slate-900/85 text-white text-[10px] font-black uppercase tracking-wider backdrop-blur-xs border border-white/20 flex items-center gap-1">
                                    <svg class="w-2.5 h-2.5 fill-current text-primary-400" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                    Video
                                </span>
                            @else
                                <!-- Blurred Background Image (same image, scaled + blurred) -->
                                <img src="{{ $imageUrl }}"
                                    alt=""
                                    aria-hidden="true"
                                    class="absolute inset-0 w-full h-full pointer-events-none select-none"
                                    style="object-fit: cover; object-position: center; filter: blur(16px) brightness(0.45); transform: scale(1.12); z-index: 0;">

                                <!-- Main Image (Full object-contain, never cropped) -->
                                <img class="relative w-full h-full object-contain group-hover:scale-105 transition-transform duration-700"
                                    style="z-index: 1;"
                                    src="{{ $imageUrl }}" alt="{{ $item->caption ?: 'Community Gallery' }}">
                            @endif

                            <!-- Gradient Overlay & High-Contrast Caption -->
                            <div style="z-index: 10;"
                                class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/20 to-transparent flex items-end p-5 transition-opacity duration-300">
                                <div class="w-full space-y-2">
                                    <p class="text-sm sm:text-base font-extrabold text-white leading-snug drop-shadow-md truncate">
                                        {{ $item->caption ?: 'Shree Satwara Gnati Mandal, Ahmedabad' }}
                                    </p>

                                    <!-- High-Visibility Badge -->
                                    <div
                                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-slate-900/85 group-hover:bg-primary-600 backdrop-blur-md border border-white/30 text-white text-xs font-bold shadow-md transition-colors duration-300">
                                        @if($isVideo)
                                            <svg class="w-3.5 h-3.5 text-primary-400 fill-current" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                            <span class="text-white">Play Video</span>
                                        @else
                                            <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
                                            </svg>
                                            <span class="text-white">{{ __('messages.click_to_enlarge') }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12 bg-slate-50 rounded-3xl border border-slate-200/80 text-slate-500">
                    <p class="text-sm font-bold">{{ __('messages.no_general_images') }}</p>
                </div>
            @endif

            <!-- Lightbox Modal -->
            <template x-teleport="body">
                <div x-show="lightbox" x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    style="position: fixed; inset: 0; z-index: 999999; background-color: rgba(0, 0, 0, 0.95);"
                    class="flex flex-col items-center justify-between p-4 sm:p-6 select-none" @click="lightbox = false"
                    x-cloak>

                    <!-- Close button (Top Right) -->
                    <button @click="lightbox = false"
                        style="position: absolute; top: 1.5rem; right: 1.5rem; z-index: 1000000;"
                        class="p-2.5 rounded-full bg-black/60 hover:bg-rose-600 text-white border border-white/20 hover:border-rose-500 transition-all duration-200 cursor-pointer shadow-xl hover:scale-110 active:scale-95"
                        title="Close (Esc)">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>

                    <!-- PREVIOUS ARROW BUTTON (Left Side) -->
                    <button x-show="currentGallery.length > 1" @click.stop="prevImage()"
                        style="position: absolute; left: 1.5rem; top: 50%; transform: translateY(-50%); z-index: 1000000;"
                        class="p-3 sm:p-4 rounded-full bg-black/60 hover:bg-primary-600 text-white border border-white/20 hover:border-primary-400 shadow-2xl transition-all duration-200 hover:scale-110 active:scale-95 cursor-pointer backdrop-blur-md"
                        title="Previous Image">
                        <svg class="w-6 h-6 sm:w-8 sm:h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </button>

                    <!-- NEXT ARROW BUTTON (Right Side) -->
                    <button x-show="currentGallery.length > 1" @click.stop="nextImage()"
                        style="position: absolute; right: 1.5rem; top: 50%; transform: translateY(-50%); z-index: 1000000;"
                        class="p-3 sm:p-4 rounded-full bg-black/60 hover:bg-primary-600 text-white border border-white/20 hover:border-primary-400 shadow-2xl transition-all duration-200 hover:scale-110 active:scale-95 cursor-pointer backdrop-blur-md"
                        title="Next Image">
                        <svg class="w-6 h-6 sm:w-8 sm:h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>

                    <!-- Center Content: Media -->
                    <div class="relative w-full flex-1 flex flex-col items-center justify-center my-auto max-w-4xl mx-auto px-12 sm:px-20 pb-14"
                        @click.stop>
                        <div class="relative rounded-2xl overflow-hidden shadow-2xl border border-white/10 bg-slate-900/40 flex items-center justify-center">
                            <template x-if="currentGallery[lightboxIndex]?.isVideo">
                                <video :src="currentGallery[lightboxIndex]?.src"
                                       controls
                                       autoplay
                                       playsinline
                                       class="w-auto max-w-full object-contain rounded-xl shadow-2xl"
                                       style="max-height: 68vh; outline: none;">
                                </video>
                            </template>
                            <template x-if="!currentGallery[lightboxIndex]?.isVideo">
                                <img :src="currentGallery[lightboxIndex]?.src"
                                    :alt="currentGallery[lightboxIndex]?.caption || 'Gallery Image'"
                                    class="w-auto max-w-full object-contain rounded-xl shadow-2xl" style="max-height: 68vh;">
                            </template>
                        </div>
                    </div>

                    <!-- Bottom Bar: Image Numbers Counter (Bottom Center) -->
                    <div x-show="currentGallery.length > 1"
                        style="position: absolute; bottom: 1rem; left: 50%; transform: translateX(-50%); z-index: 1000000;"
                        class="text-white/90 bg-black/80 px-5 py-1.5 rounded-full text-xs font-bold tracking-widest border border-white/20 backdrop-blur-md shadow-2xl inline-flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span><span x-text="lightboxIndex + 1"></span> / <span x-text="currentGallery.length"></span></span>
                    </div>

                </div>
            </template>
        </div>
    </section>
@endsection