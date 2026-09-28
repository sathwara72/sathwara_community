@extends('layouts.member')

@section('page_title', $event->title)

@section('content')
    <div class="space-y-4">
        <!-- Back to Events List -->
        <div>
            <a href="{{ route('member.events.index') }}"
                class="inline-flex items-center text-xs font-bold text-slate-500 hover:text-slate-900 transition-colors gap-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Events
            </a>
        </div>

        <!-- Main Content Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-2 items-start"
            x-data="{ showConfirmModal: {{ request()->has('open_form') ? 'true' : 'false' }} }">
            <!-- Left 2 Columns: Banner, Details, and Gallery -->
            <div class="lg:col-span-2 space-y-2">
                <!-- Event Hero Split Card -->
                <div
                    class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden flex flex-col md:flex-row md:items-stretch relative">
                    <!-- Status & Event Type Badges (Overlaid on card) -->
                    <div class="absolute top-3 left-6 z-20 flex items-center gap-1.5">
                        @if($event->date < now()->toDateString())
                            <span
                                class="text-[9px] font-extrabold text-slate-500 bg-white/95 backdrop-blur-sm border border-slate-200 px-2.5 py-1 rounded-full uppercase tracking-wider">Passed</span>
                        @elseif($event->status === 'cancelled')
                            <span
                                class="text-[9px] font-extrabold text-rose-600 bg-white/95 backdrop-blur-sm border border-rose-100 px-2.5 py-1 rounded-full uppercase tracking-wider">Cancelled</span>
                        @else
                            <span
                                class="text-[9px] font-extrabold text-emerald-600 bg-white/95 backdrop-blur-sm border border-emerald-100 px-2.5 py-1 rounded-full uppercase tracking-wider">Upcoming</span>
                        @endif

                        @if(($event->event_type ?? 'normal') === 'inam_vitaran')
                            <span
                                class="inline-flex items-center gap-1 text-[9px] font-extrabold text-amber-700 bg-amber-50/95 backdrop-blur-sm border border-amber-200 px-2.5 py-1 rounded-full uppercase tracking-wider">
                                <svg class="w-3 h-3 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
                                Inam Vitaran</span>
                        @elseif(($event->event_type ?? 'normal') === 'yuva_melo')
                            <span
                                class="inline-flex items-center gap-1 text-[9px] font-extrabold text-purple-700 bg-purple-50/95 backdrop-blur-sm border border-purple-200 px-2.5 py-1 rounded-full uppercase tracking-wider">
                                <svg class="w-3 h-3 text-purple-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                Yuva Melo</span>
                        @endif
                    </div>

                    <!-- Left: Banner Image -->
                    <div class="md:w-1/2 h-52 md:h-auto overflow-hidden shrink-0 relative" x-data="{ imageError: false }">
                        @if(!empty($event->banner_path))
                            <img x-show="!imageError" x-on:error="imageError = true"
                                class="w-full h-full object-cover transition-transform duration-500 hover:scale-105"
                                src="{{ str_starts_with($event->banner_path, 'http') ? $event->banner_path : asset('storage/' . $event->banner_path) }}"
                                alt="{{ $event->title }}">
                        @endif

                        <div x-show="imageError || !'{{ $event->banner_path }}'"
                            class="absolute inset-0 bg-gradient-to-br from-primary-500 via-primary-600 to-slate-900 flex flex-col items-center justify-center p-4"
                            x-cloak>
                            <svg class="w-10 h-10 text-white/70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span
                                class="text-[10px] font-extrabold uppercase tracking-widest text-primary-100 mt-2">Community
                                Event</span>
                        </div>
                    </div>

                    <!-- Right: Content & Metadata Overview -->
                    <div
                        class="md:w-1/2 p-5 flex flex-col justify-between space-y-4 bg-gradient-to-br from-white to-slate-50/30 relative z-10">
                        <div class="space-y-1.5">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                @if(($event->event_type ?? 'normal') === 'inam_vitaran')
                                    <span
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-amber-50 text-amber-700 border border-amber-200/80 text-[10px] font-extrabold uppercase">
                                        <svg class="w-3 h-3 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
                                        Inam Vitaran</span>
                                @elseif(($event->event_type ?? 'normal') === 'yuva_melo')
                                    <span
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-purple-50 text-purple-700 border border-purple-200/80 text-[10px] font-extrabold uppercase">
                                        <svg class="w-3 h-3 text-purple-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                        Yuva Melo</span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-blue-50 text-blue-700 border border-blue-200/80 text-[10px] font-extrabold uppercase">
                                        <svg class="w-3 h-3 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                                        Normal Event</span>
                                @endif

                                @if(($event->event_type ?? 'normal') === 'yuva_melo' && ($event->form_fee ?? 0) > 0)
                                    <span
                                        class="px-2 py-0.5 rounded bg-purple-50 text-purple-700 border border-purple-200/80 text-[10px] font-black uppercase">₹{{ number_format($event->form_fee, 0) }}
                                        Form Fee</span>
                                @endif

                                @if(($event->pass_fee ?? 0) > 0)
                                    <span
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200/80 text-[10px] font-black uppercase">
                                        <svg class="w-3 h-3 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                                        ₹{{ number_format($event->pass_fee, 0) }} Pass Fee</span>
                                @else
                                    <span
                                        class="px-2 py-0.5 rounded bg-slate-100 text-slate-600 border border-slate-200/80 text-[10px] font-black uppercase">Free
                                        Pass</span>
                                @endif
                            </div>
                            <h1 class="text-base font-extrabold text-slate-900 leading-snug">{{ $event->title }}</h1>
                        </div>

                        <!-- Meta Details -->
                        <div class="space-y-2.5">
                            <div class="flex items-center gap-3 text-xs font-semibold text-slate-600">
                                <div class="p-1.5 bg-blue-50 text-blue-500 rounded-lg shrink-0">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                        </path>
                                    </svg>
                                </div>
                                <div>
                                    <span
                                        class="text-[9px] text-slate-400 block font-bold uppercase tracking-wider leading-none">Date</span>
                                    <span class="text-slate-800">{{ date('d-M-Y', strtotime($event->date)) }}</span>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 text-xs font-semibold text-slate-600">
                                <div class="p-1.5 bg-emerald-50 text-emerald-500 rounded-lg shrink-0">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <span
                                        class="text-[9px] text-slate-400 block font-bold uppercase tracking-wider leading-none">Time</span>
                                    <span class="text-slate-800">{{ date('h:i A', strtotime($event->time)) }}</span>
                                </div>
                            </div>

                            @if(!empty($event->registration_end_date) && ($event->event_type ?? 'normal') !== 'normal')
                                <div class="flex items-center gap-3 text-xs font-semibold text-slate-600">
                                    <div class="p-1.5 bg-rose-50 text-rose-500 rounded-lg shrink-0">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <span
                                            class="text-[9px] text-rose-500 block font-extrabold uppercase tracking-wider leading-none">{{ __('messages.form_fill_up_last_date') }}</span>
                                        <span
                                            class="text-rose-700 font-bold">{{ date('d-M-Y', strtotime($event->registration_end_date)) }}</span>
                                    </div>
                                </div>
                            @endif

                            <div class="flex items-center gap-3 text-xs font-semibold text-slate-600">
                                <div class="p-1.5 bg-amber-50 text-amber-500 rounded-lg shrink-0">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                        </path>
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <span
                                        class="text-[9px] text-slate-400 block font-bold uppercase tracking-wider leading-none">Venue</span>
                                    <span class="text-slate-800 truncate block max-w-[200px]"
                                        title="{{ $event->venue }}">{{ $event->venue }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Description Card -->
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 space-y-3">
                    <div class="flex items-center gap-2 border-b border-slate-50 pb-3">
                        <div class="p-1.5 bg-slate-50 text-slate-700 rounded-lg">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7"></path>
                            </svg>
                        </div>
                        <h2 class="text-sm font-bold text-slate-900">About the Event</h2>
                    </div>
                    <div class="rich-text text-xs text-slate-600 leading-relaxed">
                        {!! $event->description !!}
                    </div>
                </div>

                @if(!empty($event->map_embed_url))
                    <!-- Venue Google Map Card -->
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 space-y-3">
                        <div class="flex items-center justify-between border-b border-slate-50 pb-3 gap-2 flex-wrap">
                            <div class="flex items-center gap-2">
                                <div class="p-1.5 bg-amber-50 text-amber-600 rounded-lg">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                        </path>
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                </div>
                                <h2 class="text-sm font-bold text-slate-900">Event Location & Venue Map</h2>
                            </div>
                            @if(!empty($event->google_map_link) && str_starts_with($event->google_map_link, 'http'))
                                <a href="{{ $event->google_map_link }}" target="_blank"
                                    class="text-xs font-bold text-primary-600 hover:text-primary-700 flex items-center gap-1">
                                    <span>Open in Google Maps</span> &rarr;
                                </a>
                            @endif
                        </div>
                        <div
                            class="rounded-xl overflow-hidden border border-slate-200 shadow-2xs h-52 sm:h-60 w-full bg-slate-50">
                            <iframe src="{{ $event->map_embed_url }}" class="w-full h-full border-0" allowfullscreen=""
                                loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                        </div>
                    </div>
                @endif
                <!-- Event Gallery -->
                @if($gallery->count() > 0)
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 space-y-4">
                        <div class="flex items-center gap-2 border-b border-slate-50 pb-3">
                            <div class="p-1.5 bg-slate-50 text-slate-700 rounded-lg">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                    </path>
                                </svg>
                            </div>
                            <h2 class="text-sm font-bold text-slate-900">Event Gallery</h2>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3" x-data="{ 
                                     lightbox: false, 
                                     lightboxIndex: 0,
                                     galleryImages: [
                                         @foreach($gallery as $photo)
                                             {
                                                 src: '{{ $photo->url }}',
                                                 caption: '{{ addslashes($photo->caption ?? $event->title) }}',
                                                 isVideo: {{ $photo->isVideo() ? 'true' : 'false' }}
                                             },
                                         @endforeach
                                     ],
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
                                 }" @keydown.window.escape="lightbox = false" @keydown.window.right="if(lightbox) nextImage()"
                            @keydown.window.left="if(lightbox) prevImage()">

                            @foreach($gallery as $index => $photo)
                                @php $mImgUrl = $photo->url; $isMVideo = $photo->isVideo(); @endphp
                                <div class="aspect-video rounded-xl overflow-hidden bg-slate-950 border border-slate-100 group relative cursor-pointer shadow-sm flex items-center justify-center"
                                    @click="lightboxIndex = {{ $index }}; lightbox = true">
                                    @if($isMVideo)
                                        <video class="w-full h-full object-cover pointer-events-none" preload="metadata" muted playsinline>
                                            <source src="{{ $mImgUrl }}">
                                        </video>
                                        <div class="absolute inset-0 flex items-center justify-center pointer-events-none" style="z-index: 2;">
                                            <div class="w-10 h-10 rounded-full bg-slate-900/85 border border-white/30 text-white flex items-center justify-center shadow-md backdrop-blur-xs group-hover:scale-110 transition-transform">
                                                <svg class="w-5 h-5 fill-current ml-0.5 text-primary-400" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                            </div>
                                        </div>
                                    @else
                                        <img class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                                            src="{{ $mImgUrl }}"
                                            alt="{{ $photo->caption }}">
                                    @endif
                                    <div
                                        class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center" style="z-index: 10;">
                                        @if($isMVideo)
                                            <span class="text-white text-xs font-bold inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-900/80 border border-white/20 backdrop-blur-xs shadow-md">
                                                <svg class="w-3.5 h-3.5 text-primary-400 fill-current" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                                <span>Play</span>
                                            </span>
                                        @else
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2.5"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7"></path>
                                            </svg>
                                        @endif
                                    </div>
                                </div>
                            @endforeach

                            <!-- Lightbox Modal with Navigation Arrows -->
                            <template x-teleport="body">
                                <div x-show="lightbox" x-transition:enter="transition ease-out duration-300"
                                    x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                                    x-transition:leave="transition ease-in duration-200"
                                    x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                                    style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; z-index: 9999999; background-color: rgba(2, 6, 23, 0.96); backdrop-filter: blur(20px); overflow: hidden;"
                                    class="flex flex-col items-center justify-between p-4 sm:p-6 select-none relative"
                                    @click="lightbox = false" x-cloak>

                                    <!-- PREVIOUS ARROW BUTTON (<) -->
                                    <button x-show="galleryImages.length > 1" @click.stop="prevImage()"
                                        style="position: absolute; left: 1.5rem; top: 50%; transform: translateY(-50%); z-index: 10000000;"
                                        class="left-4 sm:left-8 group p-2.5 sm:p-3 rounded-full bg-slate-900/80 hover:bg-primary-500 text-white border border-white/20 hover:border-primary-400 shadow-2xl transition-all duration-300 hover:scale-110 active:scale-95 cursor-pointer backdrop-blur-md"
                                        title="Previous Image (Left Arrow)">
                                        <svg class="w-5 h-5 sm:w-6 sm:h-6 transition-transform duration-300 group-hover:-translate-x-0.5"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path>
                                        </svg>
                                    </button>

                                    <!-- NEXT ARROW BUTTON (>) -->
                                    <button x-show="galleryImages.length > 1" @click.stop="nextImage()"
                                        style="position: absolute; right: 1.5rem; top: 50%; transform: translateY(-50%); z-index: 10000000;"
                                        class="right-4 sm:right-8 group p-2.5 sm:p-3 rounded-full bg-slate-900/80 hover:bg-primary-500 text-white border border-white/20 hover:border-primary-400 shadow-2xl transition-all duration-300 hover:scale-110 active:scale-95 cursor-pointer backdrop-blur-md"
                                        title="Next Image (Right Arrow)">
                                        <svg class="w-5 h-5 sm:w-6 sm:h-6 transition-transform duration-300 group-hover:translate-x-0.5"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </button>

                                    <!-- Top Bar: Close Button -->
                                    <div class="w-full flex items-center justify-end z-[10000000] max-w-7xl mx-auto pt-1 px-2"
                                        @click.stop>
                                        <button @click="lightbox = false"
                                            class="group p-2 rounded-full bg-slate-900/80 hover:bg-rose-500 text-white border border-white/20 hover:border-rose-400 transition-all duration-300 cursor-pointer shadow-xl hover:rotate-90 hover:scale-110 active:scale-95"
                                            title="Close (Esc)">
                                            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12">
                                                </path>
                                            </svg>
                                        </button>
                                    </div>

                                    <!-- Main Section: Centered Media -->
                                    <div class="relative w-full flex-1 flex items-center justify-center my-auto max-w-5xl mx-auto px-4 pb-14"
                                        @click.stop>
                                        <div class="relative flex flex-col items-center justify-center max-w-full max-h-full">
                                            <div
                                                class="relative rounded-2xl overflow-hidden shadow-2xl border border-white/20 bg-slate-900/50 flex items-center justify-center">
                                                <template x-if="galleryImages[lightboxIndex]?.isVideo">
                                                    <video :src="galleryImages[lightboxIndex]?.src"
                                                           controls
                                                           autoplay
                                                           playsinline
                                                           class="w-auto max-w-full object-contain rounded-xl shadow-2xl transition-all duration-300"
                                                           style="max-height: 68vh; max-width: 80vw; outline: none;">
                                                    </video>
                                                </template>
                                                <template x-if="!galleryImages[lightboxIndex]?.isVideo">
                                                    <img :src="galleryImages[lightboxIndex]?.src"
                                                        :alt="galleryImages[lightboxIndex]?.caption || 'Gallery Image'"
                                                        class="w-auto max-w-full object-contain rounded-xl shadow-2xl transition-all duration-300"
                                                        style="max-height: 68vh; max-width: 80vw;">
                                                </template>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Bottom Bar: Image Numbers Counter (Bottom Center) -->
                                    <div x-show="galleryImages.length > 0"
                                        style="position: absolute; bottom: 1rem; left: 50%; transform: translateX(-50%); z-index: 10000000;"
                                        class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full bg-slate-900/90 border border-white/20 text-white text-xs font-bold shadow-2xl backdrop-blur-md">
                                        <svg class="w-3.5 h-3.5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <span>
                                            <span class="text-white" x-text="lightboxIndex + 1"></span>
                                            <span class="text-white/40"> / </span>
                                            <span class="text-white/70" x-text="galleryImages.length"></span>
                                        </span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Right 1 Column: Ticket-Style Registration Widget -->
            <div class="lg:sticky lg:top-4">
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden relative pb-4">
                    <!-- Ticket Top Header -->
                    <div class="p-4 space-y-3">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                            <span class="text-xs font-extrabold text-slate-800 uppercase tracking-wider">Pass Status</span>
                            @if($registration)
                                <span
                                    class="text-[9px] font-extrabold text-emerald-600 bg-emerald-50 border border-emerald-100 px-2 py-0.5 rounded-full uppercase tracking-wider">Registered</span>
                            @else
                                <span
                                    class="text-[9px] font-extrabold text-slate-500 bg-slate-50 border border-slate-200 px-2 py-0.5 rounded-full uppercase tracking-wider">Available</span>
                            @endif
                        </div>

                        <!-- Ticket Event Mini Info -->
                        <div class="space-y-2">
                            <div class="space-y-0.5">
                                <span class="text-[9px] text-slate-400 block font-bold uppercase tracking-wider">Event
                                    Title</span>
                                <span class="text-xs font-bold text-slate-900 block truncate">{{ $event->title }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Dashed Separation Line & Cut-outs -->
                    <div class="relative my-2.5">
                        <div
                            class="absolute -left-3.5 -top-3 w-7 h-7 rounded-full bg-slate-50 border-r border-slate-100/80 z-10">
                        </div>
                        <div
                            class="absolute -right-3.5 -top-3 w-7 h-7 rounded-full bg-slate-50 border-l border-slate-100/80 z-10">
                        </div>
                        <div class="border-t-2 border-dashed border-slate-100/90 w-full"></div>
                    </div>

                    <!-- Ticket Bottom Section -->
                    <div class="px-4 pt-1 space-y-3">
                        <div class="space-y-3">
                            @if($event->date < now()->toDateString())
                                <!-- Concluded -->
                                <div
                                    class="p-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-500 text-xs font-bold text-center">
                                    This event has concluded.
                                </div>
                            @elseif($event->status === 'cancelled')
                                <!-- Cancelled -->
                                <div
                                    class="p-3 rounded-xl bg-rose-50 border border-rose-100/50 text-rose-600 text-xs font-bold text-center">
                                    This event has been cancelled.
                                </div>
                            @else
                                <!-- Active Flow -->
                                @if(($event->event_type ?? 'normal') === 'normal')
                                    @php
                                        $isFeeRequired = (float)($event->pass_fee ?? 0) > 0;
                                        $isPassPaid = $registration && (!$isFeeRequired || ($registration->payment_status === 'paid'));
                                    @endphp

                                    @if($isPassPaid)
                                        <div class="space-y-3">
                                            <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-800 text-xs font-bold text-center flex flex-col items-center justify-center gap-1">
                                                <span class="flex items-center gap-1">
                                                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                    {{ __('messages.registered_status') }}
                                                </span>
                                                <span class="text-[10px] text-emerald-700 font-semibold">
                                                    {{ __('messages.attending_persons_count', ['count' => $registration->form_data['person_count'] ?? 1]) }}
                                                </span>
                                            </div>

                                            <a href="{{ route('event.details', $event->id) }}" class="w-full flex items-center justify-center px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl shadow-sm transition-all text-center cursor-pointer gap-1">
                                                <span>{{ __('messages.view_my_passes', ['count' => $registration->form_data['person_count'] ?? 1]) }}</span> &rarr;
                                            </a>
                                        </div>
                                    @elseif($registration && !$isPassPaid)
                                        <div class="space-y-3">
                                            <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs font-bold text-center flex flex-col items-center justify-center gap-1">
                                                <span class="flex items-center gap-1">
                                                    <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                                    {{ $isGu ? 'ચૂકવણી બાકી છે' : 'Payment Pending' }}
                                                </span>
                                                <span class="text-[10px] text-amber-700 font-medium">
                                                    {{ $isGu ? 'પાસ મેળવવા માટે કૃપા કરીને ઓનલાઇન ચૂકવણી પૂર્ણ કરો.' : 'Please complete payment to receive your entry passes.' }}
                                                </span>
                                            </div>

                                            <a href="{{ route('event.details', $event->id) }}" class="w-full flex items-center justify-center px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold rounded-xl shadow-sm transition-all text-center cursor-pointer gap-1">
                                                <span>{{ $isGu ? 'હમણાં ચૂકવણી કરો' : 'Pay Now' }}</span> &rarr;
                                            </a>
                                        </div>
                                    @else
                                        <!-- Check Capacity -->
                                        @php
                                            $currentCount = $event->registrations()->whereIn('status', ['pending', 'approved'])->count();
                                            $isFull = $event->max_participants && ($currentCount >= $event->max_participants);
                                        @endphp

                                        @if($isFull)
                                            <div class="p-3 rounded-xl bg-rose-50 border border-rose-100 text-rose-700 text-xs font-bold text-center flex items-center justify-center gap-1">
                                                <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                <span>Capacity Full</span>
                                            </div>
                                        @elseif($isFeeRequired)
                                            <div class="space-y-3">
                                                <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl text-center">
                                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">{{ $isGu ? 'પાસ દીઠ ફી' : 'Fee per pass' }}</span>
                                                    <span class="text-lg font-black text-primary-600">₹{{ number_format($event->pass_fee, 0) }}</span>
                                                </div>
                                                <a href="{{ route('event.details', $event->id) }}" class="w-full flex items-center justify-center px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-xs font-extrabold rounded-xl shadow-sm transition-all text-center cursor-pointer gap-1.5">
                                                    <svg class="w-4 h-4 text-primary-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                                                    <span>{{ __('messages.purchase_pass') }}</span> &rarr;
                                                </a>
                                            </div>
                                        @else
                                            <form method="POST" action="{{ route('member.events.register', $event->id) }}" class="space-y-3">
                                                @csrf
                                                <div x-data="{ count: 1 }" class="space-y-1.5">
                                                    <label class="text-[11px] font-bold text-slate-700 flex items-center justify-between">
                                                        <span>{{ __('messages.ketla_person_attending') }}</span>
                                                    </label>
                                                    <div class="flex items-center justify-between bg-slate-100/90 p-1.5 rounded-xl border border-slate-200/80">
                                                        <button type="button" 
                                                                @click="if (count > 1) count--" 
                                                                :disabled="count <= 1"
                                                                class="w-8 h-8 rounded-lg bg-white hover:bg-slate-200 active:scale-95 disabled:opacity-40 disabled:cursor-not-allowed border border-slate-200 text-slate-800 font-black text-lg flex items-center justify-center transition-all cursor-pointer shadow-xs">
                                                            &minus;
                                                        </button>

                                                        <div class="flex items-center gap-1 px-2">
                                                            <span class="text-base font-black text-primary-600" x-text="count"></span>
                                                            <span class="text-[11px] font-bold text-slate-700 uppercase" x-text="count > 1 ? '{{ __('messages.persons') }}' : '{{ __('messages.person') }}'"></span>
                                                        </div>

                                                        <button type="button" 
                                                                @click="count++" 
                                                                class="w-8 h-8 rounded-lg bg-primary-500 hover:bg-primary-600 active:scale-95 text-white font-black text-lg flex items-center justify-center transition-all cursor-pointer shadow-xs">
                                                            &#43;
                                                        </button>
                                                    </div>
                                                    <input type="hidden" name="person_count" :value="count">
                                                </div>

                                                <button type="submit" class="w-full flex items-center justify-center px-4 py-2.5 bg-primary-500 hover:bg-primary-600 text-white text-xs font-extrabold rounded-xl shadow-sm transition-all text-center cursor-pointer gap-1">
                                                    <span>{{ __('messages.direct_register_now') }}</span> &rarr;
                                                </button>
                                            </form>
                                        @endif
                                    @endif
                                @else
                                    @if(($event->event_type ?? 'normal') === 'yuva_melo')
                                        <div class="space-y-3">
                                            <!-- Fill Yuva Form Button -->
                                            <a href="{{ route('member.events.register_form', $event->id) }}"
                                                class="w-full flex items-center justify-center px-4 py-3 bg-primary-600 hover:bg-primary-700 active:scale-95 text-white text-xs font-black rounded-xl shadow-xs transition-all text-center gap-1.5">
                                                <svg class="w-4 h-4 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                                <span>{{ __('messages.fill_yuva_form') ?? 'Fill Yuva Melo Form' }}</span>
                                                <span>&rarr;</span>
                                            </a>

                                            @if(($event->pass_fee ?? 0) > 0)
                                                <div class="pt-2.5 border-t border-slate-100 space-y-2">
                                                    <div class="flex items-center justify-between text-[11px]">
                                                        <span class="text-slate-500 font-bold inline-flex items-center gap-1">
                                                            <svg class="w-3.5 h-3.5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                                                            Attendee Pass:
                                                        </span>
                                                        <span class="text-slate-900 font-black">₹{{ number_format($event->pass_fee, 0) }}</span>
                                                    </div>
                                                    <a href="{{ route('event.details', $event->id) }}" class="w-full py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl transition-all text-center cursor-pointer inline-flex items-center justify-center gap-1.5">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                                                        <span>Book Attendee Pass &rarr;</span>
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                    @elseif($registration)
                                        <div
                                            class="p-3 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-800 text-xs font-bold text-center flex flex-col items-center justify-center gap-1">
                                            <span class="flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                Registered
                                            </span>
                                        </div>
                                        @if(($event->event_type ?? 'normal') === 'inam_vitaran')
                                            <a href="{{ route('member.events.register_form', $event->id) }}"
                                                class="w-full flex items-center justify-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold rounded-xl shadow-sm transition-all duration-150 text-center gap-1.5">
                                                <svg class="w-3.5 h-3.5 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
                                                <span>Fill Inam Form &rarr;</span>
                                            </a>
                                        @else
                                            <a href="{{ route('member.events.register_form', $event->id) }}"
                                                class="w-full flex items-center justify-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold rounded-xl shadow-sm transition-all duration-150 text-center">
                                                View Registration Details &rarr;
                                            </a>
                                        @endif
                                    @else
                                        <!-- Check Capacity -->
                                        @php
                                            $currentCount = $event->registrations()->whereIn('status', ['pending', 'approved'])->count();
                                            $isFull = $event->max_participants && ($currentCount >= $event->max_participants);
                                        @endphp

                                        @if($isFull)
                                            <div
                                                class="p-3 rounded-xl bg-rose-50 border border-rose-100 text-rose-700 text-xs font-bold text-center flex items-center justify-center gap-1">
                                                <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                <span>Capacity Full</span>
                                            </div>
                                        @else
                                            @if(($event->event_type ?? 'normal') === 'inam_vitaran')
                                                <a href="{{ route('member.events.register_form', $event->id) }}"
                                                    class="w-full flex items-center justify-center px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold rounded-xl shadow-sm transition-all duration-150 text-center gap-1.5">
                                                    <svg class="w-3.5 h-3.5 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
                                                    <span>Fill Inam Form &rarr;</span>
                                                </a>
                                            @else
                                                <a href="{{ route('member.events.register_form', $event->id) }}"
                                                    class="w-full flex items-center justify-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold rounded-xl shadow-sm transition-all duration-150 text-center">
                                                    Register for Event &rarr;
                                                </a>
                                            @endif
                                        @endif
                                    @endif
                                @endif
                            @endif
                        </div>

                        <!-- Ticket Barcode Footer decoration -->
                        <div class="flex flex-col items-center justify-center pt-1.5 space-y-1 opacity-60">
                            <div class="h-3 w-full flex items-center justify-center gap-[2px]">
                                <div class="h-full w-[2px] bg-slate-400"></div>
                                <div class="h-full w-[1px] bg-slate-400"></div>
                                <div class="h-full w-[3px] bg-slate-400"></div>
                                <div class="h-full w-[1px] bg-slate-400"></div>
                                <div class="h-full w-[2px] bg-slate-400"></div>
                                <div class="h-full w-[4px] bg-slate-400"></div>
                                <div class="h-full w-[1px] bg-slate-400"></div>
                                <div class="h-full w-[2px] bg-slate-400"></div>
                                <div class="h-full w-[3px] bg-slate-400"></div>
                                <div class="h-full w-[1px] bg-slate-400"></div>
                            </div>
                            <!-- <span class="text-[8px] tracking-[0.2em] font-mono text-slate-500 uppercase leading-none">EVT-{{ str_pad($event->id, 5, '0', STR_PAD_LEFT) }}</span> -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@if(($event->pass_fee ?? 0) > 0)
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('memberEventRegisterForm');
    if (!form) return;

    form.addEventListener('submit', function (e) {
        const paymentIdInput = document.getElementById('member_event_razorpay_payment_id');
        if (paymentIdInput && paymentIdInput.value) {
            return true; // Already paid
        }

        e.preventDefault();

        const passFee = {{ (float)($event->pass_fee ?? 0) }};
        const personCountInput = form.querySelector('[name="person_count"]');
        const personCount = personCountInput ? parseInt(personCountInput.value) || 1 : 1;
        const totalAmountPaise = Math.round(passFee * personCount * 100);

        const razorpayKey = "{{ \App\Models\Setting::get('razorpay_key_id', env('RAZORPAY_KEY_ID', '')) }}";
        const userEmail = "{{ auth()->user() ? auth()->user()->email : '' }}";
        const userName = "{{ auth()->user() ? auth()->user()->name : '' }}";
        const userPhone = "{{ (auth()->user() && auth()->user()->memberProfile) ? auth()->user()->memberProfile->phone : '' }}";

        const options = {
            "key": razorpayKey || "rzp_test_key",
            "amount": totalAmountPaise,
            "currency": "INR",
            "name": "{{ config('app.name', 'Shree Satwara Gnati Mandal, Ahmedabad') }}",
            "description": "Event Pass Booking - {{ addslashes($event->title) }} (" + personCount + " Person/s)",
            "handler": function (response) {
                paymentIdInput.value = response.razorpay_payment_id;
                window.dispatchEvent(new CustomEvent('close-all-modals'));
                window.dispatchEvent(new CustomEvent('show-loader'));
                form.submit();
            },
            "prefill": {
                "name": userName,
                "email": userEmail,
                "contact": userPhone
            },
            "modal": {
                "ondismiss": function() {
                    // User cancelled modal, do not submit
                }
            },
            "theme": {
                "color": "#2563EB"
            }
        };

        if (window.Razorpay) {
            const rzp = new Razorpay(options);
            rzp.on('payment.failed', function (response) {
                alert(response.error?.description || 'પેમેન્ટ અસફળ રહ્યું. કૃપા કરીને ફરી પ્રયાસ કરો.');
            });
            rzp.open();
        } else {
            alert('પેમેન્ટ ગેટવે લોડ થઈ શક્યો નથી. કૃપા કરીને ફરી પ્રયાસ કરો.');
        }
    });
});
</script>
@endif
@endsection