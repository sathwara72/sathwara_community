@extends('layouts.public')

@section('content')
    @php
        $isGu = (app()->getLocale() === 'gu');
    @endphp
    <style>
        /* Event Details Page Font Enhancements */
        .event-details-content .rich-text,
        .event-details-content .rich-text p,
        .event-details-content .rich-text li {
            font-size: 15.5px !important;
            line-height: 1.75 !important;
            color: #334155 !important;
        }
        .event-details-content .rich-text h1,
        .event-details-content .rich-text h2,
        .event-details-content .rich-text h3 {
            font-size: 1.35rem !important;
            font-weight: 800 !important;
            margin-top: 1.25rem !important;
            margin-bottom: 0.5rem !important;
            color: #0f172a !important;
        }
    </style>

    <!-- Event Header Banner (Top Hero Section) -->
    <section class="relative bg-slate-950 text-white overflow-hidden py-10 md:py-14">
        <!-- Ambient background lighting -->
        <div class="absolute inset-0 bg-gradient-to-r from-slate-950 via-slate-900 to-slate-950"></div>
        <div class="absolute top-0 right-1/4 w-96 h-96 bg-primary-600/15 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                
                <!-- Left Side Text & Details -->
                <div class="{{ !empty($event->banner_path) ? 'lg:col-span-8' : 'lg:col-span-12' }} space-y-5">
                    <!-- Breadcrumbs -->
                    <nav class="flex items-center gap-2 text-sm font-semibold text-slate-300">
                        <a href="{{ route('home') }}" class="hover:text-white transition-colors flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                            <span>{{ __('messages.home') }}</span>
                        </a>
                        <span class="text-slate-600">/</span>
                        <a href="{{ route('events') }}" class="hover:text-white transition-colors">
                            <span>{{ __('messages.events') }}</span>
                        </a>
                        <span class="text-slate-600">/</span>
                        <span class="text-primary-400 font-bold truncate max-w-[240px]">{{ $event->title }}</span>
                    </nav>

                    <div class="flex items-center gap-2.5 flex-wrap">
                        <span class="inline-flex items-center px-3.5 py-1 rounded-full text-xs font-black bg-primary-500/20 text-primary-300 border border-primary-500/30 uppercase tracking-wider">
                            {{ __('messages.community_gathering') }}
                        </span>
                        @if($event->date < now()->toDateString())
                            <span class="text-xs font-black text-slate-200 bg-slate-800 px-3.5 py-1 rounded-full border border-slate-700 uppercase tracking-wider shadow-xs">{{ __('messages.passed') }}</span>
                        @else
                            <span class="text-xs font-black text-white bg-emerald-600 px-3.5 py-1 rounded-full border border-emerald-500 uppercase tracking-wider shadow-xs">{{ __('messages.upcoming') }}</span>
                        @endif

                        @if(($event->event_type ?? 'normal') === 'inam_vitaran')
                            <span class="inline-flex items-center gap-1.5 text-xs font-black text-white bg-amber-500 px-3.5 py-1 rounded-full border border-amber-400 uppercase tracking-wider shadow-xs">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
                                <span>{{ __('messages.inam') }}</span>
                            </span>
                        @elseif(($event->event_type ?? 'normal') === 'yuva_melo')
                            <span class="inline-flex items-center gap-1.5 text-xs font-black text-white bg-purple-600 px-3.5 py-1 rounded-full border border-purple-500 uppercase tracking-wider shadow-xs">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                <span>{{ __('messages.yuva') }}</span>
                            </span>
                        @endif
                    </div>

                    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black tracking-tight leading-tight text-white">
                        {{ $event->title }}
                    </h1>

                    <!-- Meta Details -->
                    <div class="flex flex-wrap items-center gap-3 text-sm text-slate-200 font-bold pt-1">
                        <div class="inline-flex items-center gap-2 bg-slate-900 border border-slate-800 px-4 py-2.5 rounded-xl whitespace-nowrap shadow-xs">
                            <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span>{{ date('F d, Y', strtotime($event->date)) }}</span>
                        </div>
                        @if($event->time)
                            <div class="inline-flex items-center gap-2 bg-slate-900 border border-slate-800 px-4 py-2.5 rounded-xl whitespace-nowrap shadow-xs">
                                <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>{{ date('h:i A', strtotime($event->time)) }}</span>
                            </div>
                        @endif
                        <div class="inline-flex items-center gap-2 bg-slate-900 border border-slate-800 px-4 py-2.5 rounded-xl shadow-xs">
                            <svg class="w-4 h-4 text-rose-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span class="truncate max-w-xs sm:max-w-md" title="{{ $event->venue }}">{{ $event->venue }}</span>
                        </div>
                        @if(!empty($event->registration_end_date))
                            <div class="inline-flex items-center gap-2 text-rose-200 font-extrabold bg-rose-950 px-4 py-2.5 rounded-xl border border-rose-800 whitespace-nowrap shadow-xs">
                                <svg class="w-4 h-4 text-rose-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>{{ __('messages.pass_purchase_last_date') }}: {{ date('d-M-Y', strtotime($event->registration_end_date)) }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Right Side Small Image -->
                @if(!empty($event->banner_path))
                    <div class="lg:col-span-4 flex justify-center lg:justify-end">
                        <div class="relative max-w-[220px] sm:max-w-[260px] w-full bg-white rounded-2xl p-1.5 border border-slate-700/40 shadow-xl">
                            <img src="{{ str_starts_with($event->banner_path, 'http') ? $event->banner_path : asset('storage/' . $event->banner_path) }}" 
                                 alt="{{ $event->title }}" 
                                 class="w-full max-h-[220px] sm:max-h-[250px] object-contain rounded-xl bg-white shadow-xs">
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>

    <!-- Event Content & Registration -->
    <section class="py-12 md:py-16 bg-white event-details-content"
             @close-all-modals.window="showSponsorModal = false"
             x-data="{
                 showSponsorModal: false,
                 selectedTypeId: '',
                 selectedTypeAmount: '',
                 selectedTypeTitle: '',
                 openSponsorModal(typeId = '', amount = '', title = '') {
                     this.selectedTypeId = typeId || '';
                     this.selectedTypeAmount = amount || '';
                     this.selectedTypeTitle = title || '';
                     this.showSponsorModal = true;
                 }
             }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if($errors->any())
                <div class="p-4 bg-rose-50 border border-rose-200 text-rose-900 rounded-2xl space-y-1.5 shadow-2xs">
                    <div class="font-bold text-xs sm:text-sm flex items-center gap-2">
                        <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>{{ $isGu ? 'કૃપા કરીને નીચેની ભૂલો સુધારો:' : 'Please correct the following errors:' }}</span>
                    </div>
                    <ul class="list-disc list-inside text-xs font-semibold pl-2 space-y-0.5">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start">
                
                <!-- Left: Description / Gallery (7 cols) -->
                <div class="lg:col-span-7 space-y-10">
                    <div class="space-y-4">
                        <h2 class="text-2xl font-black text-slate-900">{{ __('messages.event_description') }}</h2>
                        <div class="rich-text text-base text-slate-700 leading-relaxed">
                            {!! $event->description !!}
                        </div>
                    </div>

                    <!-- Event Gallery -->
                    @if($gallery->count() > 0)
                        <div class="space-y-6">
                            <h2 class="text-2xl font-black text-slate-900">{{ __('messages.event_gallery') }}</h2>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4"
                                 x-data="{ 
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

                                @foreach($gallery as $index => $photo)
                                    @php $gImgUrl = $photo->url; $isGVideo = $photo->isVideo(); @endphp
                                    <div class="aspect-video rounded-2xl overflow-hidden bg-slate-950 border border-slate-200/80 group relative cursor-pointer flex items-center justify-center shadow-xs"
                                         @click="lightboxIndex = {{ $index }}; lightbox = true">
                                        @if($isGVideo)
                                            <video class="w-full h-full object-cover pointer-events-none" preload="metadata" muted playsinline>
                                                <source src="{{ $gImgUrl }}">
                                            </video>
                                            <div class="absolute inset-0 flex items-center justify-center pointer-events-none" style="z-index: 2;">
                                                <div class="w-11 h-11 rounded-full bg-slate-900/85 border border-white/30 text-white flex items-center justify-center shadow-xl backdrop-blur-xs group-hover:scale-110 transition-transform">
                                                    <svg class="w-5 h-5 fill-current ml-0.5 text-primary-400" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                                </div>
                                            </div>
                                            <span class="absolute top-2.5 left-2.5 z-10 px-2.5 py-0.5 rounded-lg bg-slate-900/85 text-white text-[10px] font-black uppercase tracking-wider backdrop-blur-xs border border-white/20 flex items-center gap-1">
                                                <svg class="w-2.5 h-2.5 fill-current text-primary-400" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                                Video
                                            </span>
                                        @else
                                            <!-- Blurred Background Image -->
                                            <img src="{{ $gImgUrl }}"
                                                 alt=""
                                                 aria-hidden="true"
                                                 class="absolute inset-0 w-full h-full pointer-events-none select-none"
                                                 style="object-fit: cover; object-position: center; filter: blur(14px) brightness(0.45); transform: scale(1.12); z-index: 0;">

                                            <!-- Main Slide Image -->
                                            <img class="relative w-full h-full object-contain transition-transform duration-500 group-hover:scale-105"
                                                 style="z-index: 1;"
                                                 src="{{ $gImgUrl }}"
                                                 alt="{{ $photo->caption }}">
                                        @endif

                                        <!-- Hover Badge -->
                                        <div class="absolute inset-0 bg-black/30 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center" style="z-index: 10;">
                                            <span class="text-white text-xs font-bold inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-900/80 border border-white/20 backdrop-blur-xs shadow-md">
                                                @if($isGVideo)
                                                    <svg class="w-3.5 h-3.5 text-primary-400 fill-current" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                                    <span>Play Video</span>
                                                @else
                                                    <svg class="w-3.5 h-3.5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7"/></svg>
                                                    <span>Zoom</span>
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                @endforeach

                                 <!-- Lightbox Modal -->
                                 <div x-ref="lightboxModal" x-show="lightbox" 
                                      x-transition:enter="transition ease-out duration-300"
                                      x-transition:enter-start="opacity-0 scale-95"
                                      x-transition:enter-end="opacity-100 scale-100"
                                      x-transition:leave="transition ease-in duration-200"
                                      x-transition:leave-start="opacity-100 scale-100"
                                      x-transition:leave-end="opacity-0 scale-95"
                                      style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; z-index: 9999999 !important; background-color: rgba(2, 6, 23, 0.96); backdrop-filter: blur(20px); overflow: hidden;"
                                      class="flex flex-col items-center justify-between p-4 sm:p-6 select-none relative" 
                                      @click="lightbox = false" 
                                      x-cloak>
                                     
                                     <button x-show="galleryImages.length > 1" 
                                             @click.stop="prevImage()" 
                                             style="position: absolute; left: 1.5rem; top: 50%; transform: translateY(-50%); z-index: 10000000;"
                                             class="left-4 sm:left-8 group p-2.5 sm:p-3 rounded-full bg-slate-900/80 hover:bg-primary-500 text-white border border-white/20 hover:border-primary-400 shadow-2xl transition-all duration-300 hover:scale-110 active:scale-95 cursor-pointer backdrop-blur-md"
                                             title="Previous (Left Arrow)">
                                         <svg class="w-5 h-5 sm:w-6 sm:h-6 transition-transform duration-300 group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                             <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path>
                                         </svg>
                                     </button>

                                     <button x-show="galleryImages.length > 1" 
                                             @click.stop="nextImage()" 
                                             style="position: absolute; right: 1.5rem; top: 50%; transform: translateY(-50%); z-index: 10000000;"
                                             class="right-4 sm:right-8 group p-2.5 sm:p-3 rounded-full bg-slate-900/80 hover:bg-primary-500 text-white border border-white/20 hover:border-primary-400 shadow-2xl transition-all duration-300 hover:scale-110 active:scale-95 cursor-pointer backdrop-blur-md"
                                             title="Next (Right Arrow)">
                                         <svg class="w-5 h-5 sm:w-6 sm:h-6 transition-transform duration-300 group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                             <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path>
                                         </svg>
                                     </button>

                                     <div class="w-full flex items-center justify-end z-[10000000] max-w-7xl mx-auto pt-1 px-2" @click.stop>
                                         <button @click="lightbox = false" 
                                                 class="group p-2 rounded-full bg-slate-900/80 hover:bg-rose-500 text-white border border-white/20 hover:border-rose-400 transition-all duration-300 cursor-pointer shadow-xl hover:rotate-90 hover:scale-110 active:scale-95"
                                                 title="Close (Esc)">
                                             <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                                 <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                                             </svg>
                                         </button>
                                     </div>

                                     <div class="relative w-full flex-1 flex items-center justify-center my-auto max-w-5xl mx-auto px-4 pb-14" @click.stop>
                                         <div class="relative flex flex-col items-center justify-center max-w-full max-h-full">
                                             <div class="relative rounded-2xl overflow-hidden shadow-2xl border border-white/20 bg-slate-900/50 flex items-center justify-center">
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
                                                          :alt="galleryImages[lightboxIndex]?.caption || 'Gallery Media'" 
                                                          class="w-auto max-w-full object-contain rounded-xl shadow-2xl transition-all duration-300"
                                                          style="max-height: 68vh; max-width: 80vw;">
                                                 </template>
                                             </div>
                                         </div>
                                     </div>

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
                            </div>
                        </div>
                    @endif

                    <!-- Yuva Melo Biodata Registration Card (Below Gallery) -->
                    @if(($event->event_type ?? 'normal') === 'yuva_melo')
                        <div class="rounded-2xl sm:rounded-3xl p-6 bg-gradient-to-r from-purple-50 via-indigo-50/50 to-white border border-purple-200 shadow-sm space-y-4">
                            <div class="flex items-center justify-between gap-3 flex-wrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-2xl bg-purple-600 text-white flex items-center justify-center text-2xl font-black shadow-sm shrink-0">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                    </div>
                                    <div>
                                        <span class="text-xs font-extrabold text-purple-700 uppercase tracking-wider block">{{ __('messages.candidate_biodata_form') }}</span>
                                        <h3 class="text-lg sm:text-xl font-black text-slate-900 leading-tight">{{ __('messages.yuva_melo_registration') }}</h3>
                                    </div>
                                </div>

                                <!-- Fee Pill -->
                                <div class="bg-white border border-purple-200 px-4 py-2 rounded-xl flex items-center gap-2 shadow-2xs">
                                    <span class="text-sm text-slate-600 font-bold">{{ __('messages.form_fee') }}:</span>
                                    @if(($event->form_fee ?? 0) > 0)
                                        <span class="text-lg font-black text-purple-700">₹{{ number_format($event->form_fee) }}</span>
                                    @else
                                        <span class="text-xs font-black text-emerald-700 uppercase bg-emerald-50 px-2.5 py-1 rounded-md border border-emerald-200">{{ __('messages.free') }}</span>
                                    @endif
                                </div>
                            </div>

                            <p class="text-sm text-slate-600 leading-relaxed font-medium">
                                {{ __('messages.yuva_melo_card_desc') }}
                            </p>

                            <div class="pt-1 flex items-center">
                                <a href="{{ route('events.public_register_form', $event->id) }}"
                                    class="inline-flex items-center justify-center px-6 py-3 bg-purple-600 hover:bg-purple-700 active:scale-95 text-white text-sm font-extrabold rounded-xl shadow-xs transition-all cursor-pointer">
                                    <span>{{ __('messages.fill_yuva_form') ?? 'Fill Yuva Melo Registration Form' }}</span>
                                </a>
                            </div>
                        </div>
                    @endif

                    <!-- Sponsorship Opportunities / Packages Section -->
                    @if($sponsorshipTypes->isNotEmpty())
                        <div class="space-y-6 pt-6 border-t border-slate-100">
                            <div class="flex items-center justify-between gap-3 flex-wrap">
                                <div>
                                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-amber-500/10 text-amber-700 border border-amber-500/20 uppercase tracking-wider mb-1.5">
                                        <svg class="w-3.5 h-3.5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span>{{ __('messages.sponsorship') }}</span>
                                    </div>
                                    <h2 class="text-2xl font-black text-slate-900">{{ __('messages.sponsorship_opportunities') }}</h2>
                                    <p class="text-xs sm:text-sm text-slate-500 font-medium mt-0.5">
                                        {{ __('messages.sponsorship_desc') }}
                                    </p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                @foreach($sponsorshipTypes as $st)
                                    <div class="rounded-2xl p-5 border transition-all duration-300 flex flex-col justify-between {{ $st->is_full ? 'bg-slate-50 border-slate-200 opacity-75' : 'bg-gradient-to-br from-white via-amber-50/20 to-orange-50/30 border-amber-200/80 shadow-sm hover:shadow-md hover:-translate-y-0.5' }}">
                                        <div class="space-y-3">
                                            <div class="flex items-start justify-between gap-2">
                                                <div>
                                                    <span class="text-[10px] font-black text-amber-700 uppercase tracking-wider block">{{ $isGu ? 'સ્પોન્સરશિપ પેકેજ' : 'Sponsorship Package' }}</span>
                                                    <h3 class="text-base font-black text-slate-900 leading-tight mt-0.5">{{ $st->title }}</h3>
                                                </div>
                                                <div class="text-right shrink-0">
                                                    <div class="text-lg font-black text-slate-900">₹{{ number_format($st->amount) }}</div>
                                                </div>
                                            </div>

                                            @if($st->description)
                                                <p class="text-xs text-slate-600 leading-relaxed font-medium">
                                                    {{ $st->description }}
                                                </p>
                                            @endif

                                            <!-- Slots Available Badge -->
                                            <div class="pt-1">
                                                @if($st->is_full)
                                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[10px] font-black bg-rose-50 text-rose-700 border border-rose-200">
                                                        <svg class="w-3 h-3 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                                        <span>{{ __('messages.slots_full') }}</span>
                                                    </span>
                                                @elseif($st->max_sponsors > 0)
                                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[10px] font-black bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                        <svg class="w-3 h-3 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                                        <span>{{ __('messages.slots_remaining', ['count' => $st->available_slots, 'max' => $st->max_sponsors]) }}</span>
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[10px] font-black bg-blue-50 text-blue-700 border border-blue-200">
                                                        <svg class="w-3 h-3 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                        <span>{{ __('messages.unlimited') }} {{ $isGu ? 'સ્લોટ્સ' : 'Slots' }}</span>
                                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="pt-4 mt-2 border-t border-amber-100">
                                            @if($st->is_full)
                                                <button type="button" disabled
                                                    class="w-full py-2.5 bg-slate-200 text-slate-400 font-bold text-xs rounded-xl cursor-not-allowed text-center">
                                                    {{ __('messages.slots_full') }}
                                                </button>
                                            @else
                                                <button type="button" @click="openSponsorModal('{{ $st->id }}', '{{ (float)$st->amount }}', '{{ addslashes($st->title) }}')"
                                                    class="w-full py-2.5 bg-slate-900 hover:bg-slate-800 active:scale-95 text-white font-extrabold text-xs rounded-xl shadow-xs transition-all cursor-pointer flex items-center justify-center gap-1.5">
                                                    <svg class="w-3.5 h-3.5 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    <span>{{ __('messages.become_sponsor') }}</span>
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Our Proud Sponsors Section -->
                    @if($approvedSponsors->isNotEmpty())
                        <div class="space-y-4 pt-6 border-t border-slate-100">
                            <div class="flex items-center justify-between gap-2">
                                <h2 class="text-xl sm:text-2xl font-black text-slate-900 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-amber-500 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                                    <span>{{ __('messages.our_sponsors') }}</span>
                                </h2>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-black bg-emerald-50 text-emerald-700 border border-emerald-200">{{ $approvedSponsors->count() }}</span>
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                @foreach($approvedSponsors as $sp)
                                    <div class="bg-slate-50/80 hover:bg-white p-3 rounded-2xl border border-slate-200/80 hover:border-primary-300 hover:shadow-md transition-all flex flex-col items-center text-center group">
                                        @if($sp->logo_path)
                                            <div class="w-16 h-16 rounded-xl bg-white p-1 border border-slate-200 flex items-center justify-center overflow-hidden mb-2 shadow-2xs group-hover:scale-105 transition-transform">
                                                <img src="{{ str_starts_with($sp->logo_path, 'http') ? $sp->logo_path : asset('storage/' . $sp->logo_path) }}"
                                                     alt="{{ $sp->name }}" class="w-full h-full object-contain">
                                            </div>
                                        @else
                                            <div class="w-16 h-16 rounded-xl bg-primary-100 text-primary-800 font-black text-xl flex items-center justify-center border border-primary-200 mb-2 group-hover:scale-105 transition-transform shadow-2xs">
                                                {{ strtoupper(substr($sp->name, 0, 1)) }}
                                            </div>
                                        @endif
                                        <div class="font-extrabold text-slate-900 text-xs w-full break-words leading-snug" title="{{ $sp->name }}">{{ $sp->name }}</div>
                                        @if($sp->sponsorshipType)
                                            <span class="text-[10px] font-bold text-primary-700 bg-primary-50 px-2 py-0.5 rounded mt-1 border border-primary-100/60 text-center break-words">
                                                {{ $sp->sponsorshipType->title }}
                                            </span>
                                        @endif
                                        @if($sp->city)
                                            <span class="text-[9px] text-slate-400 font-medium mt-0.5 flex items-center justify-center gap-0.5">
                                                <svg class="w-2.5 h-2.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                <span>{{ $sp->city }}</span>
                                            </span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Right: Registration Widget (5 cols - Fixed/Sticky on Scroll) -->
                <div class="lg:col-span-5 lg:sticky self-start z-20" style="position: -webkit-sticky; position: sticky; top: 6rem;">
                    <div class="bg-white rounded-3xl p-5 sm:p-6 border border-slate-200 shadow-md space-y-4">
                        <div class="border-b border-slate-100 pb-3 flex items-center justify-between gap-2 flex-wrap">
                            <h3 class="text-base font-black text-slate-900">
                                @if(($event->event_type ?? 'normal') === 'inam_vitaran')
                                    {{ __('messages.inam_vitaran_registration') }}
                                @elseif(($event->event_type ?? 'normal') === 'yuva_melo')
                                    {{ __('messages.yuva_melo_registration') }}
                                @else
                                    {{ __('messages.event_registration') }}
                                @endif
                            </h3>
                            <span class="inline-flex items-center gap-1.5 text-xs font-black px-3 py-1 rounded-lg uppercase tracking-wider {{ ($event->event_type ?? 'normal') === 'inam_vitaran' ? 'text-amber-800 bg-amber-50 border border-amber-200' : (($event->event_type ?? 'normal') === 'yuva_melo' ? 'text-purple-800 bg-purple-50 border border-purple-200' : 'text-primary-700 bg-primary-50 border border-primary-200') }}">
                                @if(($event->event_type ?? 'normal') === 'inam_vitaran')
                                    <svg class="w-3.5 h-3.5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
                                    <span>{{ __('messages.inam_vitaran') }}</span>
                                @elseif(($event->event_type ?? 'normal') === 'yuva_melo')
                                    <svg class="w-3.5 h-3.5 text-purple-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                    <span>{{ __('messages.yuva_melo') }}</span>
                                @else
                                    <svg class="w-3.5 h-3.5 text-primary-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                                    <span>{{ __('messages.general_event') }}</span>
                                @endif
                            </span>
                        </div>

                        <div class="space-y-4">
                            @if($event->date < now()->toDateString())
                                <!-- Finished Event -->
                                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 text-slate-600 text-sm font-bold text-center">
                                    {{ __('messages.event_concluded') }}
                                </div>
                            @elseif($event->status === 'cancelled')
                                <!-- Cancelled Event -->
                                <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-600 text-sm font-bold text-center">
                                    {{ __('messages.event_cancelled') }}
                                </div>
                            @else
                                <!-- Active Event Registration check for all event types -->
                                <div x-data="{ showPassModal: false, showViewPassesModal: false, count: 1, passFee: {{ (float)($event->pass_fee ?? 0) }} }"
                                     @close-all-modals.window="showPassModal = false; showViewPassesModal = false"
                                     class="space-y-4">
                                    @if(auth()->guest())
                                        <div class="space-y-3.5">
                                            @if(($event->pass_fee ?? 0) > 0)
                                                <div class="bg-primary-50 border border-primary-200 rounded-2xl p-3.5 flex items-center justify-between">
                                                    <span class="text-sm font-bold text-primary-900">{{ __('messages.pass_fee_per_person') }}:</span>
                                                    <span class="text-base font-black text-primary-700">₹{{ number_format($event->pass_fee) }}</span>
                                                </div>
                                            @endif
                                            <p class="text-xs text-slate-600 text-center font-medium">{{ __('messages.please_login_to_register') }}</p>
                                            <a href="{{ route('login') }}"
                                                class="w-full flex items-center justify-center px-5 py-3.5 bg-primary-600 hover:bg-primary-500 active:scale-95 text-white text-sm font-extrabold rounded-xl shadow-md shadow-primary-500/20 transition-all text-center cursor-pointer">
                                                {{ __('messages.login_to_register') }}
                                            </a>
                                        </div>
                                    @elseif(auth()->check() && auth()->user()->hasRole('Administrator'))
                                        <div class="p-4 rounded-2xl bg-slate-50 text-slate-600 text-sm font-bold text-center border border-slate-200">
                                            {{ __('messages.administrator_account') }}
                                        </div>
                                    @elseif(auth()->check() && auth()->user()->status !== 'approved')
                                        <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-800 text-sm font-bold text-center leading-relaxed">
                                            {!! __('messages.account_status_currently', ['status' => '<strong>'.e(auth()->user()->status).'</strong>']) !!} {{ __('messages.register_once_approved') }}
                                        </div>
                                    @else
                                        <!-- Normal, Inam Vitaran and Yuva Melo Attendee Passes -->
                                        <div class="space-y-3.5">
                                            @if(($event->pass_fee ?? 0) > 0)
                                                <div class="bg-primary-50 border border-primary-200 rounded-2xl p-3.5 flex items-center justify-between">
                                                    <div>
                                                        <span class="text-sm font-black text-primary-900 block">{{ __('messages.pass_fee_per_person') }}</span>
                                                        <span class="text-xs text-slate-500 font-medium">{{ __('messages.per_person_pass_fee') }}</span>
                                                    </div>
                                                    <span class="text-base font-black text-primary-700">₹{{ number_format($event->pass_fee) }}</span>
                                                </div>
                                            @else
                                                <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-3.5 flex items-center justify-between">
                                                    <span class="text-sm font-bold text-emerald-900">{{ __('messages.registration_fee') }}:</span>
                                                    <span class="text-xs font-black text-emerald-700 uppercase bg-emerald-100 px-3 py-1 rounded-lg">{{ __('messages.free_entry') }}</span>
                                                </div>
                                            @endif

                                            @php
                                                $isFeeRequired = (float)($event->pass_fee ?? 0) > 0;
                                                $hasPaidPass = $registration && (!$isFeeRequired || ($registration->payment_status === 'paid'));
                                            @endphp

                                            @if($hasPaidPass)
                                                @php
                                                    $regPersons = max(1, (int)($registration->form_data['person_count'] ?? 1));
                                                    $userPasses = [];
                                                    $tokens = \App\Services\PassTokenService::getOrGenerateTokens($registration);
                                                    $basePassNo = (int) ($registration->pass_number ?: ($registration->form_data['registration_no'] ?? $registration->id));
                                                    if ($tokens->isNotEmpty()) {
                                                        foreach ($tokens as $idx => $tk) {
                                                            $userPasses[] = [
                                                                'passNo' => sprintf('%03d', $basePassNo + $idx),
                                                                'tokenHash' => $tk->token_hash,
                                                                'passCode' => $tk->pass_code,
                                                                'qrUrl' => \App\Services\PassTokenService::getQrCodeImageUrl($tk->token_hash),
                                                            ];
                                                        }
                                                    } else {
                                                        for ($i = 0; $i < $regPersons; $i++) {
                                                            $userPasses[] = [
                                                                'passNo' => sprintf('%03d', $basePassNo + $i),
                                                                'tokenHash' => '',
                                                                'passCode' => '',
                                                                'qrUrl' => '',
                                                            ];
                                                        }
                                                    }
                                                    $attendeeName = $registration->form_data['full_name'] ?? (auth()->user() ? auth()->user()->name : 'Member');
                                                    $memberId = auth()->user() ? sprintf('#%05d', auth()->user()->id) : ($registration->form_data['member_id'] ?? '-');
                                                    $logoUrl = App\Models\Setting::get('website_logo') ? asset('storage/' . App\Models\Setting::get('website_logo')) : asset('logo.png');
                                                @endphp

                                                <!-- Action Buttons Side-by-Side -->
                                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 pt-1">
                                                    <!-- View My Passes Button -->
                                                    <button type="button" 
                                                            @click="showViewPassesModal = true"
                                                            class="w-full flex items-center justify-center px-4 py-3 bg-slate-900 hover:bg-slate-800 active:scale-95 text-white text-xs sm:text-sm font-extrabold rounded-xl shadow-xs transition-all text-center cursor-pointer">
                                                        <span>{{ __('messages.view_my_passes', ['count' => $regPersons]) }}</span>
                                                    </button>

                                                    <!-- Purchase More Passes Button -->
                                                    <button type="button" 
                                                            @click="count = 1; showPassModal = true;"
                                                            class="w-full flex items-center justify-center px-4 py-3 bg-primary-600 hover:bg-primary-500 active:scale-95 text-white text-xs sm:text-sm font-extrabold rounded-xl shadow-md shadow-primary-500/20 transition-all text-center cursor-pointer">
                                                        <span>{{ __('messages.purchase_more_passes') }}</span>
                                                    </button>
                                                </div>
                                            @else
                                                <!-- Purchase Pass Button -->
                                                <button type="button" 
                                                        @click="count = 1; showPassModal = true;"
                                                        class="w-full flex items-center justify-center px-5 py-3.5 bg-primary-600 hover:bg-primary-500 active:scale-95 text-white text-sm font-extrabold rounded-xl shadow-md shadow-primary-500/20 transition-all text-center cursor-pointer">
                                                    <span>{{ __('messages.purchase_pass') }}</span>
                                                </button>
                                            @endif
                                        </div>

                                        @if($hasPaidPass)
                                            <!-- View Passes Modal (Teleported to Body) -->
                                            <template x-teleport="body">
                                                <div x-show="showViewPassesModal" 
                                                     class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-950/70 backdrop-blur-md"
                                                     x-transition
                                                     x-cloak>
                                                    <div @click.away="showViewPassesModal = false" 
                                                         class="bg-white rounded-3xl border border-slate-100 shadow-2xl max-w-2xl w-full max-h-[90vh] flex flex-col overflow-hidden relative">
                                                        
                                                        <!-- Modal Header -->
                                                        <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between shrink-0">
                                                            <div class="flex items-center gap-3">
                                                                <div class="w-9 h-9 rounded-xl bg-primary-600/30 border border-primary-500/40 text-primary-400 flex items-center justify-center">
                                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                                                                </div>
                                                                <div>
                                                                    <h3 class="text-sm font-extrabold flex items-center gap-2">
                                                                        <span>{{ __('messages.event_entry_passes') }}</span>
                                                                        <span class="text-[10px] bg-primary-500 text-white font-black px-2 py-0.5 rounded-full">{{ count($userPasses) }} {{ __('messages.passes') }}</span>
                                                                    </h3>
                                                                    <p class="text-[11px] text-slate-400 font-medium truncate max-w-[280px] sm:max-w-md">{{ $event->title }}</p>
                                                                </div>
                                                            </div>
                                                            <div class="flex items-center gap-2">
                                                                <button type="button" onclick="downloadAllPasses()" 
                                                                        class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-extrabold rounded-xl shadow-xs transition-colors flex items-center gap-1.5 cursor-pointer">
                                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                                                    <span>{{ __('messages.download_all_pdf') }}</span>
                                                                </button>
                                                                <button type="button" @click="showViewPassesModal = false" 
                                                                        class="w-7 h-7 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-colors text-xs font-bold cursor-pointer">
                                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                                </button>
                                                            </div>
                                                        </div>

                                                        <!-- Modal Scrollable Content containing all passes -->
                                                        <div class="p-4 sm:p-6 overflow-y-auto space-y-6 bg-slate-50 flex-1" id="printablePassesArea">
                                                            @foreach($userPasses as $idx => $pObj)
                                                                @php
                                                                    $pNo = is_array($pObj) ? $pObj['passNo'] : $pObj;
                                                                    $qrUrl = is_array($pObj) ? ($pObj['qrUrl'] ?? '') : '';
                                                                    $passCode = is_array($pObj) ? ($pObj['passCode'] ?? '') : '';
                                                                @endphp
                                                                <div class="relative bg-white rounded-2xl border-2 border-slate-900 shadow-md overflow-hidden text-slate-900 print-pass-item transition-all hover:shadow-xl" 
                                                                     id="pass-card-{{ $idx }}"
                                                                     data-pass-no="{{ $pNo }}"
                                                                     data-qr-url="{{ $qrUrl }}"
                                                                     data-event-title="{{ $event->title }}"
                                                                     data-mandal="Shree Satwara Gnati Mandal, Ahmedabad"
                                                                     data-date="{{ date('d-M-Y', strtotime($event->date)) }}{{ $event->time ? ' | ' . date('h:i A', strtotime($event->time)) : '' }}"
                                                                     data-venue="{{ $event->venue }}"
                                                                     data-attendee="{{ $attendeeName }}"
                                                                     data-member-code="{{ $memberId }}"
                                                                     data-logo="{{ $logoUrl }}">
                                                                    
                                                                    <!-- Left & Right Ticket Notches -->
                                                                    <div class="absolute -left-3 top-1/2 -translate-y-1/2 w-6 h-6 rounded-full bg-slate-50 border-2 border-slate-900 z-20"></div>
                                                                    <div class="absolute -right-3 top-1/2 -translate-y-1/2 w-6 h-6 rounded-full bg-slate-50 border-2 border-slate-900 z-20"></div>

                                                                    <!-- Top Bar Header -->
                                                                    <div class="bg-gradient-to-r from-slate-950 via-slate-900 to-indigo-950 text-white px-5 py-2.5 flex items-center justify-between text-[11px] font-black uppercase tracking-wider">
                                                                        <span class="flex items-center gap-2 truncate">
                                                                            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                                                                            <span>{{ $attendeeName }}</span>
                                                                            @if($memberId && $memberId !== '-')
                                                                                <span class="text-indigo-300 font-mono">({{ $memberId }})</span>
                                                                            @endif
                                                                        </span>
                                                                        <span class="px-2.5 py-0.5 rounded-full bg-amber-400/20 border border-amber-400/40 text-amber-300 text-[10px] font-black shrink-0 tracking-widest">
                                                                            VERIFIED PASS #{{ $idx + 1 }}
                                                                        </span>
                                                                    </div>

                                                                    <!-- Pass Core Body -->
                                                                    <div class="p-4 sm:p-5 flex flex-col sm:flex-row items-center sm:items-start justify-between gap-4 relative bg-white">
                                                                        <!-- Left: Mandal Logo -->
                                                                        <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl border-2 border-slate-900 bg-slate-50 p-1 flex items-center justify-center overflow-hidden shrink-0 shadow-xs">
                                                                            <img src="{{ $logoUrl }}" alt="Logo" class="w-full h-full object-contain" onerror="this.src='/logo.png'">
                                                                        </div>

                                                                        <!-- Center: Event & Mandal Info -->
                                                                        <div class="flex-1 space-y-1.5 text-center sm:text-left min-w-0">
                                                                            <div class="text-[11px] font-black text-slate-500 uppercase tracking-widest">
                                                                                Shree Satwara Gnati Mandal, Ahmedabad
                                                                            </div>
                                                                            <div class="text-base sm:text-lg font-black text-slate-950 leading-tight tracking-tight break-words">
                                                                                {{ $event->title }}
                                                                            </div>
                                                                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-xl bg-slate-100 text-slate-800 text-xs font-bold border border-slate-200">
                                                                                <svg class="w-3.5 h-3.5 text-indigo-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                                                <span>{{ __('messages.date') ?? 'Date' }}:</span>
                                                                                <span class="font-extrabold text-slate-950">{{ date('d-M-Y', strtotime($event->date)) }}</span>
                                                                                @if($event->time)
                                                                                    <span class="text-slate-400">|</span>
                                                                                    <span class="font-extrabold text-indigo-700">{{ date('h:i A', strtotime($event->time)) }}</span>
                                                                                @endif
                                                                            </div>
                                                                        </div>

                                                                        <!-- Right: Anti-Fraud QR Code & Pass Number Badge -->
                                                                        <div class="shrink-0 flex items-center gap-3 bg-slate-50 border-2 border-slate-900 p-2 rounded-2xl shadow-xs">
                                                                            @if(!empty($qrUrl))
                                                                                <div class="w-18 h-18 sm:w-20 sm:h-20 bg-white border border-slate-300 rounded-xl p-1 flex items-center justify-center shrink-0">
                                                                                    <img src="{{ $qrUrl }}" alt="QR Gate Pass" class="w-full h-full object-contain">
                                                                                </div>
                                                                            @endif
                                                                            <div class="text-center px-2">
                                                                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block">PASS NO</span>
                                                                                <span class="text-2xl font-black text-indigo-950 block mt-0.5 tracking-wider">{{ $pNo }}</span>
                                                                                @if(!empty($passCode))
                                                                                    <span class="inline-block mt-1 text-[8px] font-mono font-bold text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200">{{ $passCode }}</span>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <!-- Bottom Location Strip -->
                                                                    <div class="border-t-2 border-dashed border-slate-200 bg-slate-50 px-5 py-2.5 text-xs font-bold text-slate-800 flex items-center justify-between gap-2">
                                                                        <span class="flex items-center gap-1.5 min-w-0">
                                                                            <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                                            <span class="truncate"><strong>{{ __('messages.location') ?? 'Location' }}:</strong> {{ $event->venue }}</span>
                                                                        </span>
                                                                        <div class="flex items-center gap-2 shrink-0">
                                                                            <span class="text-[10px] text-slate-400 font-mono font-semibold hidden sm:inline uppercase tracking-widest">GATE SCANNER VALID</span>
                                                                            <button type="button" onclick="downloadSinglePass('pass-card-{{ $idx }}')"
                                                                                    class="flex items-center gap-1 px-2.5 py-1 bg-slate-900 hover:bg-slate-700 text-white text-[10px] font-extrabold rounded-lg transition-colors cursor-pointer no-print">
                                                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                                                                <span>{{ __('messages.download') ?? 'Download' }}</span>
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>

                                                        <!-- Modal Footer -->
                                                        <div class="px-6 py-3 bg-white border-t border-slate-100 flex items-center justify-between shrink-0">
                                                            <span class="text-[11px] text-slate-400 font-medium inline-flex items-center gap-1">
                                                                <svg class="w-3.5 h-3.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                                {{ __('messages.present_pass_at_entrance') }}</span>
                                                            <button type="button" @click="showViewPassesModal = false" 
                                                                    class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-colors cursor-pointer">
                                                                {{ __('messages.close') ?? 'Close' }}
                                                             </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>
                                        @endif

                                        <!-- Purchase Pass Pop-up Modal -->
                                        <template x-teleport="body">
                                            <div x-show="showPassModal" 
                                                 class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
                                                 x-transition:enter="ease-out duration-200"
                                                 x-transition:enter-start="opacity-0"
                                                 x-transition:enter-end="opacity-100"
                                                 x-transition:leave="ease-in duration-150"
                                                 x-transition:leave-start="opacity-100"
                                                 x-transition:leave-end="opacity-0"
                                                 x-cloak>
                                                <div @click.away="showPassModal = false" 
                                                     class="bg-white rounded-3xl border border-slate-100 shadow-2xl max-w-md w-full overflow-hidden relative">
                                                    
                                                    <!-- Modal Header -->
                                                    <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between">
                                                        <div>
                                                            <h3 class="text-sm font-extrabold flex items-center gap-2">
                                                                <svg class="w-4 h-4 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                                                                <span>{{ __('messages.purchase_event_pass') }}</span>
                                                            </h3>
                                                            <p class="text-[11px] text-slate-400 font-medium mt-0.5 truncate max-w-[280px]">{{ $event->title }}</p>
                                                        </div>
                                                        <button type="button" @click="showPassModal = false" 
                                                                class="w-7 h-7 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-colors text-xs font-bold cursor-pointer">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                        </button>
                                                    </div>

                                                    <!-- Modal Body / Form -->
                                                    <form method="POST" action="{{ route('events.public_register', $event->id) }}" id="publicEventRegisterForm" class="p-6 space-y-5">
                                                        @csrf
                                                        <input type="hidden" name="razorpay_payment_id" id="event_razorpay_payment_id">
                                                        <input type="hidden" name="person_count" :value="count">

                                                        <!-- Event Snippet -->
                                                        <div class="p-3 bg-slate-50 border border-slate-200 rounded-2xl flex items-center justify-between text-xs">
                                                            <span class="font-bold text-slate-700 inline-flex items-center gap-1">
                                                                <svg class="w-3.5 h-3.5 text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                                {{ date('d-M-Y', strtotime($event->date)) }}
                                                            </span>
                                                            @if(($event->pass_fee ?? 0) > 0)
                                                                <span class="font-black text-primary-700 bg-primary-50 px-2.5 py-1 rounded-xl border border-primary-200 text-xs">₹{{ number_format($event->pass_fee) }} / {{ __('messages.pass') }}</span>
                                                            @else
                                                                <span class="font-black text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-xl border border-emerald-200 text-xs">{{ __('messages.free_entry') }}</span>
                                                            @endif
                                                        </div>

                                                        <!-- Person Selector -->
                                                        <div class="space-y-2">
                                                            <label class="text-xs font-extrabold text-slate-800 flex items-center justify-between">
                                                                <span>{{ __('messages.ketla_person_attending') }}</span>
                                                                <span class="text-[10px] font-bold text-slate-400">{{ __('messages.total_persons_coming') }}</span>
                                                            </label>
                                                            
                                                            <div class="flex items-center justify-between bg-slate-50 p-2 rounded-2xl border border-slate-200 shadow-2xs">
                                                                <button type="button" 
                                                                        @click="if (count > 1) count--" 
                                                                        :disabled="count <= 1"
                                                                        class="w-12 h-12 rounded-xl bg-white hover:bg-slate-100 active:scale-95 disabled:opacity-40 disabled:cursor-not-allowed border border-slate-200 text-slate-800 font-black text-2xl flex items-center justify-center transition-all cursor-pointer shadow-xs">
                                                                    &minus;
                                                                </button>

                                                                <div class="flex items-center gap-2 px-3">
                                                                    <span class="text-2xl font-black text-primary-600" x-text="count"></span>
                                                                    <span class="text-xs font-extrabold text-slate-700 uppercase tracking-wider" x-text="count > 1 ? '{{ __('messages.persons') }}' : '{{ __('messages.person') }}'"></span>
                                                                </div>

                                                                <button type="button" 
                                                                        @click="if (count < 50) count++"
                                                                        :disabled="count >= 50"
                                                                        class="w-12 h-12 rounded-xl bg-white hover:bg-slate-100 active:scale-95 disabled:opacity-40 disabled:cursor-not-allowed border border-slate-200 text-slate-800 font-black text-2xl flex items-center justify-center transition-all cursor-pointer shadow-xs">
                                                                    &plus;
                                                                </button>
                                                            </div>
                                                        </div>

                                                        <!-- Total Breakdown -->
                                                        <div class="p-4 bg-primary-50/80 border border-primary-200/80 rounded-2xl space-y-2">
                                                            <div class="flex items-center justify-between text-xs font-bold text-slate-600">
                                                                <span>{{ __('messages.pass_fee') }}:</span>
                                                                <span>₹<span x-text="passFee"></span> &times; <span x-text="count"></span></span>
                                                            </div>
                                                            <div class="border-t border-primary-200/60 pt-2 flex items-center justify-between">
                                                                <span class="text-xs font-extrabold text-primary-950">{{ __('messages.total_amount') }}:</span>
                                                                <span class="text-lg font-black text-primary-700">₹<span x-text="(count * passFee).toLocaleString()"></span></span>
                                                            </div>
                                                        </div>

                                                        <!-- Action Buttons -->
                                                        <div class="flex items-center gap-3 pt-2">
                                                            <button type="button" @click="showPassModal = false" 
                                                                    class="px-4 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-2xl transition-colors cursor-pointer">
                                                                {{ __('messages.cancel') }}
                                                            </button>
                                                            <button type="submit" 
                                                                    class="flex-1 py-3.5 px-4 bg-primary-600 hover:bg-primary-700 active:scale-95 text-white font-black text-xs rounded-2xl shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer uppercase tracking-wider">
                                                                @if(($event->pass_fee ?? 0) > 0)
                                                                    <span>{{ __('messages.pay_and_confirm_booking') }} (₹<span x-text="(count * passFee).toLocaleString()"></span>)</span>
                                                                @else
                                                                    <span>{{ __('messages.confirm_registration_count') }} (<span x-text="count"></span> {{ __('messages.person') }})</span>
                                                                @endif
                                                                <span>&rarr;</span>
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </template>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Right Column: Location & Google Map Widget -->
                    @if(!empty($event->map_embed_url))
                        <div class="bg-white rounded-3xl p-5 sm:p-6 border border-slate-200 shadow-md space-y-3.5 mt-4">
                            <div class="flex items-center justify-between gap-2 flex-wrap border-b border-slate-100 pb-3">
                                <h3 class="text-sm sm:text-base font-black text-slate-900 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <span>{{ __('messages.event_location_venue') }}</span>
                                </h3>
                                @if($event->venue)
                                    <span class="text-[11px] font-bold text-slate-500 truncate max-w-[170px]" title="{{ $event->venue }}">{{ $event->venue }}</span>
                                @endif
                            </div>
                            <div class="rounded-2xl overflow-hidden border border-slate-200 shadow-2xs h-48 sm:h-56 w-full bg-slate-50">
                                <iframe src="{{ $event->map_embed_url }}" class="w-full h-full border-0" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Become a Sponsor Modal -->
        <template x-teleport="body">
            <div x-show="showSponsorModal"
                 class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 x-cloak>
                <div @click.away="showSponsorModal = false"
                     class="bg-white rounded-2xl border border-slate-100 shadow-2xl max-w-xl w-full overflow-hidden relative max-h-[90vh] flex flex-col">
                    
                    <!-- Modal Header -->
                    <div class="px-5 py-3 bg-slate-900 text-white flex items-center justify-between shrink-0">
                        <div>
                            <h3 class="text-xs sm:text-sm font-extrabold flex items-center gap-2">
                                <svg class="w-4 h-4 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>{{ __('messages.become_sponsor') }}</span>
                                <template x-if="selectedTypeTitle">
                                    <span class="text-amber-300 font-bold">&bull; <span x-text="selectedTypeTitle"></span></span>
                                </template>
                            </h3>
                            <p class="text-[10px] text-slate-400 font-medium mt-0.5 truncate max-w-[280px]">{{ $event->title }}</p>
                        </div>
                        <button type="button" @click="showSponsorModal = false" 
                                class="w-6 h-6 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-colors text-xs font-bold cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <!-- Modal Body / Form -->
                    <form method="POST" action="{{ route('events.sponsor.register', $event->id) }}" id="publicSponsorRegisterForm" enctype="multipart/form-data" class="p-4 sm:p-5 space-y-3 overflow-y-auto">
                        @csrf
                        <input type="hidden" name="razorpay_payment_id" id="sponsor_razorpay_payment_id">
                        <input type="hidden" name="sponsorship_type_id" x-model="selectedTypeId">
                        <input type="hidden" name="amount" x-model="selectedTypeAmount">
                        
                        <div>
                            <label class="text-[11px] font-bold text-slate-700 block mb-1">
                                {{ __('messages.sponsorship_type') }}
                            </label>
                            <div class="w-full flex items-center justify-between px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl">
                                <span class="text-xs font-bold text-slate-900" x-text="selectedTypeTitle || '{{ $isGu ? 'સામાન્ય સ્પોન્સર' : 'General Sponsor' }}'"></span>
                                <span class="text-xs font-black text-emerald-700" x-show="selectedTypeAmount">₹<span x-text="Number(selectedTypeAmount || 0).toLocaleString()"></span></span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            <div class="sm:col-span-2">
                                <label class="text-[11px] font-bold text-slate-700 block mb-1">
                                    {{ __('messages.sponsor_name') }} <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" name="name" required placeholder="{{ $isGu ? 'નામ / સ્પોન્સરનું નામ' : 'Sponsor / Company Name' }}"
                                    value="{{ auth()->user()?->name ?? '' }}"
                                    class="w-full text-xs font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary-500">
                            </div>

                            <div>
                                <label class="text-[11px] font-bold text-slate-700 block mb-1">
                                    {{ __('messages.phone') }} <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" name="mobile" required placeholder="9876543210" maxlength="10" minlength="10" pattern="[0-9]{10}" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)"
                                    value="{{ auth()->user()?->phone ?? '' }}"
                                    class="w-full text-xs font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary-500">
                            </div>

                            <div>
                                <label class="text-[11px] font-bold text-slate-700 block mb-1">
                                    {{ __('messages.email') }}
                                </label>
                                <input type="email" name="email" placeholder="Email Address"
                                    value="{{ auth()->user()?->email ?? '' }}"
                                    class="w-full text-xs font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary-500">
                            </div>

                            <input type="hidden" name="amount" x-model="selectedTypeAmount">

                            <div class="sm:col-span-2">
                                <label class="text-[11px] font-bold text-slate-700 block mb-1">
                                    {{ __('messages.address') }}
                                </label>
                                <textarea name="address" rows="2" placeholder="{{ $isGu ? 'સંપૂર્ણ સરનામું...' : 'Full Address...' }}"
                                    class="w-full text-xs font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary-500"></textarea>
                            </div>

                            <div class="sm:col-span-2">
                                <label class="text-[11px] font-bold text-slate-700 block mb-1">
                                    {{ __('messages.sponsor_logo') }} ({{ $isGu ? 'વૈકલ્પિક' : 'Optional' }})
                                </label>
                                <input type="file" name="logo" accept="image/*"
                                    class="text-xs block w-full text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-primary-50 file:text-primary-700 border border-slate-200 rounded-xl p-1 bg-slate-50">
                            </div>

                            <div class="sm:col-span-2">
                                <label class="text-[11px] font-bold text-slate-700 block mb-1">
                                    {{ $isGu ? 'સંદેશ / નોંધ (વૈકલ્પિક)' : 'Message / Remarks (Optional)' }}
                                </label>
                                <textarea name="notes" rows="2" placeholder="{{ $isGu ? 'કોઈ ખાસ સૂચના અથવા સંદેશ...' : 'Any message or notes...' }}"
                                    class="w-full text-xs font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary-500"></textarea>
                            </div>
                        </div>

                        <div class="pt-3 border-t border-slate-100 flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-2">
                            <button type="button" @click="showSponsorModal = false"
                                class="px-4 py-2 border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs rounded-xl transition-colors cursor-pointer text-center">
                                {{ __('messages.cancel') ?? 'Cancel' }}
                            </button>

                            <template x-if="Number(selectedTypeAmount || 0) > 0">
                                <div class="flex flex-col sm:flex-row items-stretch gap-2">
                                    <!-- <button type="submit"
                                        class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-extrabold text-xs rounded-xl transition-all cursor-pointer text-center">
                                        {{ $isGu ? 'ઓફલાઇન સબમિટ (Pay Later)' : 'Submit Offline (Pay Later)' }}
                                    </button> -->
                                    <button type="button" onclick="submitSponsorFormWithRazorpay()"
                                        class="px-5 py-2 bg-primary-600 hover:bg-primary-500 active:scale-95 text-white font-black text-xs rounded-xl shadow-md shadow-primary-500/20 transition-all cursor-pointer flex items-center justify-center gap-1.5 text-center">
                                        <svg class="w-3.5 h-3.5 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                        <span>{{ $isGu ? 'ઓનલાઇન ચુકવણી કરો & સબમિટ' : 'Pay Online & Submit' }} (₹<span x-text="Number(selectedTypeAmount || 0).toLocaleString()"></span>) &rarr;</span>
                                    </button>
                                </div>
                            </template>

                            <template x-if="!Number(selectedTypeAmount || 0)">
                                <button type="submit"
                                    class="px-5 py-2 bg-primary-600 hover:bg-primary-500 active:scale-95 text-white font-black text-xs rounded-xl shadow-md shadow-primary-500/20 transition-all cursor-pointer text-center">
                                    {{ $isGu ? 'સબમિટ કરો' : 'Submit Sponsorship' }} &rarr;
                                </button>
                            </template>
                        </div>
                    </form>
                </div>
            </div>
        </template>
    </section>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

<script>
const razorpayKey = "{{ \App\Models\Setting::get('razorpay_key_id', env('RAZORPAY_KEY_ID', '')) }}";
const appName = "{{ config('app.name', 'Shree Satwara Gnati Mandal, Ahmedabad') }}";

function submitSponsorFormWithRazorpay() {
    const sponsorForm = document.getElementById('publicSponsorRegisterForm');
    if (!sponsorForm) return;

    if (!sponsorForm.reportValidity()) {
        return; // HTML5 validation failed
    }

    const paymentIdInput = document.getElementById('sponsor_razorpay_payment_id');
    if (paymentIdInput && paymentIdInput.value) {
        sponsorForm.submit();
        return;
    }

    const amountInput = sponsorForm.querySelector('[name="amount"]');
    const sponsorAmount = amountInput ? parseFloat(amountInput.value) || 0 : 0;
    const totalAmountPaise = Math.round(sponsorAmount * 100);

    if (totalAmountPaise <= 0) {
        sponsorForm.submit();
        return;
    }

    const sponsorName = (sponsorForm.querySelector('[name="name"]')?.value || '').trim();
    const sponsorContact = (sponsorForm.querySelector('[name="mobile"]')?.value || '').trim();
    const sponsorEmail = (sponsorForm.querySelector('[name="email"]')?.value || '').trim();

    const options = {
        "key": razorpayKey || "rzp_test_key",
        "amount": totalAmountPaise,
        "currency": "INR",
        "name": appName,
        "description": "Event Sponsorship - {{ addslashes($event->title) }} (₹" + sponsorAmount.toLocaleString() + ")",
        "handler": function (response) {
            paymentIdInput.value = response.razorpay_payment_id;
            window.dispatchEvent(new CustomEvent('close-all-modals'));
            document.querySelectorAll('[x-show="showSponsorModal"]').forEach(function(el) {
                el.style.display = 'none';
            });
            window.dispatchEvent(new CustomEvent('show-loader'));
            sponsorForm.submit();
        },
        "prefill": {
            "name": sponsorName,
            "email": sponsorEmail,
            "contact": sponsorContact
        },
        "theme": {
            "color": "#D97706"
        },
        "modal": {
            "ondismiss": function() {
                // User closed or cancelled the Razorpay payment modal.
                // Do not submit the form and do not prompt to submit with pending status.
            }
        }
    };

    if (window.Razorpay) {
        const rzp = new Razorpay(options);
        rzp.on('payment.failed', function (response) {
            alert(response.error?.description || "{{ $isGu ? 'પેમેન્ટ અસફળ રહ્યું. કૃપા કરીને ફરી પ્રયાસ કરો.' : 'Payment failed. Please try again.' }}");
        });
        rzp.open();
    } else {
        alert("{{ $isGu ? 'પેમેન્ટ ગેટવે લોડ થઈ શક્યો નથી. કૃપા કરીને ફરી પ્રયાસ કરો.' : 'Payment gateway failed to load. Please try again.' }}");
    }
}

document.addEventListener('DOMContentLoaded', function () {
    /* =================== SPONSOR REGISTRATION INTERCEPT =================== */
    const sponsorForm = document.getElementById('publicSponsorRegisterForm');
    if (sponsorForm) {
        sponsorForm.addEventListener('submit', function (e) {
            const paymentIdInput = document.getElementById('sponsor_razorpay_payment_id');
            if (paymentIdInput && paymentIdInput.value) {
                return true; // Already paid
            }

            const amountInput = sponsorForm.querySelector('[name="amount"]');
            const sponsorAmount = amountInput ? parseFloat(amountInput.value) || 0 : 0;
            if (sponsorAmount > 0) {
                e.preventDefault();
                submitSponsorFormWithRazorpay();
                return false;
            }
            return true;
        });
    }

    /* =================== PASS REGISTRATION RAZORPAY =================== */
    const passForm = document.getElementById('publicEventRegisterForm');
    if (passForm) {
        passForm.addEventListener('submit', function (e) {
            const paymentIdInput = document.getElementById('event_razorpay_payment_id');
            if (paymentIdInput && paymentIdInput.value) {
                return true; // Already paid
            }

            const passFee = {{ (float)($event->pass_fee ?? 0) }};
            const personCountInput = passForm.querySelector('[name="person_count"]');
            const personCount = personCountInput ? parseInt(personCountInput.value) || 1 : 1;
            const totalAmountPaise = Math.round(passFee * personCount * 100);

            if (totalAmountPaise <= 0) {
                return true; // Free pass
            }

            e.preventDefault();

            const userEmail = "{{ auth()->user() ? auth()->user()->email : '' }}";
            const userName = "{{ auth()->user() ? auth()->user()->name : '' }}";
            const userPhone = "{{ (auth()->user() && auth()->user()->memberProfile) ? auth()->user()->memberProfile->phone : '' }}";

            const options = {
                "key": razorpayKey || "rzp_test_key",
                "amount": totalAmountPaise,
                "currency": "INR",
                "name": appName,
                "description": "Event Pass Booking - {{ addslashes($event->title) }} (" + personCount + " Person/s)",
                "handler": function (response) {
                    paymentIdInput.value = response.razorpay_payment_id;
                    window.dispatchEvent(new CustomEvent('close-all-modals'));
                    document.querySelectorAll('[x-show="showPassModal"], [x-show="showViewPassesModal"]').forEach(function(el) {
                        el.style.display = 'none';
                    });
                    window.dispatchEvent(new CustomEvent('show-loader'));
                    passForm.submit();
                },
                "modal": {
                    "ondismiss": function() {
                        // User cancelled pass payment modal, do not submit
                    }
                },
                "prefill": {
                    "name": userName,
                    "email": userEmail,
                    "contact": userPhone
                },
                "theme": {
                    "color": "#2563EB"
                }
            };

            if (window.Razorpay) {
                const rzp = new Razorpay(options);
                rzp.on('payment.failed', function (response) {
                    alert(response.error?.description || "{{ $isGu ? 'પેમેન્ટ અસફળ રહ્યું. કૃપા કરીને ફરી પ્રયાસ કરો.' : 'Payment failed. Please try again.' }}");
                });
                rzp.open();
            } else {
                alert("{{ $isGu ? 'પેમેન્ટ ગેટવે લોડ થઈ શક્યો નથી. કૃપા કરીને ફરી પ્રયાસ કરો.' : 'Payment gateway failed to load. Please try again.' }}");
            }
        });
    }
});
</script>

<script>
/* =================== PASS PDF DOWNLOAD =================== */

function _renderPassHtmlCard(passData) {
    const logoSrc = passData.logo || '/logo.png';
    const mandal = passData.mandal || 'Shree Satwara Gnati Mandal, Ahmedabad';
    const title = passData.title || '';
    const date = passData.date || '';
    const passNo = passData.passNo || '001';
    const venue = passData.venue || '';
    const attendee = passData.attendee || '';
    const memberCode = passData.memberCode || '';
    const topNameWithCode = attendee ? (attendee + (memberCode && memberCode !== '-' ? ' (' + memberCode + ')' : '')) : 'SHREE SATWARA GNATI MANDAL, AHMEDABAD';

    return `
    <div style="border: 2px solid #0f172a; border-radius: 12px; overflow: hidden; margin-bottom: 22px; page-break-inside: avoid; background: #ffffff; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; box-sizing: border-box;">
        <!-- Top Bar -->
        <table style="width: 100%; border-collapse: collapse; background-color: #0f172a; color: #ffffff;">
            <tr>
                <td style="padding: 7px 16px; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; text-align: left; color: #ffffff;">
                    ${topNameWithCode}
                </td>
                <td style="padding: 7px 16px; font-size: 11px; font-weight: 900; text-transform: uppercase; letter-spacing: 1px; text-align: right; color: #f59e0b;">
                    ENTRY PASS
                </td>
            </tr>
        </table>

        <!-- Main Body -->
        <table style="width: 100%; border-collapse: collapse; background-color: #ffffff;">
            <tr>
                <!-- Circular Logo -->
                <td style="width: 90px; vertical-align: middle; padding: 14px 0 14px 16px; text-align: center;">
                    <div style="width: 76px; height: 76px; border-radius: 50%; border: 2px solid #cbd5e1; background-color: #f8fafc; overflow: hidden; display: inline-block;">
                        <img src="${logoSrc}" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.style.display='none'">
                    </div>
                </td>

                <!-- Details (Mandal, Title, Date) -->
                <td style="vertical-align: middle; padding: 14px 16px; text-align: left;">
                    <div style="font-size: 11px; font-weight: 900; text-transform: uppercase; letter-spacing: 1.2px; color: #0f172a; margin-bottom: 4px;">
                        ${mandal}
                    </div>
                    <div style="font-size: 16px; font-weight: 900; color: #e11d48; line-height: 1.25; margin-bottom: 6px;">
                        ${title}
                    </div>
                    <div style="font-size: 12px; font-weight: 700; color: #334155; display: flex; align-items: center; gap: 4px;">
                        <svg style="width: 14px; height: 14px; color: #e11d48; display: inline-block; vertical-align: middle;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>${date}</span>
                    </div>
                </td>

                <!-- Pass No & QR Code Box -->
                <td style="width: 170px; vertical-align: middle; padding: 14px 16px 14px 0; text-align: right;">
                    <div style="display: inline-flex; align-items: center; gap: 8px;">
                        ${passData.qrUrl ? `
                        <div style="border: 2px solid #0f172a; border-radius: 10px; background-color: #ffffff; padding: 3px; width: 68px; height: 68px; box-sizing: border-box; text-align: center;">
                            <img src="${passData.qrUrl}" style="width: 100%; height: 100%; object-fit: contain;">
                        </div>
                        ` : ''}
                        <div style="display: inline-block; border: 2px solid #0f172a; border-radius: 10px; background-color: #f8fafc; padding: 8px 12px; text-align: center; min-width: 80px; box-sizing: border-box;">
                            <div style="font-size: 8px; font-weight: 800; text-transform: uppercase; letter-spacing: 1.5px; color: #64748b;">PASS NO.</div>
                            <div style="font-size: 20px; font-weight: 900; letter-spacing: 3px; color: #0f172a; margin-top: 2px;">${passNo}</div>
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <!-- Bottom Location Strip -->
        <div style="border-top: 2px dashed #e2e8f0; background-color: #f8fafc; padding: 9px 16px; font-size: 11px; font-weight: 700; color: #334155; display: flex; align-items: center; gap: 4px;">
            <svg style="width: 14px; height: 14px; color: #e11d48; display: inline-block; vertical-align: middle;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <span><strong>Location / Venue:</strong> ${venue}</span>
        </div>
    </div>`;
}

function _openPassesPrintWindow(cardsHtml, title) {
    const w = window.open('', '_blank', 'width=880,height=750');
    if (!w) {
        alert('Please allow pop-ups for this website to download passes.');
        return;
    }
    w.document.write(`<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>${title}</title>
    <style>
        * { 
            box-sizing: border-box; 
            margin: 0; 
            padding: 0; 
            -webkit-print-color-adjust: exact !important; 
            print-color-adjust: exact !important; 
            color-adjust: exact !important; 
        }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; 
            background: #ffffff; 
            padding: 24px; 
            color: #0f172a; 
            -webkit-print-color-adjust: exact !important; 
            print-color-adjust: exact !important; 
            color-adjust: exact !important; 
        }
        @media print {
            body { 
                padding: 0; 
                -webkit-print-color-adjust: exact !important; 
                print-color-adjust: exact !important; 
                color-adjust: exact !important; 
            }
            * {
                -webkit-print-color-adjust: exact !important; 
                print-color-adjust: exact !important; 
                color-adjust: exact !important; 
            }
            @page { margin: 15mm; size: auto; }
        }
    </style>
</head>
<body>
    ${cardsHtml}
</body>
</html>`);
    w.document.close();
    w.focus();
    setTimeout(() => { w.print(); }, 500);
}

function downloadAllPasses() {
    const cards = document.querySelectorAll('.print-pass-item');
    if (!cards.length) return;
    let html = '';
    cards.forEach(card => {
        const data = {
            passNo: card.dataset.passNo || card.querySelector('.text-2xl')?.innerText.trim() || card.querySelector('.text-xl')?.innerText.trim() || '001',
            qrUrl: card.dataset.qrUrl || '',
            title: card.dataset.eventTitle || '',
            mandal: card.dataset.mandal || 'Shree Satwara Gnati Mandal, Ahmedabad',
            date: card.dataset.date || '',
            venue: card.dataset.venue || '',
            attendee: card.dataset.attendee || '',
            memberCode: card.dataset.memberCode || '',
            logo: card.dataset.logo || card.querySelector('img')?.src || ''
        };
        html += _renderPassHtmlCard(data);
    });
    _openPassesPrintWindow(html, 'Event Entry Passes');
}

function downloadSinglePass(cardId) {
    const card = document.getElementById(cardId);
    if (!card) return;
    const data = {
        passNo: card.dataset.passNo || card.querySelector('.text-2xl')?.innerText.trim() || card.querySelector('.text-xl')?.innerText.trim() || '001',
        qrUrl: card.dataset.qrUrl || '',
        title: card.dataset.eventTitle || '',
        mandal: card.dataset.mandal || 'Shree Satwara Gnati Mandal, Ahmedabad',
        date: card.dataset.date || '',
        venue: card.dataset.venue || '',
        attendee: card.dataset.attendee || '',
        memberCode: card.dataset.memberCode || '',
        logo: card.dataset.logo || card.querySelector('img')?.src || ''
    };
    _openPassesPrintWindow(_renderPassHtmlCard(data), 'Event Entry Pass - ' + data.passNo);
}
</script>
@endsection