@extends('layouts.public')

@section('content')
@include('partials.page_header', [
    'title' => __('messages.events'),
    'subtitle' => __('messages.events_subtitle'),
    'breadcrumb' => __('messages.events')
])

<style>
    /* Events Page Typography Enhancements */
    .events-page-container h3 {
        font-size: 1.15rem !important; /* ~18.5px */
        font-weight: 800 !important;
        line-height: 1.35 !important;
    }
    .events-page-container p {
        font-size: 14px !important;
        line-height: 1.65 !important;
        color: #475569 !important;
    }
</style>

<!-- Events Grid -->
<section class="py-8 bg-transparent events-page-container">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @forelse($events as $event)
                <a href="{{ route('event.details', $event->id) }}" class="group bg-white rounded-3xl overflow-hidden border border-slate-200/80 flex flex-col hover:shadow-xl hover:-translate-y-1 transition-all duration-300 block cursor-pointer">
                    <div class="relative h-48 w-full overflow-hidden shrink-0 bg-white">
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
                            <img
                                class="absolute inset-0 w-full h-full object-contain bg-white group-hover:scale-105 transition-transform duration-500"
                                src="{{ str_starts_with($event->banner_path, 'http') ? $event->banner_path : asset('storage/' . $event->banner_path) }}"
                                alt="{{ $event->title }}"
                                onerror="this.style.display='none'">
                        @endif

                        {{-- Date Badge (Top-Left overlay) --}}
                        <div class="absolute top-3.5 left-3.5 z-10">
                            <span class="text-xs font-black text-white px-3 py-1.5 rounded-xl uppercase tracking-wider shadow-lg flex items-center gap-1.5"
                                style="background-color: #0f172a !important; color: #ffffff !important; box-shadow: 0 4px 12px rgba(0,0,0,0.35); border: 1px solid rgba(255,255,255,0.25);">
                                <svg class="w-3.5 h-3.5 text-slate-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span>{{ date('M d, Y', strtotime($event->date)) }}</span>
                            </span>
                        </div>

                        {{-- Status & Type Badges (Top-Right overlay) --}}
                        <div class="absolute top-3.5 right-3.5 z-10 flex flex-col items-end gap-1.5">
                            @if($event->date < now()->toDateString())
                                <span class="text-xs font-black text-white bg-slate-600 px-3 py-1 rounded-xl uppercase tracking-wider shadow-md">{{ __('messages.passed') }}</span>
                            @else
                                <span class="text-xs font-black text-white bg-emerald-600 px-3 py-1 rounded-xl uppercase tracking-wider shadow-md">{{ __('messages.upcoming') }}</span>
                            @endif

                            @if(($event->event_type ?? 'normal') === 'inam_vitaran')
                                <span class="inline-flex items-center gap-1 text-xs font-black text-white bg-amber-500 px-3 py-1 rounded-xl uppercase tracking-wider shadow-md">
                                    <svg class="w-3 h-3 text-amber-100 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
                                    <span>{{ __('messages.inam') }}</span>
                                </span>
                            @elseif(($event->event_type ?? 'normal') === 'yuva_melo')
                                <span class="inline-flex items-center gap-1 text-xs font-black text-white bg-purple-600 px-3 py-1 rounded-xl uppercase tracking-wider shadow-md">
                                    <svg class="w-3 h-3 text-purple-100 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                    <span>{{ __('messages.yuva') }}</span>
                                </span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="p-6 flex-grow flex flex-col justify-between space-y-4">
                        <div class="space-y-2">
                            <h3 class="text-base sm:text-lg font-extrabold text-slate-900 group-hover:text-primary-600 transition-colors line-clamp-1" title="{{ $event->title }}">{{ $event->title }}</h3>
                            @php
                                $cleanDesc = preg_replace('/<span class="ql-ui"[^>]*>.*?<\/span>/i', '', $event->description);
                                $cleanDesc = strip_tags(str_replace(['<br>', '</p>', '</li>', '</div>'], ' ', $cleanDesc));
                                $cleanDesc = trim(preg_replace('/\s+/', ' ', $cleanDesc));
                            @endphp
                            <p class="text-sm text-slate-600 line-clamp-2 leading-relaxed break-words">
                                {{ \Illuminate\Support\Str::limit($cleanDesc, 110, '...') }}
                            </p>
                        </div>

                        <div class="flex justify-between items-center pt-3.5 border-t border-slate-100 min-w-0 gap-2 flex-wrap">
                            <div class="min-w-0 flex-1">
                                <span class="inline-flex items-center gap-1 text-xs sm:text-sm font-bold text-slate-500 truncate max-w-full" title="{{ $event->venue }}">
                                    <svg class="w-3.5 h-3.5 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <span class="truncate">{{ $event->venue }}</span>
                                </span>
                            </div>
                            @if(!empty($event->registration_end_date) && ($event->event_type ?? 'normal') !== 'normal')
                                <span class="inline-flex items-center gap-1 text-xs font-extrabold text-rose-600 bg-rose-50 px-2.5 py-1 rounded-lg border border-rose-100 whitespace-nowrap">
                                    <svg class="w-3 h-3 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span>{{ __('messages.last_date') }}: {{ $event->passPurchaseDeadlineLabel() }}</span>
                                </span>
                            @endif
                        </div>
                    </div>
                </a>
            @empty
                <div class="col-span-3 text-center py-20 text-slate-400">
                    <p class="text-base font-bold">{{ __('messages.no_events_scheduled') }}</p>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $events->links() }}
        </div>
    </div>
</section>
@endsection
