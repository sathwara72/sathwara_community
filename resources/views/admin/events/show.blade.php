@extends('layouts.admin')

@section('page_title', __('messages.event_details'))

@section('content')
    <div class="space-y-4" 
         x-data="adminEventShowData()"
         @keydown.window.escape="galleryLightbox = false"
         @keydown.window.right="if(galleryLightbox) nextGalleryImage()"
         @keydown.window.left="if(galleryLightbox) prevGalleryImage()">
        @php
            $user = auth()->user();
            $userPerms = $user->permissions->pluck('name');
            $canEditThisEvent = $user->hasRole('Administrator') ||
                $userPerms->contains('events_manage') ||
                $userPerms->contains('event_manage_' . $event->id) ||
                $userPerms->contains('event_edit_' . $event->id);

            $canDeleteThisEvent = $user->hasRole('Administrator') ||
                $userPerms->contains('events_manage') ||
                $userPerms->contains('event_manage_' . $event->id);

            $totalCount = count($registrations);
            $approvedCount = $registrations->where('status', 'approved')->count();
            $rejectedCount = $registrations->where('status', 'rejected')->count();
            $galleryCount = $gallery->count();
            $sponsorsCount = $sponsors->count();
            $sponsorshipTypesCount = $sponsorshipTypes->count();
            $approvedSponsorsCount = $sponsors->where('status', 'approved')->count();
            $pendingSponsorsCount = $sponsors->where('status', 'pending')->count();
            $totalSponsorshipAmount = $sponsors->where('status', 'approved')->sum('amount');
        @endphp

        <!-- Top Navigation Bar: Back Button + Main Tabs + Action Buttons -->
        <div
            class="bg-white p-2 sm:p-2.5 rounded-xl border border-slate-100 shadow-sm flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
            <div class="flex items-center gap-2 overflow-x-auto no-scrollbar">
                <!-- Back Button -->
                <a href="{{ route('admin.events.index') }}"
                    class="px-3 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-extrabold text-xs transition-colors flex items-center gap-1.5 shrink-0 shadow-2xs">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>{{ __('messages.back') }}</span>
                </a>

                <!-- Tab 1: Details -->
                <button type="button" @click="mainTab = 'details'"
                    :class="mainTab === 'details' ? 'bg-primary-500 text-white shadow-xs font-black' : 'text-slate-600 hover:text-slate-900 font-bold hover:bg-slate-100'"
                    class="px-3 py-1.5 text-xs rounded-lg transition-all flex items-center gap-1.5 whitespace-nowrap cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>{{ __('messages.event_details') }}</span>
                </button>

                @if(($event->event_type ?? 'normal') !== 'normal')
                <!-- Tab 2: Submissions / Registrations (Only for Inam Vitaran & Yuva Melo) -->
                <button type="button" @click="mainTab = 'submissions'"
                    :class="mainTab === 'submissions' ? 'bg-primary-500 text-white shadow-xs font-black' : 'text-slate-600 hover:text-slate-900 font-bold hover:bg-slate-100'"
                    class="px-3 py-1.5 text-xs rounded-lg transition-all flex items-center gap-1.5 whitespace-nowrap cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                    </svg>
                    <span>{{ ($event->event_type ?? 'normal') === 'inam_vitaran' ? __('messages.student_inam_submissions') : (($event->event_type ?? 'normal') === 'yuva_melo' ? __('messages.yuva_melo_candidate_submissions') : __('messages.event_registrations')) }}</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-black"
                        :class="mainTab === 'submissions' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700'">{{ $totalCount }}</span>
                </button>
                @endif

                <!-- Tab 3: Gallery -->
                <button type="button" @click="mainTab = 'gallery'"
                    :class="mainTab === 'gallery' ? 'bg-primary-500 text-white shadow-xs font-black' : 'text-slate-600 hover:text-slate-900 font-bold hover:bg-slate-100'"
                    class="px-3 py-1.5 text-xs rounded-lg transition-all flex items-center gap-1.5 whitespace-nowrap cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span>{{ __('messages.gallery') }}</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-black"
                        :class="mainTab === 'gallery' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700'">{{ $galleryCount }}</span>
                </button>

                <!-- Tab 4: Sponsorship -->
                <button type="button" @click="mainTab = 'sponsorship'"
                    :class="mainTab === 'sponsorship' ? 'bg-primary-500 text-white shadow-xs font-black' : 'text-slate-600 hover:text-slate-900 font-bold hover:bg-slate-100'"
                    class="px-3 py-1.5 text-xs rounded-lg transition-all flex items-center gap-1.5 whitespace-nowrap cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ __('messages.sponsorship') }}</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-black"
                        :class="mainTab === 'sponsorship' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700'">{{ $sponsorsCount }}</span>
                    @if($pendingSponsorsCount > 0)
                        <span class="w-2 h-2 rounded-full bg-amber-400 animate-ping"></span>
                    @endif
                </button>
            </div>

            <!-- Header Action Buttons (Scanner, Edit & Delete) -->
            <div class="flex items-center gap-1.5 shrink-0 justify-end">
                <a href="{{ route('admin.events.scanner', $event->id) }}"
                    class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white font-black text-xs rounded-lg shadow-xs transition-colors flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                    </svg>
                    <span>Gate QR Scanner</span>
                </a>
                @if($canEditThisEvent)
                    @include('admin.partials.fee_settings_popup', [
                        'title' => 'Fees — ' . $event->title,
                        'action' => route('admin.fees.event', $event->id),
                        'fields' => array_values(array_filter([
                            ['name' => 'pass_fee', 'label' => 'Pass purchase (per person)', 'value' => $event->pass_fee, 'help' => 'Free: passes are issued without payment.'],
                            $event->event_type === 'yuva_melo'
                                ? ['name' => 'form_fee', 'label' => 'Yuva Melo candidate form', 'value' => $event->form_fee, 'help' => 'Free: candidates submit the form without paying.']
                                : null,
                        ])),
                    ])
                    <a href="{{ route('admin.events.edit', $event->id) }}"
                        class="px-3 py-1.5 bg-amber-50 text-amber-700 hover:bg-amber-100 border border-amber-200/60 font-extrabold text-xs rounded-lg transition-colors flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        <span>{{ __('messages.edit') }}</span>
                    </a>
                @endif

                @if($canDeleteThisEvent)
                    <button type="button"
                        @click="$dispatch('confirm-delete', { action: '{{ route('admin.events.destroy', $event->id) }}', message: '{{ __('messages.delete_confirm_event', ['name' => $event->title]) }}' })"
                        class="px-3 py-1.5 bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200/60 font-extrabold text-xs rounded-lg transition-colors flex items-center gap-1.5 cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        <span>{{ __('messages.delete') }}</span>
                    </button>
                @endif
            </div>
        </div>

        <!-- ================= TAB 1: EVENT DETAILS ================= -->
        <div x-show="mainTab === 'details'" x-transition class="space-y-4">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 items-stretch">
                <!-- Left Side: Cover Photo Card -->
                <div class="lg:col-span-1 flex flex-col">
                    <div
                        class="bg-white rounded-xl border border-slate-100 shadow-sm p-4 flex-1 flex items-center justify-center min-h-[260px] max-h-[380px]">
                        @if($event->banner_path)
                            <img src="{{ str_starts_with($event->banner_path, 'http') ? $event->banner_path : asset('storage/' . $event->banner_path) }}"
                                alt="{{ $event->title }}" class="w-full h-full object-contain max-h-[360px] rounded-lg">
                        @else
                            <div class="text-center text-slate-400 font-medium py-12">
                                <span class="text-3xl block mb-2">🖼️</span>
                                <span>No banner image uploaded</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Right Side: Title, Badges, Schedule & Description -->
                <div class="lg:col-span-1 space-y-3 flex flex-col justify-between">
                    <!-- Event Header Info & Schedule Card -->
                    <div class="bg-white p-4 rounded-xl border border-slate-100 shadow-sm space-y-3">
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <h1 class="text-base sm:text-lg font-black text-slate-900 leading-tight">{{ $event->title }}
                                </h1>

                                <!-- Status Badge -->
                                <span
                                    class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase tracking-wider
                                        {{ $event->status == 'published' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/60' : ($event->status == 'cancelled' ? 'bg-rose-50 text-rose-700 border border-rose-200/60' : 'bg-slate-100 text-slate-600 border border-slate-200') }}">
                                    {{ __('messages.' . strtolower($event->status)) != 'messages.' . strtolower($event->status) ? __('messages.' . strtolower($event->status)) : ucfirst($event->status) }}
                                </span>

                                <!-- Event Type Badge -->
                                @if($event->event_type === 'inam_vitaran')
                                    <span
                                        class="px-2 py-0.5 rounded bg-amber-50 text-amber-800 border border-amber-200 text-[10px] font-extrabold uppercase tracking-wider">🏆
                                        {{ __('messages.inam_vitaran') }}</span>
                                @elseif($event->event_type === 'yuva_melo')
                                    <span
                                        class="px-2 py-0.5 rounded bg-purple-50 text-purple-800 border border-purple-200 text-[10px] font-extrabold uppercase tracking-wider">⚡
                                        {{ __('messages.yuva_melo') }}</span>
                                @else
                                    <span
                                        class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 border border-slate-200 text-[10px] font-extrabold uppercase tracking-wider">📢
                                        {{ __('messages.general_event') }}</span>
                                @endif

                                <!-- Fee Badge -->
                                @if($event->pass_fee > 0)
                                    <span
                                        class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-800 border border-emerald-200 text-[10px] font-extrabold uppercase tracking-wider">🎟️
                                        ₹{{ number_format($event->pass_fee, 2) }} {{ __('messages.fee') }}</span>
                                @endif

                                @if($event->event_type === 'yuva_melo' && ($event->form_fee ?? 0) > 0)
                                    <span
                                        class="px-2 py-0.5 rounded bg-purple-50 text-purple-800 border border-purple-200 text-[10px] font-extrabold uppercase tracking-wider">⚡
                                        ₹{{ number_format($event->form_fee, 2) }} {{ __('messages.form_fee') }}</span>
                                @endif
                            </div>
                        </div>

                        <!-- Schedule & Location -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 text-xs pt-2 border-t border-slate-100">
                            <!-- Date & Time -->
                            <div class="flex items-start gap-2.5 p-2.5 bg-slate-50 rounded-xl border border-slate-100">
                                <div
                                    class="w-7 h-7 rounded-lg bg-primary-50 text-primary-600 flex items-center justify-center shrink-0 text-xs">
                                    📅
                                </div>
                                <div>
                                    <p class="text-[9px] font-bold text-slate-400 uppercase">
                                        {{ __('messages.date_and_time') }}
                                    </p>
                                    <p class="font-extrabold text-slate-900 text-xs">
                                        {{ date('d M Y (l)', strtotime($event->date)) }}
                                    </p>
                                    <p class="font-semibold text-slate-600 text-[10px]">
                                        {{ date('h:i A', strtotime($event->time)) }}
                                    </p>
                                </div>
                            </div>

                            <!-- Location -->
                            <div class="flex items-start gap-2.5 p-2.5 bg-slate-50 rounded-xl border border-slate-100">
                                <div
                                    class="w-7 h-7 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center shrink-0 text-xs">
                                    📍
                                </div>
                                <div>
                                    <p class="text-[9px] font-bold text-slate-400 uppercase">
                                        {{ __('messages.location') }}
                                    </p>
                                    <p class="font-extrabold text-slate-900 text-xs line-clamp-1" title="{{ $event->venue }}">
                                        {{ $event->venue ?: __('messages.not_specified') }}
                                    </p>
                                    <p class="font-semibold text-slate-600 text-[10px] line-clamp-1" title="{{ $event->city }}">
                                        {{ $event->city }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sequence & Summary Statistics Box -->
                    <div class="bg-slate-900 p-4 rounded-xl border border-slate-800 text-white space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black tracking-wide uppercase flex items-center gap-1.5 text-primary-300">
                                <span>🔢</span> {{ __('messages.event_sequence_summary') }}
                            </span>
                            <span class="text-[10px] bg-slate-700/80 px-2 py-0.5 rounded font-mono text-slate-300">
                                Event #{{ $event->id }}
                            </span>
                        </div>

                        <div class="grid grid-cols-2 gap-2.5 text-center">
                            <!-- Passes Sequence (Always shown) -->
                            <div class="bg-slate-800/80 p-2.5 rounded-lg border border-slate-700/70 flex flex-col justify-center items-center {{ ($event->event_type ?? 'normal') === 'normal' ? 'col-span-2' : '' }}">
                                <span class="text-[9px] font-extrabold text-slate-400 uppercase block truncate">{{ __('messages.total_passes') }}</span>
                                <span class="text-base font-black text-emerald-400 block">{{ $stats['total_passes'] ?? 0 }}</span>
                            </div>

                            @if(($event->event_type ?? 'normal') === 'inam_vitaran')
                            <!-- Inam Forms Sequence -->
                            <div class="bg-slate-800/80 p-2.5 rounded-lg border border-slate-700/70 flex flex-col justify-center items-center">
                                <span class="text-[9px] font-extrabold text-slate-400 uppercase block truncate">{{ __('messages.inam_forms') }}</span>
                                <span class="text-base font-black text-amber-400 block">{{ $stats['total_inam_forms'] ?? 0 }}</span>
                            </div>
                            @elseif(($event->event_type ?? 'normal') === 'yuva_melo')
                            <!-- Yuva Melo Sequence -->
                            <div class="bg-slate-800/80 p-2.5 rounded-lg border border-slate-700/70 flex flex-col justify-center items-center">
                                <span class="text-[9px] font-extrabold text-slate-400 uppercase block truncate">{{ __('messages.yuva_melo_forms') }}</span>
                                <span class="text-lg font-black text-purple-400 block">{{ $stats['total_yuva_forms'] ?? 0 }}</span>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Description Card -->
                    <div class="bg-white p-4 rounded-xl border border-slate-100 shadow-sm space-y-2 grow">
                        <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-400">
                            {{ __('messages.description') }}
                        </h3>

                        <div class="rich-text text-xs text-slate-700 leading-relaxed font-medium">
                            {!! $event->description ?: __('messages.no_description_provided') !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= TAB 2: SUBMISSIONS / REGISTRATIONS ================= -->
        <div x-show="mainTab === 'submissions'" x-transition class="space-y-4">
            @php
                // 1. Process student registrations: extract Standard and compute percentage
                $processedStudents = $registrations->map(function ($r) {
                    $fd = $r->form_data ?? [];

                    $rawStd = trim((string) ($fd['schoolStandard'] ?? $fd['standard'] ?? $fd['school_standard'] ?? $fd['education'] ?? $fd['course'] ?? 'General'));
                    $stream = trim((string) ($fd['schoolStream'] ?? $fd['stream'] ?? ''));

                    if (!empty($stream) && $stream !== 'Other' && !str_contains($rawStd, $stream)) {
                        $stdName = $rawStd . ' (' . $stream . ')';
                    } else {
                        $stdName = $rawStd ?: 'General';
                    }

                    $r->std_name = $stdName;

                    // Calculate numeric percentage
                    $pct = 0;
                    if (!empty($fd['percentage'])) {
                        $cleaned = preg_replace('/[^0-9.]/', '', (string) $fd['percentage']);
                        $pct = (float) $cleaned;
                    } elseif (!empty($fd['received_marks']) && !empty($fd['total_marks']) && (float) $fd['total_marks'] > 0) {
                        $pct = round(((float) $fd['received_marks'] / (float) $fd['total_marks']) * 100, 2);
                    }
                    $r->calc_pct = $pct;
                    return $r;
                });

                // 2. Group by standard and sort standard names in natural order (1st, 2nd ... 10th, 12th)
                $groupedByStandard = $processedStudents->groupBy('std_name')->sortBy(function ($students, $key) {
                    if (preg_match('/(\d+)/', $key, $m)) {
                        return (int) $m[1];
                    }
                    return 999;
                });

                $allStandardsList = $groupedByStandard->keys()->toArray();

                // 3. Calculate rank 1 to N within each standard!
                $stdRanksMap = [];
                $top5InStdIds = [];
                $top3InStdIds = [];

                foreach ($groupedByStandard as $stdName => $studentsInStd) {
                    $sortedStd = $studentsInStd->sortByDesc('calc_pct')->values();
                    foreach ($sortedStd as $idx => $student) {
                        $rank = $idx + 1;
                        $stdRanksMap[$student->id] = $rank;
                        if ($rank <= 5) {
                            $top5InStdIds[] = $student->id;
                        }
                        if ($rank <= 3) {
                            $top3InStdIds[] = $student->id;
                        }
                    }
                }
            @endphp

            <!-- Header Toolbar inside Tab 2: Single Unified Row -->
            <div
                class="bg-white p-2.5 rounded-xl border border-slate-100 shadow-sm flex items-center justify-between gap-2 overflow-x-auto no-scrollbar flex-nowrap">
                <div class="flex items-center gap-2 shrink-0">
                    <!-- Total Submissions Badge -->
                    <div
                        class="px-2.5 py-1.5 bg-amber-50 rounded-xl border border-amber-200 text-amber-900 text-xs font-bold inline-flex items-center gap-1.5 whitespace-nowrap shrink-0">
                        <span>🏆 {{ __('messages.total_prefix') }}</span>
                        <span class="font-black text-amber-800">{{ $totalCount }}</span>
                    </div>

                    @if(($event->event_type ?? 'normal') === 'inam_vitaran')
                        <!-- Standard Filter Dropdown -->
                        <div class="relative shrink-0">
                            <select x-model="selectedStandard"
                                class="text-xs font-bold px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-primary-500 shadow-2xs text-slate-800 cursor-pointer">
                                <option value="all">📚 {{ __('messages.all_standards') }}
                                    ({{ count($allStandardsList) }})</option>
                                @foreach($groupedByStandard as $stdName => $stdStudents)
                                    <option value="{{ $stdName }}">{{ $stdName }} ({{ $stdStudents->count() }})</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Top Rankers Filter Buttons -->
                        <div
                            class="flex items-center gap-1 p-1 bg-slate-50 rounded-xl border border-slate-200/80 shadow-2xs shrink-0">
                            <button type="button" @click="topRankFilter = 'all'"
                                :class="topRankFilter === 'all' ? 'bg-slate-900 text-white font-extrabold shadow-xs' : 'text-slate-600 hover:text-slate-900 font-semibold'"
                                class="px-2.5 py-1 text-xs rounded-lg transition-all cursor-pointer whitespace-nowrap">
                                {{ __('messages.all_students') }}
                            </button>
                            <button type="button" @click="topRankFilter = 'top5'"
                                :class="topRankFilter === 'top5' ? 'bg-amber-500 text-white font-black shadow-xs' : 'text-amber-800 hover:text-amber-900 font-bold bg-amber-50/60'"
                                class="px-2.5 py-1 text-xs rounded-lg transition-all flex items-center gap-1 cursor-pointer whitespace-nowrap"
                                title="{{ __('messages.top_5_tooltip') }}">
                                <span>🌟 {{ __('messages.top_5_per_standard') }}</span>
                            </button>
                            <button type="button" @click="topRankFilter = 'top3'"
                                :class="topRankFilter === 'top3' ? 'bg-amber-600 text-white font-black shadow-xs' : 'text-amber-800 hover:text-amber-900 font-bold bg-amber-50/40'"
                                class="px-2.5 py-1 text-xs rounded-lg transition-all flex items-center gap-1 cursor-pointer whitespace-nowrap"
                                title="{{ __('messages.top_3_tooltip') }}">
                                <span>🥇 {{ __('messages.top_3') }}</span>
                            </button>
                        </div>
                    @endif
                </div>

                <!-- Search input & Export Button -->
                <div class="flex items-center gap-1.5 shrink-0">
                    <div class="relative w-36 sm:w-44">
                        <input type="text" x-model="search"
                            placeholder="{{ ($event->event_type ?? 'normal') === 'inam_vitaran' ? __('messages.search_student_school') : (($event->event_type ?? 'normal') === 'yuva_melo' ? __('messages.search_candidate') : __('messages.search')) }}"
                            class="text-xs font-semibold pl-7 pr-6 py-1.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-primary-500 w-full transition-colors">
                        <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2 top-1/2 -translate-y-1/2" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <button type="button" x-show="search" @click="search = ''"
                            class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-rose-500 font-extrabold text-xs"
                            title="Clear search">
                            &times;
                        </button>
                    </div>

                    @if(($event->event_type ?? 'normal') === 'inam_vitaran')
                        <a :href="'{{ route('admin.events.inam_submissions.export', $event->id) }}' + '?top=' + topRankFilter + '&standard=' + encodeURIComponent(selectedStandard) + '&search=' + encodeURIComponent(search)"
                            class="px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold text-xs rounded-xl border border-emerald-200/60 shadow-xs transition-colors shrink-0 inline-flex items-center gap-1.5 whitespace-nowrap">
                            <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span>{{ __('messages.export_excel') }}</span>
                        </a>
                    @elseif(($event->event_type ?? 'normal') === 'yuva_melo')
                        <a href="{{ route('admin.events.yuva_submissions.export', $event->id) }}"
                            class="px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold text-xs rounded-xl border border-emerald-200/60 shadow-xs transition-colors shrink-0 inline-flex items-center gap-1.5 whitespace-nowrap">
                            <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span>{{ __('messages.export_candidates_excel') }}</span>
                        </a>
                    @else
                        <a href="{{ route('admin.events.registrations.export', $event->id) }}"
                            class="px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold text-xs rounded-xl border border-emerald-200/60 shadow-xs transition-colors shrink-0 inline-flex items-center gap-1.5 whitespace-nowrap">
                            <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span>{{ __('messages.export_excel') }}</span>
                        </a>
                    @endif
                </div>
            </div>

            @if(($event->event_type ?? 'normal') === 'inam_vitaran')
                <!-- ================= EXCEL-STYLE STANDARD-WISE SECTIONS ================= -->
                <div class="space-y-6">
                    @forelse($groupedByStandard as $stdName => $stdStudents)
                        @php
                            $sortedStdStudents = $stdStudents->sortByDesc('calc_pct')->values();
                            $topScore = $sortedStdStudents->first()->calc_pct ?? 0;
                        @endphp
                        <div x-show="selectedStandard === 'all' || selectedStandard === '{{ addslashes($stdName) }}'"
                            class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-4 space-y-3.5">

                            <!-- Standard Section Header Strip -->
                            <div
                                class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-slate-100 bg-slate-50/70 -mx-4 -mt-4 p-4 rounded-t-2xl">
                                <div class="flex items-center gap-2.5">
                                    <span
                                        class="w-8 h-8 rounded-xl bg-amber-500 text-white flex items-center justify-center font-black text-sm shadow-xs">
                                        📚
                                    </span>
                                    <div>
                                        <h3 class="text-sm font-black text-slate-900 flex items-center gap-2">
                                            <span>{{ $stdName }}</span>
                                            <span
                                                class="px-2 py-0.5 rounded-full text-[10px] font-black bg-blue-50 text-blue-700 border border-blue-200">
                                                {{ $stdStudents->count() }} {{ __('messages.candidates') }}
                                            </span>
                                        </h3>
                                        <p class="text-[11px] text-slate-500 font-medium">
                                            {{ __('messages.top_rankers_desc') }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2">
                                    @if($topScore > 0)
                                        <span
                                            class="px-2.5 py-1 bg-amber-50 rounded-xl border border-amber-200 text-amber-900 font-black text-[11px] inline-flex items-center gap-1 shadow-2xs">
                                            ⭐ {{ __('messages.top_percentage') }} <span
                                                class="text-amber-700 font-black">{{ $topScore }}%</span>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Table View for this Standard -->
                            <div class="rounded-xl border border-slate-200 overflow-hidden">
                                <table class="w-full text-left border-collapse text-xs">
                                    <thead>
                                        <tr
                                            class="bg-slate-50 text-slate-800 font-black border-b border-slate-200 uppercase text-xs sm:text-[13px] tracking-wider">
                                            <th class="py-2.5 px-2 text-center w-20">{{ __('messages.rank') }}</th>
                                            <th class="py-2.5 px-2.5">{{ __('messages.student_name') }}</th>
                                            <th class="py-2.5 px-2">{{ __('messages.father_name') }}</th>
                                            <th class="py-2.5 px-2 text-center">{{ __('messages.percentage') }}</th>
                                            <th class="py-2.5 px-2">{{ __('messages.school_college') }}</th>
                                            <th class="py-2.5 px-2">{{ __('messages.contact') }}</th>
                                            <th class="py-2.5 px-2 text-center w-28">{{ __('messages.marksheet') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 bg-white">
                                        @foreach($sortedStdStudents as $index => $reg)
                                            @php
                                                $fd = $reg->form_data ?? [];
                                                $userName = $fd['student_name'] ?? ($reg->user ? $reg->user->name : 'Student');
                                                $parentName = $fd['father_name'] ?? ($fd['parent_name'] ?? '-');
                                                $userPhone = $fd['mobile_no'] ?? $fd['mobile'] ?? ($reg->user ? ($reg->user->memberProfile->phone ?? null) : null);
                                                $userCity = $fd['city'] ?? $fd['native_place'] ?? '-';
                                                $schoolName = $fd['school_college'] ?? '-';
                                                $stdRank = $stdRanksMap[$reg->id] ?? ($index + 1);
                                                $pctStr = !empty($fd['percentage']) ? (str_contains((string) $fd['percentage'], '%') ? $fd['percentage'] : $fd['percentage'] . '%') : ($reg->calc_pct > 0 ? $reg->calc_pct . '%' : '-');
                                                $marksStr = (!empty($fd['received_marks']) && !empty($fd['total_marks'])) ? ($fd['received_marks'] . ' / ' . $fd['total_marks']) : null;
                                                $marksheetUrl = $fd['marksheet_url'] ?? $fd['marksheet'] ?? $fd['result_photo'] ?? $fd['result_url'] ?? null;
                                                $inamNoFormatted = $reg->inam_number ? sprintf('%03d', $reg->inam_number) : (isset($fd['registration_no']) && is_numeric($fd['registration_no']) ? sprintf('%03d', (int)$fd['registration_no']) : sprintf('%03d', $index + 1));
                                            @endphp
                                            <tr x-show="(!search || 
                                                                     '{{ $inamNoFormatted }}'.includes(search.trim()) ||
                                                                     '{{ (int)$inamNoFormatted }}' === search.trim() ||
                                                                     '{{ addslashes(strtolower($userName)) }}'.includes(search.toLowerCase()) || 
                                                                     '{{ addslashes(strtolower($parentName)) }}'.includes(search.toLowerCase()) || 
                                                                     '{{ addslashes(strtolower($userCity)) }}'.includes(search.toLowerCase()) || 
                                                                     '{{ addslashes(strtolower($schoolName)) }}'.includes(search.toLowerCase()) || 
                                                                     '{{ addslashes(strtolower($pctStr)) }}'.includes(search.toLowerCase())) &&
                                                                    (topRankFilter === 'all' || (topRankFilter === 'top5' && {{ in_array($reg->id, $top5InStdIds) ? 'true' : 'false' }}) || (topRankFilter === 'top3' && {{ in_array($reg->id, $top3InStdIds) ? 'true' : 'false' }}))"
                                                class="hover:bg-amber-50/40 transition-colors {{ $stdRank === 1 ? 'bg-amber-50/20' : ($stdRank === 2 ? 'bg-slate-50/30' : '') }}">

                                                <!-- Rank Badge Column -->
                                                <td class="py-2 px-2 text-center whitespace-nowrap">
                                                    @if($stdRank === 1)
                                                        <span
                                                            class="inline-flex items-center justify-center px-1.5 py-0.5 rounded-md text-[10px] font-black bg-amber-100 text-amber-900 border border-amber-300 shadow-2xs">
                                                            🥇 {{ __('messages.rank_1') }}
                                                        </span>
                                                    @elseif($stdRank === 2)
                                                        <span
                                                            class="inline-flex items-center justify-center px-1.5 py-0.5 rounded-md text-[10px] font-black bg-slate-200 text-slate-900 border border-slate-300">
                                                            🥈 {{ __('messages.rank_2') }}
                                                        </span>
                                                    @elseif($stdRank === 3)
                                                        <span
                                                            class="inline-flex items-center justify-center px-1.5 py-0.5 rounded-md text-[10px] font-black bg-amber-50 text-amber-800 border border-amber-200">
                                                            🥉 {{ __('messages.rank_3') }}
                                                        </span>
                                                    @elseif($stdRank <= 5)
                                                        <span
                                                            class="inline-flex items-center justify-center px-1.5 py-0.5 rounded-md text-[10px] font-black bg-emerald-50 text-emerald-800 border border-emerald-200">
                                                            🎖️ {{ __('messages.rank_n', ['rank' => $stdRank]) }}
                                                        </span>
                                                    @else
                                                        <span
                                                            class="inline-flex items-center justify-center px-1.5 py-0.2 rounded text-[10px] font-bold text-slate-500 bg-slate-100">
                                                            {{ __('messages.rank_n', ['rank' => $stdRank]) }}
                                                        </span>
                                                    @endif
                                                </td>

                                                <!-- Student Name & Sequence Number -->
                                                <td class="py-2 px-2.5">
                                                    <div class="flex items-center gap-1.5 flex-wrap">
                                                        <span class="px-1.5 py-0.5 bg-amber-50 text-amber-800 border border-amber-200/80 rounded font-mono font-bold text-[10px]">
                                                            Inam #{{ $inamNoFormatted }}
                                                        </span>
                                                        <span class="font-black text-slate-900 text-xs block leading-tight">{{ $userName }}</span>
                                                        @php $missing = $reg->missingDetails(); @endphp
                                                        @if($missing)
                                                            <span class="inline-block mt-0.5 px-1.5 py-0.5 rounded bg-rose-50 text-rose-700 border border-rose-200 text-[9px] font-black uppercase tracking-wider" title="Missing: {{ implode(', ', $missing) }}">Incomplete: {{ implode(', ', $missing) }}</span>
                                                        @endif
                                                    </div>
                                                </td>

                                                <!-- Father Name -->
                                                <td class="py-2 px-2 text-slate-700 font-semibold text-xs">
                                                    {{ $parentName }}
                                                </td>

                                                <!-- Percentage & Marks -->
                                                <td class="py-2 px-2 text-center whitespace-nowrap">
                                                    <span
                                                        class="inline-block font-black text-amber-800 bg-amber-50 px-1.5 py-0.5 rounded-md border border-amber-200 text-xs">
                                                        {{ $pctStr }}
                                                    </span>
                                                    @if($marksStr)
                                                        <span
                                                            class="block text-[9px] font-semibold text-slate-400 mt-0.5">({{ $marksStr }})</span>
                                                    @endif
                                                </td>

                                                <!-- School / College -->
                                                <td class="py-2 px-2 text-slate-700 font-medium text-xs max-w-[200px] truncate"
                                                    title="{{ $schoolName }}">
                                                    {{ $schoolName }}
                                                </td>

                                                <!-- Contact -->
                                                <td class="py-2 px-2 text-slate-700 font-bold text-xs whitespace-nowrap">
                                                    {{ $userPhone ?: '-' }}
                                                </td>

                                                <!-- Marksheet -->
                                                <td class="py-2 px-2 text-center whitespace-nowrap">
                                                    @if(!empty($marksheetUrl))
                                                        <a href="{{ str_starts_with($marksheetUrl, 'http') ? $marksheetUrl : asset('storage/' . $marksheetUrl) }}"
                                                            target="_blank"
                                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 font-bold text-[10px] transition-colors shadow-2xs">
                                                            <span>📄 {{ __('messages.marksheet') }} ↗</span>
                                                        </a>
                                                    @else
                                                        <span
                                                            class="text-slate-400 font-medium text-[10px]">{{ __('messages.no_file') }}</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @empty
                        <div class="py-12 text-center text-slate-400 font-medium bg-white rounded-xl border border-slate-100">
                            {{ __('messages.no_student_submissions') }}
                        </div>
                    @endforelse
                </div>
            @else
                <!-- For Yuva Melo & General Events: Single Grid -->
                <div class="bg-white p-4 rounded-xl border border-slate-100 shadow-sm space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
                        @forelse($registrations as $index => $reg)
                            @php
                                $fd = $reg->form_data ?? [];
                                $memberCode = $reg->user ? sprintf('#%05d', $reg->user->id) : (is_scalar($fd['member_id'] ?? null) ? (string) $fd['member_id'] : '-');
                                $isYuvaCandidate = ($event->event_type ?? 'normal') === 'yuva_melo' || !empty($fd['surname']);

                                if ($isYuvaCandidate) {
                                    $rawName = ((!empty($fd['first_name']) ? $fd['first_name'] : '') . ' ' . (!empty($fd['surname']) ? $fd['surname'] : '')) ?: ($fd['full_name'] ?? ($reg->user ? $reg->user->name : 'Candidate'));
                                } else {
                                    $rawName = $reg->user ? $reg->user->name : ($fd['full_name'] ?? $fd['first_name'] ?? 'Participant');
                                }
                                $userName = is_scalar($rawName) ? (string) $rawName : 'Participant';

                                $rawParent = !empty($fd['father_name']) ? $fd['father_name'] : ($fd['parent_name'] ?? ($reg->user ? $reg->user->name : '-'));
                                $parentName = is_scalar($rawParent) ? (string) $rawParent : '-';

                                $rawPhone = $fd['mobile_no'] ?? $fd['mobile'] ?? $fd['contact_number'] ?? ($reg->user ? ($reg->user->memberProfile->phone ?? null) : null);
                                $userPhone = is_scalar($rawPhone) ? (string) $rawPhone : null;

                                $rawCity = $fd['native_place'] ?? $fd['city'] ?? $fd['area'] ?? $fd['district'] ?? ($reg->user ? ($reg->user->memberProfile->city ?? null) : null);
                                if (is_array($rawCity)) {
                                    $userCity = implode(', ', array_filter($rawCity, 'is_scalar'));
                                } elseif (is_object($rawCity)) {
                                    $userCity = $rawCity->name ?? (string) $rawCity;
                                } else {
                                    $userCity = is_scalar($rawCity) ? (string) $rawCity : null;
                                }

                                $yuvaNoFormatted = $reg->yuva_melo_number ? sprintf('%03d', $reg->yuva_melo_number) : (isset($fd['registration_no']) && is_numeric($fd['registration_no']) ? sprintf('%03d', (int)$fd['registration_no']) : sprintf('%03d', $index + 1));
                                $regNo = $yuvaNoFormatted;

                                $modalData = [
                                    'member_code' => $memberCode,
                                    'yuva_melo_number' => $yuvaNoFormatted,
                                    'user_name' => $userName,
                                    'email' => $reg->user ? $reg->user->email : ($fd['email'] ?? '-'),
                                    'phone' => $userPhone ?? '-',
                                    'city' => $userCity ?? '-',
                                    'person_count' => (int) ($fd['person_count'] ?? 1),
                                    'is_selected' => (bool) $reg->is_selected,
                                    'payment_status' => $reg->payment_status ?? 'paid',
                                    'payment_amount' => $reg->payment_amount ?? 0,
                                    'payment_id' => $reg->payment_id ?? '-',
                                    'transaction_nos' => $reg->transactions->pluck('transaction_no')->implode(', '),
                                    'date' => $reg->created_at ? $reg->created_at->format('d-M-Y h:i A') : '',
                                    'form_data' => $fd,
                                    'index' => $regNo,
                                ];
                            @endphp
                            <div x-show="(!search || 
                                                 '{{ $yuvaNoFormatted }}'.includes(search.trim()) ||
                                                 '{{ (int)$yuvaNoFormatted }}' === search.trim() ||
                                                 '{{ addslashes(strtolower($userName)) }}'.includes(search.toLowerCase()) || 
                                                 '{{ addslashes(strtolower($memberCode)) }}'.includes(search.toLowerCase()) || 
                                                 '{{ addslashes(strtolower($userCity ?? '')) }}'.includes(search.toLowerCase())) &&
                                                (statusTab === 'all' || statusTab === '{{ $reg->status }}')"
                                class="bg-white border border-slate-200/80 rounded-xl p-3 hover:shadow-md hover:border-primary-400 transition-all flex flex-col justify-between gap-2.5">

                                <div class="space-y-2.5">
                                    <!-- Yuva Melo Candidate Card Layout -->
                                    <div>
                                        <div class="flex items-center justify-between gap-1.5 mb-1">
                                            <span class="px-2 py-0.5 bg-purple-50 text-purple-800 border border-purple-200/80 rounded-md font-mono font-black text-[10px]">
                                                Yuva Melo #{{ $yuvaNoFormatted }}
                                            </span>
                                            <span class="text-[9px] font-bold text-slate-400">{{ $reg->created_at ? $reg->created_at->format('d-M-Y') : '' }}</span>
                                        </div>
                                        <h4 class="text-xs font-black text-slate-900 truncate" title="{{ $userName }}">
                                            {{ $userName }}</h4>
                                        @php $missing = $reg->missingDetails(); @endphp
                                        @if($missing)
                                            <span class="inline-block mt-0.5 px-1.5 py-0.5 rounded bg-rose-50 text-rose-700 border border-rose-200 text-[9px] font-black uppercase tracking-wider" title="Missing: {{ implode(', ', $missing) }}">Incomplete: {{ implode(', ', $missing) }}</span>
                                        @endif
                                        @if($parentName !== '-')
                                            <p class="text-[9px] text-slate-600 font-semibold truncate mt-0.5">👨‍👦 {{ $parentName }}
                                            </p>
                                        @endif
                                    </div>

                                    <!-- Phone & Native Place Row -->
                                    <div
                                        class="flex items-center justify-between text-[9px] bg-slate-50 rounded-lg p-1.5 border border-slate-100">
                                        <span class="font-bold text-blue-700">📞 {{ $userPhone ?: '-' }}</span>
                                        @if($userCity)
                                            <span class="text-slate-600 font-bold truncate max-w-[100px]">📍 {{ $userCity }}</span>
                                        @endif
                                    </div>

                                    <!-- Candidate Quick Stats -->
                                    <div class="space-y-1 text-[9px]">
                                        @if(!empty($fd['qualification']))
                                            <div
                                                class="bg-slate-50/80 px-2 py-0.5 rounded border border-slate-100/80 flex items-center justify-between">
                                                <span class="text-slate-500 font-medium">{{ __('messages.education_label') }}</span>
                                                <span
                                                    class="font-bold text-slate-800 truncate max-w-[140px]">{{ $fd['qualification'] }}</span>
                                            </div>
                                        @endif

                                        @if(!empty($fd['occupation']))
                                            <div
                                                class="bg-slate-50/80 px-2 py-0.5 rounded border border-slate-100/80 flex items-center justify-between">
                                                <span class="text-slate-500 font-medium">{{ __('messages.job_label') }}</span>
                                                <span
                                                    class="font-bold text-slate-800 truncate max-w-[140px]">{{ $fd['occupation'] }}</span>
                                            </div>
                                        @endif

                                        @if(!empty($fd['birth_date']) || !empty($fd['age']))
                                            <div
                                                class="bg-purple-50/80 px-2 py-0.5 rounded border border-purple-200/60 flex items-center justify-between">
                                                <span
                                                    class="text-purple-800 font-bold">{{ __('messages.age_dob') }}</span>
                                                <span class="font-black text-purple-900 text-[10px]">
                                                    {{ !empty($fd['age']) ? $fd['age'] . ' ' . __('messages.years') : '' }}
                                                    {{ !empty($fd['birth_date']) ? '(' . $fd['birth_date'] . ')' : '' }}
                                                </span>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Actions: View Biodata & Edit Form -->
                                    <div class="flex items-center gap-1.5 pt-1">
                                        <button type="button"
                                            @click.stop="openBiodata({{ json_encode($modalData, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP) }})"
                                            class="flex-1 py-1.5 bg-purple-50 hover:bg-purple-100 active:scale-95 text-purple-800 rounded-lg text-[10px] font-black border border-purple-200 transition-all shadow-2xs cursor-pointer flex items-center justify-center gap-1">
                                            <span>👁️ {{ __('messages.view_biodata') }}</span>
                                        </button>
                                        <a href="{{ route('admin.events.registrations.edit', $reg->id) }}"
                                            class="py-1.5 px-3 bg-amber-50 hover:bg-amber-100 active:scale-95 text-amber-800 rounded-lg text-[10px] font-black border border-amber-200 transition-all shadow-2xs flex items-center justify-center gap-1 shrink-0">
                                            <span>✏️ {{ __('messages.edit') }}</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div
                                class="col-span-full py-10 text-center text-slate-400 font-medium bg-slate-50 rounded-xl border border-slate-100">
                                {{ __('messages.no_registrations_found') }}
                            </div>
                        @endforelse
                    </div>
                </div>
            @endif
        </div>

        <!-- ================= TAB 3: GALLERY ================= -->
        <div x-show="mainTab === 'gallery'" x-transition class="space-y-4">
            <div class="bg-white p-4 rounded-xl border border-slate-100 shadow-sm space-y-4">
                <!-- Gallery Header inside Tab 3 -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
                    <div>
                        <h2 class="text-sm font-black text-slate-900 flex items-center gap-2">
                            <span>📸 {{ __('messages.event_photo_gallery') }}</span>
                            <span
                                class="px-2 py-0.5 rounded-full text-xs font-black bg-primary-50 text-primary-700 border border-primary-100">{{ $galleryCount }}
                                {{ __('messages.photos') }}</span>
                        </h2>
                        <p class="text-[11px] text-slate-400 font-medium">
                            {{ __('messages.gallery_desc') }}
                        </p>
                    </div>

                    <button type="button" @click="showUploadModal = true"
                        class="px-3.5 py-1.5 bg-primary-500 hover:bg-primary-600 text-white font-extrabold text-xs rounded-xl shadow-xs transition-colors flex items-center gap-1.5 self-start sm:self-auto cursor-pointer">
                        <span>{{ __('messages.add_photos') }}</span>
                    </button>
                </div>

                <!-- Gallery Grid -->
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3">
                    @forelse($gallery as $photo)
                        @php $gpUrl = $photo->url; $isGpVideo = $photo->isVideo(); @endphp
                        <div class="bg-white border border-slate-100 rounded-2xl overflow-hidden shadow-xs hover:shadow-md transition-shadow flex flex-col justify-between group">
                            <!-- Clickable Media with Blurred Background or Video Stream -->
                            <div class="aspect-video w-full overflow-hidden bg-slate-950 relative flex items-center justify-center cursor-pointer"
                                 @click="galleryLightboxIndex = {{ $loop->index }}; galleryLightbox = true">
                                @if($isGpVideo)
                                    <video class="w-full h-full object-cover pointer-events-none" preload="metadata" muted playsinline>
                                        <source src="{{ $gpUrl }}">
                                    </video>
                                    <div class="absolute inset-0 flex items-center justify-center pointer-events-none" style="z-index: 2;">
                                        <div class="w-10 h-10 rounded-full bg-slate-900/85 border border-white/30 text-white flex items-center justify-center shadow-md backdrop-blur-xs group-hover:scale-110 transition-transform">
                                            <svg class="w-5 h-5 fill-current ml-0.5 text-primary-400" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                        </div>
                                    </div>
                                    <span class="absolute top-2 left-2 z-10 px-2 py-0.5 rounded-md bg-slate-900/80 text-white text-[9px] font-black uppercase tracking-wider backdrop-blur-xs border border-white/20 flex items-center gap-1">
                                        <svg class="w-2.5 h-2.5 fill-current text-primary-400" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                        Video
                                    </span>
                                @else
                                    <!-- Blurred Backdrop -->
                                    <img src="{{ $gpUrl }}" 
                                         alt="" 
                                         aria-hidden="true"
                                         class="absolute inset-0 w-full h-full pointer-events-none select-none"
                                         style="object-fit: cover; object-position: center; filter: blur(14px) brightness(0.45); transform: scale(1.12); z-index: 0;">

                                    <!-- Main Full Image -->
                                    <img class="relative w-full h-full object-contain transition-transform duration-300 group-hover:scale-105" 
                                         style="z-index: 1;"
                                         src="{{ $gpUrl }}" 
                                         alt="Gallery Image">
                                @endif

                                <!-- Hover Overlay -->
                                <div class="absolute inset-0 bg-black/30 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center" style="z-index: 10;">
                                    <span class="text-white text-[10px] font-bold inline-flex items-center gap-1 bg-slate-900/80 px-2.5 py-1 rounded-full backdrop-blur-xs border border-white/20 shadow-sm">
                                        @if($isGpVideo)
                                            <svg class="w-3 h-3 text-primary-400 fill-current" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                            Play Video
                                        @else
                                            <svg class="w-3 h-3 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7"/></svg>
                                            {{ __('messages.preview') }}
                                        @endif
                                    </span>
                                </div>
                            </div>
                            <div class="p-2.5 border-t border-slate-100 bg-white">
                                <button type="button"
                                    @click="$dispatch('confirm-delete', { action: '{{ route('admin.events.gallery.destroy', $photo->id) }}', message: '{{ __('messages.delete_photo_confirm') }}' })"
                                    class="w-full py-1 bg-rose-50 hover:bg-rose-100 text-rose-600 font-bold text-[9px] rounded-lg transition-colors cursor-pointer">
                                    {{ __('messages.delete_photo') }}
                                </button>
                            </div>
                        </div>
                    @empty
                        <div
                            class="col-span-full text-center py-12 bg-slate-50 rounded-xl border border-dashed border-slate-200">
                            <span class="text-3xl block mb-2">📸</span>
                            <p class="text-xs font-bold text-slate-600">
                                {{ __('messages.no_event_photos_uploaded') }}</p>
                            <button type="button" @click="showUploadModal = true"
                                class="mt-3 px-3.5 py-1.5 bg-primary-500 hover:bg-primary-600 text-white font-bold text-xs rounded-xl shadow-xs transition-colors inline-flex items-center gap-1 cursor-pointer">
                                <span>{{ __('messages.upload_photos') }}</span>
                            </button>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- ================= TAB 4: SPONSORSHIP ================= -->
        <div x-show="mainTab === 'sponsorship'" x-transition class="space-y-3.5">
            <!-- Nested Sub-Tab Navigation Bar -->
            <div class="bg-white p-2 rounded-xl border border-slate-100 shadow-sm flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-2.5">
                <div class="flex items-center gap-2">
                    <!-- Sub-Tab 1: Sponsorship Type -->
                    <button type="button" @click="sponsorshipSubTab = 'types'"
                        :class="sponsorshipSubTab === 'types' ? 'bg-primary-500 text-white shadow-xs font-black' : 'text-slate-600 hover:text-slate-900 font-bold hover:bg-slate-100'"
                        class="px-3 py-1.5 text-xs rounded-lg transition-all flex items-center gap-1.5 whitespace-nowrap cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                        <span>{{ __('messages.sponsorship_type') }}</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] font-black"
                            :class="sponsorshipSubTab === 'types' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700'">{{ $sponsorshipTypesCount }}</span>
                    </button>

                    <!-- Sub-Tab 2: Registered Sponsor -->
                    <button type="button" @click="sponsorshipSubTab = 'sponsors'"
                        :class="sponsorshipSubTab === 'sponsors' ? 'bg-primary-500 text-white shadow-xs font-black' : 'text-slate-600 hover:text-slate-900 font-bold hover:bg-slate-100'"
                        class="px-3 py-1.5 text-xs rounded-lg transition-all flex items-center gap-1.5 whitespace-nowrap cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span>{{ __('messages.registered_sponsor') }}</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] font-black"
                            :class="sponsorshipSubTab === 'sponsors' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700'">{{ $sponsorsCount }}</span>
                    </button>
                </div>

                <!-- Sub-Tab Header Action Buttons -->
                <div class="flex items-center gap-2">
                    <template x-if="sponsorshipSubTab === 'types'">
                        <button type="button" @click="showAddTypeModal = true"
                            class="px-3 py-1.5 bg-primary-500 hover:bg-primary-600 active:scale-95 text-white font-extrabold text-xs rounded-lg shadow-xs transition-all flex items-center gap-1.5 cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                            </svg>
                            <span>{{ __('messages.add_sponsorship_type') }}</span>
                        </button>
                    </template>
                    <template x-if="sponsorshipSubTab === 'sponsors'">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.events.sponsors.export', $event->id) }}"
                                class="px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-extrabold text-xs rounded-lg border border-emerald-200/60 shadow-2xs transition-colors flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                <span>{{ __('messages.export_excel') }}</span>
                            </a>
                            <button type="button" @click="openAddSponsor()"
                                class="px-3 py-1.5 bg-primary-500 hover:bg-primary-600 active:scale-95 text-white font-extrabold text-xs rounded-lg shadow-xs transition-all flex items-center gap-1.5 cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                </svg>
                                <span>{{ __('messages.add_sponsor') }}</span>
                            </button>
                        </div>
                    </template>
                </div>
            </div>

            <!-- ================= SUB-TAB 1: SPONSORSHIP TYPES (EXTRA COMPACT CARD STYLE) ================= -->
            <div x-show="sponsorshipSubTab === 'types'" x-transition class="space-y-3">
                <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-3">
                    <!-- Extra Compact Card Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-2.5">
                        @forelse($sponsorshipTypes as $index => $st)
                            <div class="bg-slate-50/60 hover:bg-white rounded-xl border border-slate-200/80 shadow-2xs hover:shadow-xs transition-all p-2.5 flex flex-col justify-between space-y-2 relative group">
                                <!-- Card Header: Title + Inline Action Buttons -->
                                <div class="flex items-center justify-between gap-1.5">
                                    <div class="min-w-0 flex-1">
                                        <h3 class="text-xs font-black text-slate-900 truncate leading-tight group-hover:text-primary-600 transition-colors" title="{{ $st->title }}">
                                            {{ $st->title }}
                                        </h3>
                                        @if($st->description)
                                            <p class="text-[9.5px] text-slate-400 font-medium line-clamp-1 mt-0.5" title="{{ $st->description }}">
                                                {{ $st->description }}
                                            </p>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-1 shrink-0">
                                        <button type="button" @click="openEditType({{ json_encode($st) }})"
                                            class="p-1 bg-amber-50 hover:bg-amber-100 text-amber-700 rounded-md border border-amber-200/60 transition-colors cursor-pointer"
                                            title="{{ __('messages.edit') }}">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <button type="button"
                                            @click="$dispatch('confirm-delete', { action: '{{ route('admin.events.sponsorship_types.destroy', $st->id) }}', message: '{{ __('messages.delete_confirmation') }}' })"
                                            class="p-1 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded-md border border-rose-200/60 transition-colors cursor-pointer"
                                            title="{{ __('messages.delete') }}">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <!-- Card Body: Amount & Registration Slots -->
                                <div class="bg-white p-2 rounded-lg border border-slate-200/70 shadow-2xs space-y-1">
                                    <div class="flex items-baseline justify-between gap-1">
                                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">{{ __('messages.amount') }}</span>
                                        <span class="text-xs font-black text-emerald-700 font-mono">
                                            ₹ {{ number_format($st->amount, 2) }}
                                        </span>
                                    </div>
                                    <div class="flex items-center justify-between text-[10px] font-bold pt-1 border-t border-slate-100">
                                        <span class="text-slate-500 text-[9.5px]">{{ __('messages.registered') }}:</span>
                                        <div class="flex items-center gap-1">
                                            <span class="font-black text-slate-900">{{ $st->approved_sponsors_count }}</span>
                                            <span class="text-slate-400">/</span>
                                            <span class="text-slate-600">{{ $st->max_sponsors > 0 ? $st->max_sponsors : '∞' }}</span>
                                            @if($st->is_full)
                                                <span class="px-1.5 py-0.2 rounded text-[8.5px] font-black bg-rose-50 text-rose-700 border border-rose-200">{{ __('messages.slots_full') }}</span>
                                            @elseif($st->max_sponsors > 0)
                                                <!-- <span class="px-1.5 py-0.2 rounded text-[8.5px] font-black bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                    {{ $st->available_slots }} {{ __('messages.remaining') }}
                                                </span> -->
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-full py-6 text-center text-slate-400 font-medium bg-slate-50/50 rounded-xl border border-dashed border-slate-200">
                                <div class="space-y-1.5">
                                    <svg class="w-6 h-6 mx-auto text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                    </svg>
                                    <p class="text-xs font-bold text-slate-600">{{ __('messages.no_data_found') }}</p>
                                    <button type="button" @click="showAddTypeModal = true"
                                        class="mt-1 px-3 py-1.5 bg-primary-500 hover:bg-primary-600 text-white font-bold text-xs rounded-lg shadow-xs transition-colors inline-flex items-center gap-1 cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                        </svg>
                                        <span>{{ __('messages.add_sponsorship_type') }}</span>
                                    </button>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- ================= SUB-TAB 2: REGISTERED SPONSORS (COMPACT WITH ICONS) ================= -->
            <div x-show="sponsorshipSubTab === 'sponsors'" x-transition class="space-y-3.5">
                <!-- Summary Stats Cards -->
                <!-- <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                    <div class="bg-white p-3 rounded-xl border border-slate-100 shadow-2xs">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-wider block">{{ __('messages.registered_sponsors') }}</span>
                        <div class="text-base font-black text-slate-900 mt-0.5">{{ $sponsorsCount }}</div>
                    </div>
                    <div class="bg-white p-3 rounded-xl border border-slate-100 shadow-2xs">
                        <span class="text-[10px] font-black text-emerald-600 uppercase tracking-wider block">{{ __('messages.approved') }}</span>
                        <div class="text-base font-black text-emerald-700 mt-0.5">{{ $approvedSponsorsCount }}</div>
                    </div>
                    <div class="bg-white p-3 rounded-xl border border-slate-100 shadow-2xs">
                        <span class="text-[10px] font-black text-amber-600 uppercase tracking-wider block">{{ __('messages.pending') }}</span>
                        <div class="text-base font-black text-amber-700 mt-0.5">{{ $pendingSponsorsCount }}</div>
                    </div>
                    <div class="bg-white p-3 rounded-xl border border-slate-100 shadow-2xs">
                        <span class="text-[10px] font-black text-blue-600 uppercase tracking-wider block">{{ __('messages.sponsors_total_fund') }}</span>
                        <div class="text-base font-black text-blue-700 mt-0.5">₹ {{ number_format($totalSponsorshipAmount, 2) }}</div>
                    </div>
                </div> -->

                <!-- Registered Sponsors Card Grid -->
                <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-3">
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-4 gap-3">
                        @forelse($sponsors as $index => $s)
                            @php
                                $sJson = json_encode([
                                    'id' => $s->id,
                                    'name' => $s->name,
                                    'contact_person' => $s->contact_person,
                                    'mobile' => $s->mobile,
                                    'email' => $s->email,
                                    'sponsorship_type_id' => $s->sponsorship_type_id,
                                    'type_title' => $s->sponsorshipType?->title ?? __('messages.general_sponsor'),
                                    'amount' => (float)$s->amount,
                                    'logo_url' => $s->logo_path ? (str_starts_with($s->logo_path, 'http') ? $s->logo_path : asset('storage/' . $s->logo_path)) : null,
                                    'city' => $s->city,
                                    'address' => $s->address,
                                    'notes' => $s->notes,
                                    'payment_status' => $s->payment_status,
                                    'transaction_nos' => $s->transactions->pluck('transaction_no')->implode(', '),
                                    'status' => $s->status,
                                    'created_at' => $s->created_at ? $s->created_at->format('d-M-Y h:i A') : '-',
                                ], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP);
                            @endphp
                            <div class="bg-slate-50/60 hover:bg-white rounded-xl border border-slate-200/80 shadow-2xs hover:shadow-xs transition-all p-3 flex flex-col justify-between space-y-2.5 relative group">
                                
                                <!-- Top Row: Logo/Avatar + Full Name + Status Badge on Top Right -->
                                <div class="space-y-2">
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                            @if($s->logo_path)
                                                <img src="{{ str_starts_with($s->logo_path, 'http') ? $s->logo_path : asset('storage/' . $s->logo_path) }}"
                                                     alt="{{ $s->name }}"
                                                     class="w-8 h-8 rounded-lg object-contain bg-white border border-slate-200 p-0.5 shrink-0 shadow-2xs">
                                            @else
                                                <div class="w-8 h-8 rounded-lg bg-primary-50 text-primary-700 font-black text-xs flex items-center justify-center border border-primary-100 shrink-0 shadow-2xs">
                                                    {{ strtoupper(substr($s->name, 0, 1)) }}
                                                </div>
                                            @endif
                                            <div class="min-w-0 flex-1">
                                                <h3 class="text-xs font-black text-slate-900 leading-snug group-hover:text-primary-600 transition-colors break-words" title="{{ $s->name }}">
                                                    {{ $s->name }}
                                                </h3>
                                                @if($s->city)
                                                    <p class="text-[10px] font-bold text-slate-400 flex items-center gap-0.5 mt-0.5" title="{{ $s->city }}">
                                                        <svg class="w-2.5 h-2.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                        <span>{{ $s->city }}</span>
                                                    </p>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Status Badge (Top Right) -->
                                        <div class="shrink-0">
                                            @if($s->status === 'approved')
                                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full text-[9px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/70 shadow-2xs leading-none">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                                                    <span>{{ __('messages.approved') }}</span>
                                                </span>
                                            @elseif($s->status === 'rejected')
                                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full text-[9px] font-semibold bg-rose-50 text-rose-700 border border-rose-200/70 shadow-2xs leading-none">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500 shrink-0"></span>
                                                    <span>{{ __('messages.rejected') }}</span>
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full text-[9px] font-semibold bg-amber-50 text-amber-700 border border-amber-200/70 shadow-2xs leading-none">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 shrink-0"></span>
                                                    <span>{{ __('messages.pending') }}</span>
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Package & Amount Info Box -->
                                    <div class="bg-white p-2.5 rounded-lg border border-slate-200/70 shadow-2xs space-y-1.5">
                                        <!-- Package Title & Amount -->
                                        <div class="flex items-center justify-between gap-1.5 pb-1.5 border-b border-slate-100">
                                            <span class="inline-flex items-center gap-1 font-bold text-slate-700 bg-slate-100 px-1.5 py-0.5 rounded text-[9.5px] truncate max-w-[120px]" title="{{ $s->sponsorshipType?->title ?? __('messages.general_sponsor') }}">
                                                <svg class="w-2.5 h-2.5 text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                                <span class="truncate">{{ $s->sponsorshipType?->title ?? __('messages.general_sponsor') }}</span>
                                            </span>
                                            
                                            <span class="text-xs font-black text-emerald-700 font-mono">
                                                ₹ {{ number_format($s->amount, 2) }}
                                            </span>
                                        </div>

                                        <!-- Contact Phone -->
                                        <div class="space-y-1 text-[10px]">
                                            <div class="flex items-center justify-between">
                                                <span class="text-slate-400 font-bold text-[9px]">{{ __('messages.phone') }}:</span>
                                                <a href="tel:{{ $s->mobile }}" class="text-blue-700 hover:text-blue-800 font-bold flex items-center gap-1">
                                                    <svg class="w-2.5 h-2.5 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                                    <span>{{ $s->mobile }}</span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Card Footer: Date & Actions -->
                                <div class="flex items-center justify-between pt-1 border-t border-slate-200/60">
                                    <span class="text-[9px] font-bold text-slate-400">{{ $s->created_at ? $s->created_at->format('d M Y') : '-' }}</span>
                                    <div class="flex items-center gap-1">
                                        @if($s->status === 'pending')
                                            <form method="POST" action="{{ route('admin.events.sponsors.approve', $s->id) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="p-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded-md border border-emerald-200 transition-colors shadow-2xs cursor-pointer" title="{{ __('messages.approve') }}">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.events.sponsors.reject', $s->id) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="p-1 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded-md border border-rose-200 transition-colors shadow-2xs cursor-pointer" title="{{ __('messages.reject') }}">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                            </form>
                                        @endif

                                        <!-- Receipt PDF Download Button -->
                                        <a href="{{ route('admin.receipts.sponsorship', $s->id) }}"
                                            class="p-1 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-md border border-blue-200 transition-colors shadow-2xs cursor-pointer"
                                            title="{{ __('messages.download_receipt') }}">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </a>

                                        <!-- View Details Button -->
                                        <button type="button" @click="openViewSponsor({{ $sJson }})"
                                            class="p-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-md border border-slate-200 transition-colors shadow-2xs cursor-pointer"
                                            title="{{ __('messages.view_details') }}">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </button>

                                        <!-- Edit Button -->
                                        <button type="button" @click="openEditSponsor({{ $sJson }})"
                                            class="p-1 bg-amber-50 hover:bg-amber-100 text-amber-700 rounded-md border border-amber-200/60 transition-colors shadow-2xs cursor-pointer"
                                            title="{{ __('messages.edit') }}">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>

                                        <!-- Delete Button -->
                                        <button type="button"
                                            @click="$dispatch('confirm-delete', { action: '{{ route('admin.events.sponsors.destroy', $s->id) }}', message: '{{ __('messages.delete_sponsor_confirm') }}' })"
                                            class="p-1 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded-md border border-rose-200/60 transition-colors shadow-2xs cursor-pointer"
                                            title="{{ __('messages.delete') }}">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-full py-8 text-center text-slate-400 font-medium bg-slate-50/50 rounded-xl border border-dashed border-slate-200">
                                <div class="space-y-1.5">
                                    <svg class="w-7 h-7 mx-auto text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    <p class="text-xs font-bold text-slate-600">{{ __('messages.no_sponsors_registered') }}</p>
                                    <button type="button" @click="openAddSponsor()"
                                        class="mt-1 px-3 py-1.5 bg-primary-500 hover:bg-primary-600 text-white font-bold text-xs rounded-lg shadow-xs transition-colors inline-flex items-center gap-1 cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                        </svg>
                                        <span>{{ __('messages.add_sponsor') }}</span>
                                    </button>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Upload Gallery Photos Modal -->
        <template x-teleport="body">
            <div x-show="showUploadModal"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/40 backdrop-blur-sm" x-transition
                x-cloak>
                <div class="bg-white rounded-xl max-w-md w-full p-4 border border-slate-100 shadow-xl space-y-3.5 max-h-[90vh] overflow-y-auto"
                    @click.away="showUploadModal = false">

                    <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                        <h3 class="text-xs font-extrabold text-slate-950">
                            {{ __('messages.upload_photos') }}</h3>
                        <button type="button" @click="showUploadModal = false"
                            class="text-slate-400 hover:text-slate-600 text-sm font-black cursor-pointer">
                            &times;
                        </button>
                    </div>

                    <form method="POST" action="{{ route('admin.events.gallery.upload', $event->id) }}"
                        enctype="multipart/form-data" class="space-y-3.5">
                        @csrf
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold text-slate-600 uppercase tracking-wider block">
                                {{ __('messages.select_images_or_zip') }}
                            </label>
                            <input type="file" name="images[]" multiple required accept=".jpg,.jpeg,.png,.webp,.gif,.zip"
                                @change="$el.name = ($el.files.length === 1 && $el.files[0].name.toLowerCase().endsWith('.zip')) ? 'image' : 'images[]'"
                                class="text-[11px] block w-full text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[10px] file:font-semibold file:bg-primary-50 file:text-primary-700 border border-slate-200 rounded-lg p-1 bg-slate-50">
                            <p class="text-[10px] text-slate-400 font-medium">
                                {{ __('messages.zip_upload_hint') }}
                            </p>
                        </div>

                        <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                            <button type="button" @click="showUploadModal = false"
                                class="px-3.5 py-1.5 border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs rounded-xl transition-colors cursor-pointer">
                                {{ __('messages.cancel') ?? 'Cancel' }}
                            </button>
                            <button type="submit"
                                class="px-4 py-1.5 bg-primary-500 hover:bg-primary-600 text-white font-bold text-xs rounded-xl shadow-xs transition-colors cursor-pointer">
                                {{ __('messages.upload_photos') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>

        <!-- REGISTRATION DETAILS POPUP MODAL (BILINGUAL BIODATA BOOKLET FORMAT) -->
        <template x-teleport="body">
            <div x-show="showDetailsModal"
                class="fixed inset-0 z-50 flex items-center justify-center p-2 sm:p-4 bg-slate-900/75 backdrop-blur-sm"
                x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" x-cloak>
                <div @click.away="showDetailsModal = false"
                    class="bg-white rounded-2xl border border-slate-300 shadow-2xl max-w-2xl w-full max-h-[92vh] flex flex-col overflow-hidden relative">

                    <!-- Modal Top Header Bar -->
                    <div class="px-4 py-2.5 bg-slate-900 text-white flex items-center justify-between shrink-0">
                        <div class="flex items-center gap-2">
                            <div>
                                <h3 class="text-xs font-extrabold flex items-center gap-2">
                                    <span
                                        x-text="(('{{ $event->event_type ?? 'normal' }}' === 'yuva_melo' || selectedRegistration.form_data?.surname) ? (previewLang === 'en' ? 'Candidate Biodata Preview' : 'ઉમેદવાર બાયોડેટા પ્રીવ્યૂ (Candidate Biodata)') : 'Submitted Registration Details')"></span>
                                </h3>
                                <p class="text-[10px] text-slate-400 font-medium"
                                    x-text="(previewLang === 'en' ? 'Submitted on: ' : 'સબમિટ તારીખ: ') + (selectedRegistration.date || '')">
                                </p>
                                <p class="text-[10px] text-slate-500 font-mono font-bold" x-show="selectedRegistration.transaction_nos"
                                    x-text="(previewLang === 'en' ? 'Transaction ID: ' : 'વ્યવહાર ID: ') + selectedRegistration.transaction_nos">
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <!-- Language Toggle Switch (GU / EN) -->
                            <div
                                class="inline-flex rounded-lg border border-slate-700 p-0.5 bg-slate-800 text-[11px] font-bold">
                                <button type="button" @click="previewLang = 'gu'"
                                    :class="previewLang === 'gu' ? 'bg-primary-600 text-white shadow-xs' : 'text-slate-300 hover:text-white'"
                                    class="px-2 py-0.5 rounded-md transition-colors cursor-pointer">
                                    ગુજરાતી
                                </button>
                                <button type="button" @click="previewLang = 'en'"
                                    :class="previewLang === 'en' ? 'bg-primary-600 text-white shadow-xs' : 'text-slate-300 hover:text-white'"
                                    class="px-2 py-0.5 rounded-md transition-colors cursor-pointer">
                                    English
                                </button>
                            </div>

                            <button type="button" @click="printBiodata()"
                                class="px-2.5 py-1 bg-white/10 hover:bg-white/20 text-white rounded-lg text-xs font-bold transition-colors flex items-center gap-1 cursor-pointer">
                                <span>🖨️ <span x-text="previewLang === 'en' ? 'Print' : 'પ્રિન્ટ'"></span></span>
                            </button>
                            <button type="button" @click="showDetailsModal = false"
                                class="w-6 h-6 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-colors text-xs cursor-pointer">
                                ✕
                            </button>
                        </div>
                    </div>

                    <!-- Modal Body (Compact Scrollable with Traditional Gujarati Biodata Layout) -->
                    <div class="p-3 sm:p-4 overflow-y-auto text-xs bg-slate-100/70">

                        <!-- YUVA MELO TRADITIONAL BIODATA BOOKLET VIEW -->
                        <template
                            x-if="'{{ $event->event_type ?? 'normal' }}' === 'yuva_melo' || selectedRegistration.form_data?.surname">
                            <div id="printableBiodata"
                                class="bg-white p-3 border-2 border-slate-900 shadow-sm font-sans text-slate-900 space-y-2.5 max-w-[620px] mx-auto print:border-none print:p-0 print:shadow-none print:max-w-full">

                                <!-- Top Box: Candidate Photo + Personal Header Summary -->
                                <div class="border-2 border-slate-900 p-2.5 bg-white">
                                    <div class="flex gap-3 items-start">
                                        <!-- Left Photo Box (Fixed Passport Size) -->
                                        <div style="width: 110px; height: 140px; min-width: 110px; max-width: 110px; min-height: 140px; max-height: 140px; overflow: hidden; flex-shrink: 0;"
                                            class="shrink-0 border border-slate-900 rounded-xs bg-slate-50 relative flex items-center justify-center shadow-2xs">
                                            <template x-if="getPhotoUrl(selectedRegistration.form_data)">
                                                <img :src="getPhotoUrl(selectedRegistration.form_data)"
                                                    style="width: 100%; height: 100%; object-fit: contain; display: block;"
                                                    class="w-full h-full object-contain bg-white">
                                            </template>
                                            <template x-if="!getPhotoUrl(selectedRegistration.form_data)">
                                                <div class="text-center p-1 text-slate-400">
                                                    <svg class="w-8 h-8 mx-auto text-slate-300 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                                    <span class="text-[9px] font-bold"
                                                        x-text="previewLang === 'en' ? 'No Photo' : 'ફોટો નથી'"></span>
                                                </div>
                                            </template>
                                        </div>

                                        <!-- Right Personal & Contact Info -->
                                        <div class="flex-1 min-w-0 space-y-1 text-xs">
                                            <!-- Candidate Name (Red Bold) -->
                                            <h2 class="text-sm sm:text-base font-black text-rose-600 leading-tight"
                                                x-text="((selectedRegistration.form_data?.first_name || '') + ' ' + (selectedRegistration.form_data?.surname || '')).trim() || selectedRegistration.form_data?.full_name || '-'">
                                            </h2>

                                            <!-- Father / Grandfather Full Name -->
                                            <div class="font-bold text-slate-800 text-[11px] leading-tight">
                                                <span
                                                    x-text="((selectedRegistration.form_data?.father_name || '') + ' ' + (selectedRegistration.form_data?.grandfather_name || '') + ' ' + (selectedRegistration.form_data?.surname || '')).trim() || '-'"></span>
                                            </div>

                                            <!-- Full Address -->
                                            <div class="text-[10.5px] text-slate-700 leading-tight">
                                                <span
                                                    x-text="[selectedRegistration.form_data?.address, selectedRegistration.form_data?.district, selectedRegistration.form_data?.state].filter(Boolean).join(', ') || '-'"></span>
                                            </div>

                                            <!-- Mobile Numbers -->
                                            <div class="text-[10.5px] font-bold flex flex-wrap gap-x-3 gap-y-0.5 pt-0.5">
                                                <div>
                                                    <span class="text-slate-800"
                                                        x-text="previewLang === 'en' ? 'Cand. Mob.:' : 'ઉ. મો.:'"></span>
                                                    <span class="text-blue-700 font-bold ml-0.5"
                                                        x-text="selectedRegistration.form_data?.mobile_no || '-'"></span>
                                                </div>
                                                <div>
                                                    <span class="text-slate-800"
                                                        x-text="previewLang === 'en' ? 'Guard. Mob.:' : 'વા. મો.:'"></span>
                                                    <span class="text-blue-700 font-bold ml-0.5"
                                                        x-text="selectedRegistration.form_data?.father_mobile || selectedRegistration.form_data?.whatsapp || '-'"></span>
                                                </div>
                                            </div>

                                            <!-- Mini Stats Table (DOB, Age, Height, Weight) -->
                                            <table
                                                class="w-full border-collapse border border-slate-900 text-[10px] text-center mt-1 table-fixed">
                                                <colgroup>
                                                    <col style="width: 25%;">
                                                    <col style="width: 25%;">
                                                    <col style="width: 25%;">
                                                    <col style="width: 25%;">
                                                </colgroup>
                                                <tbody>
                                                    <tr class="border-b border-slate-900">
                                                        <td class="border-r border-slate-900 font-bold py-0.5 px-1 bg-slate-50 text-slate-800"
                                                            x-text="previewLang === 'en' ? 'Birth Date' : 'જન્મ તારીખ'">
                                                        </td>
                                                        <td class="border-r border-slate-900 font-bold py-0.5 px-1 text-blue-700 truncate"
                                                            x-text="selectedRegistration.form_data?.birth_date || '-'"></td>
                                                        <td class="border-r border-slate-900 font-bold py-0.5 px-1 bg-slate-50 text-slate-800"
                                                            x-text="previewLang === 'en' ? 'Age' : 'ઉંમર વર્ષ'"></td>
                                                        <td class="font-bold py-0.5 px-1 text-blue-700 truncate"
                                                            x-text="(selectedRegistration.form_data?.age ? selectedRegistration.form_data?.age + (previewLang === 'en' ? ' Yrs' : ' વર્ષ') : '-')">
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="border-r border-slate-900 font-bold py-0.5 px-1 bg-slate-50 text-slate-800"
                                                            x-text="previewLang === 'en' ? 'Height' : 'ઊંચાઈ'"></td>
                                                        <td class="border-r border-slate-900 font-bold py-0.5 px-1 text-blue-700 truncate"
                                                            x-text="selectedRegistration.form_data?.height || '-'"></td>
                                                        <td class="border-r border-slate-900 font-bold py-0.5 px-1 bg-slate-50 text-slate-800"
                                                            x-text="previewLang === 'en' ? 'Weight' : 'વજન'"></td>
                                                        <td class="font-bold py-0.5 px-1 text-blue-700 truncate"
                                                            x-text="selectedRegistration.form_data?.weight || '-'"></td>
                                                    </tr>
                                                </tbody>
                                            </table>

                                            <!-- Native Place -->
                                            <div class="text-[10.5px] font-bold pt-0.5">
                                                <span class="text-slate-800"
                                                    x-text="previewLang === 'en' ? 'Native Place, District: ' : 'મૂળ વતન, ગામ, જિલ્લો: '"></span>
                                                <span class="text-blue-700"
                                                    x-text="selectedRegistration.form_data?.native_place ? selectedRegistration.form_data?.native_place + (selectedRegistration.form_data?.district ? ', ' + selectedRegistration.form_data?.district : '') : (selectedRegistration.form_data?.district || '-')"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Main Structured Table (Labels Left, Blue Values Right) -->
                                <table
                                    class="w-full border-collapse border-2 border-slate-900 text-[10.5px] sm:text-[11px] text-left bg-white table-fixed">
                                    <colgroup>
                                        <col style="width: 34%;">
                                        <col style="width: 16%;">
                                        <col style="width: 34%;">
                                        <col style="width: 16%;">
                                    </colgroup>
                                    <tbody>
                                        <!-- Row 1: Qualification -->
                                        <tr class="border-b border-slate-900">
                                            <td class="border-r border-slate-900 py-1 px-2 font-bold text-slate-800 bg-slate-50/80"
                                                x-text="previewLang === 'en' ? 'Candidate Qualification' : 'ઉમેદવારની શૈક્ષણિક લાયકાત'">
                                            </td>
                                            <td colspan="3" class="py-1 px-2 font-bold text-blue-700 break-words"
                                                x-text="selectedRegistration.form_data?.qualification || '-'"></td>
                                        </tr>

                                        <!-- Row 2: Occupation -->
                                        <tr class="border-b border-slate-900">
                                            <td class="border-r border-slate-900 py-1 px-2 font-bold text-slate-800 bg-slate-50/80"
                                                x-text="previewLang === 'en' ? 'Candidate Occupation' : 'ઉમેદવારનો વ્યવસાય'">
                                            </td>
                                            <td colspan="3" class="py-1 px-2 font-bold text-blue-700 break-words"
                                                x-text="selectedRegistration.form_data?.occupation || '-'"></td>
                                        </tr>

                                        <!-- Row 3: Occupation Address -->
                                        <tr class="border-b border-slate-900">
                                            <td class="border-r border-slate-900 py-1 px-2 font-bold text-slate-800 bg-slate-50/80"
                                                x-text="previewLang === 'en' ? 'Occupation Address' : 'ઉમેદવારના વ્યવસાય નું સરનામું'">
                                            </td>
                                            <td colspan="3" class="py-1 px-2 font-bold text-blue-700 break-words"
                                                x-text="selectedRegistration.form_data?.occupation_address || '-'"></td>
                                        </tr>

                                        <!-- Row 4: Monthly Income -->
                                        <tr class="border-b border-slate-900">
                                            <td class="border-r border-slate-900 py-1 px-2 font-bold text-slate-800 bg-slate-50/80"
                                                x-text="previewLang === 'en' ? 'Monthly Income' : 'ઉમેદવારની માસિક આવક'">
                                            </td>
                                            <td colspan="3" class="py-1 px-2 font-bold text-blue-700 break-words"
                                                x-text="selectedRegistration.form_data?.monthly_income ? '₹ ' + selectedRegistration.form_data?.monthly_income : '-'">
                                            </td>
                                        </tr>

                                        <!-- Row 5: Sibling - Elder Brothers -->
                                        <tr class="border-b border-slate-900">
                                            <td class="border-r border-slate-900 py-1 px-2 font-bold text-slate-800 bg-slate-50/80"
                                                x-text="previewLang === 'en' ? 'Elder Brothers Count' : 'ઉમેદવારના મોટાભાઈની સંખ્યા'">
                                            </td>
                                            <td class="border-r border-slate-900 py-1 px-1.5 font-bold text-blue-700 text-center break-words"
                                                x-text="getSiblingStat(selectedRegistration.form_data, 'Elder Brother', false)">
                                            </td>
                                            <td class="border-r border-slate-900 py-1 px-2 font-bold text-slate-800 bg-slate-50/80 text-[10px]"
                                                x-text="previewLang === 'en' ? 'Married Elder Brothers' : 'પરણેલા મોટાભાઈની સંખ્યા'">
                                            </td>
                                            <td class="py-1 px-1.5 font-bold text-blue-700 text-center break-words"
                                                x-text="getSiblingStat(selectedRegistration.form_data, 'Elder Brother', true)">
                                            </td>
                                        </tr>

                                        <!-- Row 6: Sibling - Younger Brothers -->
                                        <tr class="border-b border-slate-900">
                                            <td class="border-r border-slate-900 py-1 px-2 font-bold text-slate-800 bg-slate-50/80"
                                                x-text="previewLang === 'en' ? 'Younger Brothers Count' : 'ઉમેદવારના નાનાભાઈની સંખ્યા'">
                                            </td>
                                            <td class="border-r border-slate-900 py-1 px-1.5 font-bold text-blue-700 text-center break-words"
                                                x-text="getSiblingStat(selectedRegistration.form_data, 'Younger Brother', false)">
                                            </td>
                                            <td class="border-r border-slate-900 py-1 px-2 font-bold text-slate-800 bg-slate-50/80 text-[10px]"
                                                x-text="previewLang === 'en' ? 'Married Younger Brothers' : 'પરણેલા નાનાભાઈની સંખ્યા'">
                                            </td>
                                            <td class="py-1 px-1.5 font-bold text-blue-700 text-center break-words"
                                                x-text="getSiblingStat(selectedRegistration.form_data, 'Younger Brother', true)">
                                            </td>
                                        </tr>

                                        <!-- Row 7: Sibling - Elder Sisters -->
                                        <tr class="border-b border-slate-900">
                                            <td class="border-r border-slate-900 py-1 px-2 font-bold text-slate-800 bg-slate-50/80"
                                                x-text="previewLang === 'en' ? 'Elder Sisters Count' : 'ઉમેદવારના મોટા બહેનો ની સંખ્યા'">
                                            </td>
                                            <td class="border-r border-slate-900 py-1 px-1.5 font-bold text-blue-700 text-center break-words"
                                                x-text="getSiblingStat(selectedRegistration.form_data, 'Elder Sister', false)">
                                            </td>
                                            <td class="border-r border-slate-900 py-1 px-2 font-bold text-slate-800 bg-slate-50/80 text-[10px]"
                                                x-text="previewLang === 'en' ? 'Married Elder Sisters' : 'પરણેલા મોટા બહેનો ની સંખ્યા'">
                                            </td>
                                            <td class="py-1 px-1.5 font-bold text-blue-700 text-center break-words"
                                                x-text="getSiblingStat(selectedRegistration.form_data, 'Elder Sister', true)">
                                            </td>
                                        </tr>

                                        <!-- Row 8: Sibling - Younger Sisters -->
                                        <tr class="border-b border-slate-900">
                                            <td class="border-r border-slate-900 py-1 px-2 font-bold text-slate-800 bg-slate-50/80"
                                                x-text="previewLang === 'en' ? 'Younger Sisters Count' : 'ઉમેદવારના નાના બહેનો ની સંખ્યા'">
                                            </td>
                                            <td class="border-r border-slate-900 py-1 px-1.5 font-bold text-blue-700 text-center break-words"
                                                x-text="getSiblingStat(selectedRegistration.form_data, 'Younger Sister', false)">
                                            </td>
                                            <td class="border-r border-slate-900 py-1 px-2 font-bold text-slate-800 bg-slate-50/80 text-[10px]"
                                                x-text="previewLang === 'en' ? 'Married Younger Sisters' : 'પરણેલા નાના બહેનો ની સંખ્યા'">
                                            </td>
                                            <td class="py-1 px-1.5 font-bold text-blue-700 text-center break-words"
                                                x-text="getSiblingStat(selectedRegistration.form_data, 'Younger Sister', true)">
                                            </td>
                                        </tr>

                                        <!-- Row 9: Father's Occupation -->
                                        <tr class="border-b border-slate-900">
                                            <td class="border-r border-slate-900 py-1 px-2 font-bold text-slate-800 bg-slate-50/80"
                                                x-text="previewLang === 'en' ? 'Father Occupation' : 'ઉમેદવારના પિતાનો વ્યવસાય'">
                                            </td>
                                            <td colspan="3" class="py-1 px-2 font-bold text-blue-700 break-words"
                                                x-text="selectedRegistration.form_data?.father_occupation || '-'"></td>
                                        </tr>

                                        <!-- Row 10: Father's Occupation Address -->
                                        <tr class="border-b border-slate-900">
                                            <td class="border-r border-slate-900 py-1 px-2 font-bold text-slate-800 bg-slate-50/80"
                                                x-text="previewLang === 'en' ? 'Father Occupation Address' : 'ઉમેદવારના પિતાના વ્યવસાયનું સરનામું'">
                                            </td>
                                            <td colspan="3" class="py-1 px-2 font-bold text-blue-700 break-words"
                                                x-text="selectedRegistration.form_data?.father_occupation_address || '-'">
                                            </td>
                                        </tr>

                                        <!-- Row 11: Mother's Name -->
                                        <tr class="border-b border-slate-900">
                                            <td class="border-r border-slate-900 py-1 px-2 font-bold text-slate-800 bg-slate-50/80"
                                                x-text="previewLang === 'en' ? 'Mother Name' : 'ઉમેદવારના માતાનું નામ'">
                                            </td>
                                            <td colspan="3" class="py-1 px-2 font-bold text-blue-700 break-words"
                                                x-text="selectedRegistration.form_data?.mother_name || '-'"></td>
                                        </tr>

                                        <!-- Row 12: Maternal Grandfather Address -->
                                        <tr class="border-b border-slate-900">
                                            <td class="border-r border-slate-900 py-1 px-2 font-bold text-slate-800 bg-slate-50/80"
                                                x-text="previewLang === 'en' ? 'Maternal Address' : 'ઉમેદવારના મોસાળ નું સરનામું'">
                                            </td>
                                            <td colspan="3" class="py-1 px-2 font-bold text-blue-700 break-words"
                                                x-text="selectedRegistration.form_data?.maternal_grandfather_address || '-'">
                                            </td>
                                        </tr>

                                        <!-- Row 13: Maternal Elder Name -->
                                        <tr class="border-b border-slate-900">
                                            <td class="border-r border-slate-900 py-1 px-2 font-bold text-slate-800 bg-slate-50/80"
                                                x-text="previewLang === 'en' ? 'Maternal Uncle / Grandfather' : 'મોસાળ ના વડીલનું નામ'">
                                            </td>
                                            <td colspan="3" class="py-1 px-2 font-bold text-blue-700 break-words"
                                                x-text="[selectedRegistration.form_data?.maternal_uncle_name, selectedRegistration.form_data?.maternal_grandfather_name].filter(Boolean).join(' / ') || '-'">
                                            </td>
                                        </tr>

                                        <!-- Row 14: Maternal Elder Occupation -->
                                        <tr class="border-b border-slate-900">
                                            <td class="border-r border-slate-900 py-1 px-2 font-bold text-slate-800 bg-slate-50/80"
                                                x-text="previewLang === 'en' ? 'Maternal Uncle / Grandfather Occupation' : 'મોસાળ ના વડીલ નો વ્યવસાય'">
                                            </td>
                                            <td colspan="3" class="py-1 px-2 font-bold text-blue-700 break-words"
                                                x-text="selectedRegistration.form_data?.maternal_grandfather_occupation || '-'">
                                            </td>
                                        </tr>

                                        <!-- Row 15: Physical Disability -->
                                        <tr class="border-b border-slate-900">
                                            <td class="border-r border-slate-900 py-1 px-2 font-bold text-slate-800 bg-slate-50/80"
                                                x-text="previewLang === 'en' ? 'Physical Disability' : 'ઉમેદવારની શારીરિક ખોડ-ખાંપણ'">
                                            </td>
                                            <td class="border-r border-slate-900 py-1 px-1.5 font-bold text-blue-700 text-center break-words"
                                                x-text="selectedRegistration.form_data?.physical_disability || (previewLang === 'en' ? 'None' : 'નથી')">
                                            </td>
                                            <td class="border-r border-slate-900 py-1 px-2 font-bold text-slate-800 bg-slate-50/80 text-[10px]"
                                                x-text="previewLang === 'en' ? 'Duration' : 'કેટલા સમયથી'"></td>
                                            <td class="py-1 px-1.5 font-bold text-blue-700 text-center break-words"
                                                x-text="selectedRegistration.form_data?.disability_duration || (previewLang === 'en' ? 'None' : 'નથી')">
                                            </td>
                                        </tr>

                                        <!-- Row 16: Divorce / Second Marriage -->
                                        <tr class="border-b border-slate-900">
                                            <td class="border-r border-slate-900 py-1 px-2 font-bold text-slate-800 bg-slate-50/80"
                                                x-text="previewLang === 'en' ? 'Divorce / Other Details' : 'છૂટા-છેડા, બીજા લગ્ન અન્ય માહિતી'">
                                            </td>
                                            <td colspan="3" class="py-1 px-2 font-bold text-blue-700 break-words"
                                                x-text="(selectedRegistration.form_data?.divorce === 'Yes' ? (previewLang === 'en' ? 'Yes (Divorced)' : 'હા (Yes)') : (previewLang === 'en' ? 'None' : 'નથી')) + (selectedRegistration.form_data?.other_info ? ' - ' + selectedRegistration.form_data?.other_info : '')">
                                            </td>
                                        </tr>

                                        <!-- Row 17: Special Info -->
                                        <tr>
                                            <td class="border-r border-slate-900 py-1 px-2 font-bold text-slate-800 bg-slate-50/80"
                                                x-text="previewLang === 'en' ? 'Special Information' : 'વિશેષ માહિતી'"></td>
                                            <td colspan="3" class="py-1 px-2 font-bold text-blue-700 break-words"
                                                x-text="selectedRegistration.form_data?.special_info || '-'"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </template>

                        <!-- STANDARD / INAM VITARAN EVENT DETAILS VIEW -->
                        <template
                            x-if="'{{ $event->event_type ?? 'normal' }}' !== 'yuva_melo' && !selectedRegistration.form_data?.surname">
                            <div class="space-y-2.5">
                                <!-- Uploaded Documents & Photos Compact Strip -->
                                <template
                                    x-if="Object.keys(selectedRegistration.form_data || {}).some(k => k.endsWith('_url'))">
                                    <div class="space-y-1">
                                        <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Uploaded
                                            Documents & Photos</h4>
                                        <div
                                            class="flex flex-wrap items-center gap-2 bg-slate-50 p-2 rounded-xl border border-slate-100">
                                            <template x-for="(val, key) in (selectedRegistration.form_data || {})"
                                                :key="key">
                                                <template x-if="key.endsWith('_url') && val">
                                                    <div
                                                        class="flex items-center gap-2 bg-white px-2 py-1 rounded-lg border border-slate-200 shadow-2xs">
                                                        <a :href="val" target="_blank"
                                                            class="block w-8 h-8 shrink-0 overflow-hidden rounded bg-slate-100 border border-slate-200">
                                                            <img :src="val" class="w-full h-full object-cover">
                                                        </a>
                                                        <div class="min-w-0">
                                                            <span
                                                                class="text-[9px] font-bold text-slate-700 uppercase block truncate max-w-[100px]"
                                                                x-text="key.replace('_url', '').replace(/_/g, ' ')"></span>
                                                            <a :href="val" target="_blank"
                                                                class="text-[9px] font-bold text-primary-600 hover:underline">View
                                                                File ↗</a>
                                                        </div>
                                                    </div>
                                                </template>
                                            </template>
                                        </div>
                                    </div>
                                </template>

                                <!-- Complete Form Data Grid -->
                                <div class="space-y-1">
                                    <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Submitted
                                        Form Fields</h4>
                                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-1.5">
                                        <template x-for="(val, key) in (selectedRegistration.form_data || {})" :key="key">
                                            <template x-if="!key.endsWith('_url') && key !== 'submission_date'">
                                                <div
                                                    class="bg-white px-2 py-1 rounded-lg border border-slate-200 hover:bg-slate-50 transition-colors shadow-2xs">
                                                    <span
                                                        class="text-[8px] font-black text-slate-400 uppercase tracking-wider block truncate"
                                                        x-text="key.replace(/_/g, ' ')"></span>
                                                    <span
                                                        class="font-bold text-slate-900 text-[10.5px] block break-words leading-tight mt-0.5"
                                                        x-text="val || '-'"></span>
                                                </div>
                                            </template>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </template>

                    </div>

                    <!-- Modal Footer -->
                    <div class="px-4 py-2.5 bg-slate-50 border-t border-slate-200 flex items-center justify-end shrink-0">
                        <button type="button" @click="showDetailsModal = false"
                            class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl transition-colors cursor-pointer">
                            Close Details
                        </button>
                    </div>
                </div>
            </div>
        </template>

        <!-- ================= MODALS: SPONSORSHIP TYPE ================= -->
        <!-- 1. Add Sponsorship Type Modal (Compact) -->
        <template x-teleport="body">
            <div x-show="showAddTypeModal"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/40 backdrop-blur-sm" x-transition
                x-cloak>
                <div class="bg-white rounded-xl max-w-md w-full p-4 border border-slate-100 shadow-xl space-y-3 max-h-[90vh] overflow-y-auto"
                    @click.away="showAddTypeModal = false">
                    <div class="flex items-center justify-between pb-2.5 border-b border-slate-100">
                        <h3 class="text-xs font-black text-slate-900 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                            </svg>
                            <span>{{ __('messages.add_sponsorship_type') }}</span>
                        </h3>
                        <button type="button" @click="showAddTypeModal = false"
                            class="text-slate-400 hover:text-slate-600 text-base font-black cursor-pointer leading-none">&times;</button>
                    </div>

                    <form method="POST" action="{{ route('admin.events.sponsorship_types.store', $event->id) }}" class="space-y-2.5">
                        @csrf
                        <input type="hidden" name="status" value="1">
                        <div>
                            <label class="text-[10px] font-bold text-slate-700 block mb-1">
                                {{ __('messages.sponsorship_title_category') }} <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="title" required placeholder="{{ __('messages.sponsorship_title_placeholder') }}"
                                class="w-full text-xs font-semibold px-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary-500">
                        </div>

                        <div class="grid grid-cols-2 gap-2.5">
                            <div>
                                <label class="text-[10px] font-bold text-slate-700 block mb-1">
                                    {{ __('messages.amount') }} (₹) <span class="text-rose-500">*</span>
                                </label>
                                <input type="number" step="0.01" min="0" name="amount" required placeholder="0.00"
                                    class="w-full text-xs font-semibold px-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary-500">
                            </div>

                            <div>
                                <label class="text-[10px] font-bold text-slate-700 block mb-1">
                                    {{ __('messages.max_sponsors') }}
                                </label>
                                <input type="number" min="0" name="max_sponsors" placeholder="0 = {{ __('messages.unlimited') }}"
                                    class="w-full text-xs font-semibold px-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary-500">
                            </div>
                        </div>

                        <div>
                            <label class="text-[10px] font-bold text-slate-700 block mb-1">
                                {{ __('messages.sponsorship_desc_perks') }}
                            </label>
                            <textarea name="description" rows="2" placeholder="{{ __('messages.sponsorship_desc_placeholder') }}"
                                class="w-full text-xs font-semibold px-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary-500"></textarea>
                        </div>

                        <div class="flex justify-end gap-2 pt-2.5 border-t border-slate-100">
                            <button type="button" @click="showAddTypeModal = false"
                                class="px-3 py-1.5 border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs rounded-lg transition-colors cursor-pointer">
                                {{ __('messages.cancel') ?? 'Cancel' }}
                            </button>
                            <button type="submit"
                                class="px-4 py-1.5 bg-primary-500 hover:bg-primary-600 active:scale-95 text-white font-extrabold text-xs rounded-lg shadow-2xs transition-all cursor-pointer">
                                {{ __('messages.save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>

        <!-- 2. Edit Sponsorship Type Modal (Compact) -->
        <template x-teleport="body">
            <div x-show="showEditTypeModal"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/40 backdrop-blur-sm" x-transition
                x-cloak>
                <div class="bg-white rounded-xl max-w-md w-full p-4 border border-slate-100 shadow-xl space-y-3 max-h-[90vh] overflow-y-auto"
                    @click.away="showEditTypeModal = false">
                    <div class="flex items-center justify-between pb-2.5 border-b border-slate-100">
                        <h3 class="text-xs font-black text-slate-900 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            <span>{{ __('messages.edit_sponsorship_type') }}</span>
                        </h3>
                        <button type="button" @click="showEditTypeModal = false"
                            class="text-slate-400 hover:text-slate-600 text-base font-black cursor-pointer leading-none">&times;</button>
                    </div>

                    <form method="POST" :action="'{{ url('admin/events/sponsorship-types') }}/' + editingType.id" class="space-y-2.5">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" value="1">
                        <div>
                            <label class="text-[10px] font-bold text-slate-700 block mb-1">
                                {{ __('messages.sponsorship_title_category') }} <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="title" required x-model="editingType.title"
                                class="w-full text-xs font-semibold px-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary-500">
                        </div>

                        <div class="grid grid-cols-2 gap-2.5">
                            <div>
                                <label class="text-[10px] font-bold text-slate-700 block mb-1">
                                    {{ __('messages.amount') }} (₹) <span class="text-rose-500">*</span>
                                </label>
                                <input type="number" step="0.01" min="0" name="amount" required x-model="editingType.amount"
                                    class="w-full text-xs font-semibold px-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary-500">
                            </div>

                            <div>
                                <label class="text-[10px] font-bold text-slate-700 block mb-1">
                                    {{ __('messages.max_sponsors') }}
                                </label>
                                <input type="number" min="0" name="max_sponsors" x-model="editingType.max_sponsors"
                                    class="w-full text-xs font-semibold px-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary-500">
                            </div>
                        </div>

                        <div>
                            <label class="text-[10px] font-bold text-slate-700 block mb-1">
                                {{ __('messages.sponsorship_desc_perks') }}
                            </label>
                            <textarea name="description" rows="2" x-model="editingType.description"
                                class="w-full text-xs font-semibold px-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary-500"></textarea>
                        </div>

                        <div class="flex justify-end gap-2 pt-2.5 border-t border-slate-100">
                            <button type="button" @click="showEditTypeModal = false"
                                class="px-3 py-1.5 border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs rounded-lg transition-colors cursor-pointer">
                                {{ __('messages.cancel') ?? 'Cancel' }}
                            </button>
                            <button type="submit"
                                class="px-4 py-1.5 bg-primary-500 hover:bg-primary-600 active:scale-95 text-white font-extrabold text-xs rounded-lg shadow-2xs transition-all cursor-pointer">
                                {{ __('messages.save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>

        <!-- ================= MODALS: REGISTERED SPONSOR ================= -->
        <!-- 3. Add Sponsor Modal (Admin Manual Add) -->
        <template x-teleport="body">
            <div x-show="showAddSponsorModal"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/40 backdrop-blur-sm" x-transition
                x-cloak>
                <div class="bg-white rounded-2xl max-w-xl w-full p-5 border border-slate-100 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto"
                    @click.away="showAddSponsorModal = false">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <h3 class="text-sm font-black text-slate-900 flex items-center gap-2">
                            <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                            </svg>
                            <span>{{ __('messages.add_sponsor') }}</span>
                        </h3>
                        <button type="button" @click="showAddSponsorModal = false"
                            class="text-slate-400 hover:text-slate-600 text-lg font-black cursor-pointer leading-none">&times;</button>
                    </div>

                    <form method="POST" action="{{ route('admin.events.sponsors.store', $event->id) }}" enctype="multipart/form-data" class="space-y-3.5">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="sm:col-span-2">
                                <label class="text-[11px] font-bold text-slate-700 block mb-1">
                                    {{ __('messages.sponsor_name') }} <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" name="name" required placeholder="{{ __('messages.sponsor_name_placeholder') }}"
                                    class="w-full text-xs font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary-500">
                            </div>

                            <div>
                                <label class="text-[11px] font-bold text-slate-700 block mb-1">
                                    {{ __('messages.phone') }} <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" name="mobile" required placeholder="9876543210" maxlength="10" minlength="10" pattern="[0-9]{10}" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)"
                                    class="w-full text-xs font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary-500">
                            </div>

                            <div>
                                <label class="text-[11px] font-bold text-slate-700 block mb-1">
                                    {{ __('messages.email') }}
                                </label>
                                <input type="email" name="email" placeholder="Email Address"
                                    class="w-full text-xs font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary-500">
                            </div>

                            <div>
                                <label class="text-[11px] font-bold text-slate-700 block mb-1">
                                    {{ __('messages.sponsorship_type') }}
                                </label>
                                <select name="sponsorship_type_id" x-model="editingSponsor.sponsorship_type_id"
                                    class="w-full text-xs font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary-500">
                                    <option value="">{{ __('messages.general_sponsor_option') }}</option>
                                    @foreach($sponsorshipTypes as $st)
                                        <option value="{{ $st->id }}">{{ $st->title }} (₹ {{ number_format($st->amount) }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="text-[11px] font-bold text-slate-700 block mb-1">
                                    {{ __('messages.amount') }} (₹)
                                </label>
                                <input type="number" step="0.01" min="0" name="amount" x-model="editingSponsor.amount" placeholder="0.00"
                                    class="w-full text-xs font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary-500">
                            </div>

                            <div>
                                <label class="text-[11px] font-bold text-slate-700 block mb-1">
                                    {{ __('messages.city') }}
                                </label>
                                <input type="text" name="city" placeholder="City / Area"
                                    class="w-full text-xs font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary-500">
                            </div>

                            <div class="sm:col-span-2">
                                <label class="text-[11px] font-bold text-slate-700 block mb-1">
                                    {{ __('messages.sponsor_logo') }} ({{ __('messages.optional') }})
                                </label>
                                <input type="file" name="logo" accept="image/*"
                                    class="text-[11px] block w-full text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[10px] file:font-semibold file:bg-primary-50 file:text-primary-700 border border-slate-200 rounded-xl p-1 bg-slate-50">
                            </div>

                            <div class="sm:col-span-2">
                                <label class="text-[11px] font-bold text-slate-700 block mb-1">
                                    {{ __('messages.address') }}
                                </label>
                                <input type="text" name="address" placeholder="Address"
                                    class="w-full text-xs font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary-500">
                            </div>

                            <div>
                                <label class="text-[11px] font-bold text-slate-700 block mb-1">
                                    {{ __('messages.payment_status') }}
                                </label>
                                <select name="payment_status" x-model="editingSponsor.payment_status"
                                    class="w-full text-xs font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary-500">
                                    <option value="pending">{{ __('messages.payment_pending') }}</option>
                                    <option value="received">{{ __('messages.payment_received') }}</option>
                                    <option value="failed">{{ __('messages.payment_failed') }}</option>
                                </select>
                            </div>

                            <div>
                                <label class="text-[11px] font-bold text-slate-700 block mb-1">
                                    {{ __('messages.status') }}
                                </label>
                                <select name="status" x-model="editingSponsor.status"
                                    class="w-full text-xs font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary-500">
                                    <option value="approved">{{ __('messages.approved') }}</option>
                                    <option value="pending">{{ __('messages.pending') }}</option>
                                    <option value="rejected">{{ __('messages.rejected') }}</option>
                                </select>
                            </div>

                            <div class="sm:col-span-2">
                                <label class="text-[11px] font-bold text-slate-700 block mb-1">
                                    {{ __('messages.notes_remarks') }}
                                </label>
                                <textarea name="notes" rows="2" placeholder="Notes..."
                                    class="w-full text-xs font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary-500"></textarea>
                            </div>
                        </div>

                        <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                            <button type="button" @click="showAddSponsorModal = false"
                                class="px-4 py-2 border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs rounded-xl transition-colors cursor-pointer">
                                {{ __('messages.cancel') ?? 'Cancel' }}
                            </button>
                            <button type="submit"
                                class="px-5 py-2 bg-primary-500 hover:bg-primary-600 active:scale-95 text-white font-black text-xs rounded-xl shadow-xs transition-all cursor-pointer">
                                {{ __('messages.save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>

        <!-- 4. Edit Sponsor Modal -->
        <template x-teleport="body">
            <div x-show="showEditSponsorModal"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/40 backdrop-blur-sm" x-transition
                x-cloak>
                <div class="bg-white rounded-2xl max-w-xl w-full p-5 border border-slate-100 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto"
                    @click.away="showEditSponsorModal = false">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <h3 class="text-sm font-black text-slate-900 flex items-center gap-2">
                            <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            <span>{{ __('messages.edit_sponsor') }}</span>
                        </h3>
                        <button type="button" @click="showEditSponsorModal = false"
                            class="text-slate-400 hover:text-slate-600 text-lg font-black cursor-pointer leading-none">&times;</button>
                    </div>

                    <form method="POST" :action="'{{ url('admin/events/sponsors') }}/' + editingSponsor.id" enctype="multipart/form-data" class="space-y-3.5">
                        @csrf
                        @method('PUT')
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="sm:col-span-2">
                                <label class="text-[11px] font-bold text-slate-700 block mb-1">
                                    {{ __('messages.sponsor_name') }} <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" name="name" required x-model="editingSponsor.name"
                                    class="w-full text-xs font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary-500">
                            </div>

                            <div>
                                <label class="text-[11px] font-bold text-slate-700 block mb-1">
                                    {{ __('messages.phone') }} <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" name="mobile" required x-model="editingSponsor.mobile" maxlength="10" minlength="10" pattern="[0-9]{10}" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)"
                                    class="w-full text-xs font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary-500">
                            </div>

                            <div>
                                <label class="text-[11px] font-bold text-slate-700 block mb-1">
                                    {{ __('messages.email') }}
                                </label>
                                <input type="email" name="email" x-model="editingSponsor.email"
                                    class="w-full text-xs font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary-500">
                            </div>

                            <div>
                                <label class="text-[11px] font-bold text-slate-700 block mb-1">
                                    {{ __('messages.sponsorship_type') }}
                                </label>
                                <select name="sponsorship_type_id" x-model="editingSponsor.sponsorship_type_id"
                                    class="w-full text-xs font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary-500">
                                    <option value="">{{ __('messages.general_sponsor_option') }}</option>
                                    @foreach($sponsorshipTypes as $st)
                                        <option value="{{ $st->id }}">{{ $st->title }} (₹ {{ number_format($st->amount) }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="text-[11px] font-bold text-slate-700 block mb-1">
                                    {{ __('messages.amount') }} (₹) <span class="text-rose-500">*</span>
                                </label>
                                <input type="number" step="0.01" min="0" name="amount" required x-model="editingSponsor.amount"
                                    class="w-full text-xs font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary-500">
                            </div>

                            <div>
                                <label class="text-[11px] font-bold text-slate-700 block mb-1">
                                    {{ __('messages.city') }}
                                </label>
                                <input type="text" name="city" x-model="editingSponsor.city"
                                    class="w-full text-xs font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary-500">
                            </div>

                            <div class="sm:col-span-2">
                                <label class="text-[11px] font-bold text-slate-700 block mb-1">
                                    {{ __('messages.sponsor_logo') }} ({{ __('messages.upload_new_logo') }})
                                </label>
                                <div class="flex items-center gap-3">
                                    <template x-if="editingSponsor.logo_url">
                                        <img :src="editingSponsor.logo_url" class="w-10 h-10 object-contain rounded-lg border border-slate-200 bg-white p-0.5 shrink-0">
                                    </template>
                                    <input type="file" name="logo" accept="image/*"
                                        class="text-[11px] block w-full text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[10px] file:font-semibold file:bg-primary-50 file:text-primary-700 border border-slate-200 rounded-xl p-1 bg-slate-50">
                                </div>
                            </div>

                            <div class="sm:col-span-2">
                                <label class="text-[11px] font-bold text-slate-700 block mb-1">
                                    {{ __('messages.address') }}
                                </label>
                                <input type="text" name="address" x-model="editingSponsor.address"
                                    class="w-full text-xs font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary-500">
                            </div>

                            <div>
                                <label class="text-[11px] font-bold text-slate-700 block mb-1">
                                    {{ __('messages.payment_status') }}
                                </label>
                                <select name="payment_status" x-model="editingSponsor.payment_status"
                                    class="w-full text-xs font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary-500">
                                    <option value="pending">{{ __('messages.payment_pending') }}</option>
                                    <option value="received">{{ __('messages.payment_received') }}</option>
                                    <option value="failed">{{ __('messages.payment_failed') }}</option>
                                </select>
                            </div>

                            <div>
                                <label class="text-[11px] font-bold text-slate-700 block mb-1">
                                    {{ __('messages.status') }}
                                </label>
                                <select name="status" x-model="editingSponsor.status"
                                    class="w-full text-xs font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary-500">
                                    <option value="approved">{{ __('messages.approved') }}</option>
                                    <option value="pending">{{ __('messages.pending') }}</option>
                                    <option value="rejected">{{ __('messages.rejected') }}</option>
                                </select>
                            </div>

                            <div class="sm:col-span-2">
                                <label class="text-[11px] font-bold text-slate-700 block mb-1">
                                    {{ __('messages.notes_remarks') }}
                                </label>
                                <textarea name="notes" rows="2" x-model="editingSponsor.notes"
                                    class="w-full text-xs font-semibold px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary-500"></textarea>
                            </div>
                        </div>

                        <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                            <button type="button" @click="showEditSponsorModal = false"
                                class="px-4 py-2 border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs rounded-xl transition-colors cursor-pointer">
                                {{ __('messages.cancel') ?? 'Cancel' }}
                            </button>
                            <button type="submit"
                                class="px-5 py-2 bg-primary-500 hover:bg-primary-600 active:scale-95 text-white font-black text-xs rounded-xl shadow-xs transition-all cursor-pointer">
                                {{ __('messages.save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>

        <!-- 5. View Sponsor Details Modal -->
        <template x-teleport="body">
            <div x-show="showViewSponsorModal"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/50 backdrop-blur-sm" x-transition
                x-cloak>
                <div class="bg-white rounded-2xl max-w-md w-full p-5 border border-slate-100 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto"
                    @click.away="showViewSponsorModal = false">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <h3 class="text-sm font-black text-slate-900 flex items-center gap-2">
                            <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>{{ __('messages.sponsor_details') }}</span>
                        </h3>
                        <button type="button" @click="showViewSponsorModal = false"
                            class="text-slate-400 hover:text-slate-600 text-lg font-black cursor-pointer leading-none">&times;</button>
                    </div>

                    <div class="space-y-3.5">
                        <!-- Top Header with Logo -->
                        <div class="flex items-center gap-3 bg-slate-50 p-3 rounded-xl border border-slate-100">
                            <template x-if="viewingSponsor.logo_url">
                                <img :src="viewingSponsor.logo_url" class="w-14 h-14 object-contain rounded-xl bg-white border border-slate-200 p-1 shrink-0">
                            </template>
                            <template x-if="!viewingSponsor.logo_url">
                                <div class="w-14 h-14 rounded-xl bg-primary-50 text-primary-700 font-black text-xl flex items-center justify-center border border-primary-100 shrink-0"
                                     x-text="(viewingSponsor.name || '').charAt(0).toUpperCase()"></div>
                            </template>
                            <div class="min-w-0 flex-1">
                                <h4 class="text-sm font-black text-slate-900 leading-tight" x-text="viewingSponsor.name"></h4>
                                <div class="inline-flex items-center gap-1 font-bold text-slate-600 text-[11px] mt-1 bg-white px-2 py-0.5 rounded border border-slate-200">
                                    <svg class="w-3 h-3 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                    </svg>
                                    <span x-text="viewingSponsor.type_title || '{{ __('messages.general_sponsor_option') }}'"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Info Grid -->
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <div class="bg-slate-50 p-2 rounded-lg border border-slate-100 min-w-0 overflow-hidden">
                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-wider block">{{ __('messages.phone') }}</span>
                                <span class="font-bold text-blue-700 block mt-0.5 truncate" x-text="viewingSponsor.mobile || '-'"></span>
                            </div>
                            <div class="bg-slate-50 p-2 rounded-lg border border-slate-100 min-w-0 overflow-hidden">
                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-wider block">{{ __('messages.amount') }}</span>
                                <span class="font-black text-emerald-700 block mt-0.5 truncate" x-text="'₹ ' + Number(viewingSponsor.amount || 0).toLocaleString()"></span>
                            </div>
                            <div class="bg-slate-50 p-2 rounded-lg border border-slate-100 min-w-0 overflow-hidden">
                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-wider block">{{ __('messages.payment_status') }}</span>
                                <span class="font-black block mt-0.5 truncate"
                                      :class="viewingSponsor.payment_status === 'received' ? 'text-emerald-700' : (viewingSponsor.payment_status === 'failed' ? 'text-rose-700' : 'text-amber-700')"
                                      x-text="viewingSponsor.payment_status === 'received' ? '{{ __('messages.payment_received') }}' : (viewingSponsor.payment_status === 'failed' ? '{{ __('messages.payment_failed') }}' : '{{ __('messages.payment_pending') }}')"></span>
                            </div>
                            <div class="bg-slate-50 p-2 rounded-lg border border-slate-100 min-w-0 overflow-hidden" x-show="viewingSponsor.transaction_nos">
                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-wider block">Transaction ID</span>
                                <span class="font-mono font-black text-slate-800 block mt-0.5 truncate" x-text="viewingSponsor.transaction_nos"></span>
                            </div>
                            <div class="bg-slate-50 p-2 rounded-lg border border-slate-100 min-w-0 overflow-hidden">
                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-wider block">{{ __('messages.status') }}</span>
                                <span class="font-black block mt-0.5 truncate"
                                      :class="viewingSponsor.status === 'approved' ? 'text-emerald-700' : (viewingSponsor.status === 'rejected' ? 'text-rose-700' : 'text-amber-700')"
                                      x-text="viewingSponsor.status === 'approved' ? '{{ __('messages.approved') }}' : (viewingSponsor.status === 'rejected' ? '{{ __('messages.rejected') }}' : '{{ __('messages.pending') }}')"></span>
                            </div>
                            <template x-if="viewingSponsor.city">
                                <div class="col-span-2 bg-slate-50 p-2 rounded-lg border border-slate-100 min-w-0 overflow-hidden">
                                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-wider block">{{ __('messages.city') }}</span>
                                    <span class="font-bold text-slate-800 block mt-0.5 break-words" x-text="viewingSponsor.city"></span>
                                </div>
                            </template>
                            <template x-if="viewingSponsor.email">
                                <div class="col-span-2 bg-slate-50 p-2 rounded-lg border border-slate-100 min-w-0 overflow-hidden">
                                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-wider block">{{ __('messages.email') }}</span>
                                    <span class="font-bold text-slate-800 block mt-0.5 break-all text-[11px]" x-text="viewingSponsor.email"></span>
                                </div>
                            </template>
                            <template x-if="viewingSponsor.address">
                                <div class="col-span-2 bg-slate-50 p-2 rounded-lg border border-slate-100 min-w-0 overflow-hidden">
                                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-wider block">{{ __('messages.address') }}</span>
                                    <span class="font-bold text-slate-800 block mt-0.5 break-words leading-relaxed" x-text="viewingSponsor.address"></span>
                                </div>
                            </template>
                            <template x-if="viewingSponsor.notes">
                                <div class="col-span-2 bg-slate-50 p-2 rounded-lg border border-slate-100 min-w-0 overflow-hidden">
                                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-wider block">{{ __('messages.notes_remarks') }}</span>
                                    <span class="font-medium text-slate-700 block mt-0.5 leading-relaxed break-words" x-text="viewingSponsor.notes"></span>
                                </div>
                            </template>
                            <div class="col-span-2 bg-slate-50 p-2 rounded-lg border border-slate-100 text-[10px] text-slate-500 font-medium flex items-center justify-between min-w-0 overflow-hidden">
                                <span>{{ __('messages.registered_on') }}</span>
                                <span class="font-bold text-slate-800" x-text="viewingSponsor.created_at"></span>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end pt-3 border-t border-slate-100">
                        <button type="button" @click="showViewSponsorModal = false"
                            class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl transition-colors cursor-pointer">
                            {{ __('messages.close') ?? 'Close' }}
                        </button>
                    </div>
                </div>
            </div>
        </template>

        <!-- Event Gallery Lightbox Preview Modal -->
        <template x-teleport="body">
            <div x-show="galleryLightbox" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 style="position: fixed; inset: 0; z-index: 999999; background-color: rgba(15, 23, 42, 0.96); backdrop-filter: blur(12px);"
                 class="flex flex-col items-center justify-between p-4 sm:p-6 select-none" 
                 @click="galleryLightbox = false"
                 x-cloak>
                
                <!-- Close button (Top Right) -->
                <button @click="galleryLightbox = false" 
                        style="position: absolute; top: 1.5rem; right: 1.5rem; z-index: 1000000;"
                        class="p-2.5 rounded-full bg-slate-800/90 hover:bg-rose-600 text-white border border-white/20 hover:border-rose-500 transition-all duration-200 cursor-pointer shadow-xl hover:scale-110 active:scale-95"
                        title="Close (Esc)">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>

                <!-- Prev / Next Navigation Buttons -->
                <button x-show="galleryImages.length > 1" 
                        @click.stop="prevGalleryImage()" 
                        style="position: absolute; left: 1.5rem; top: 50%; transform: translateY(-50%); z-index: 1000000;"
                        class="p-3 rounded-full bg-slate-800/90 hover:bg-white text-white hover:text-slate-900 border border-white/20 hover:border-white transition-all duration-200 cursor-pointer shadow-xl hover:scale-110 active:scale-95"
                        title="Previous Image (Left Arrow)">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                </button>

                <button x-show="galleryImages.length > 1" 
                        @click.stop="nextGalleryImage()" 
                        style="position: absolute; right: 1.5rem; top: 50%; transform: translateY(-50%); z-index: 1000000;"
                        class="p-3 rounded-full bg-slate-800/90 hover:bg-white text-white hover:text-slate-900 border border-white/20 hover:border-white transition-all duration-200 cursor-pointer shadow-xl hover:scale-110 active:scale-95"
                        title="Next Image (Right Arrow)">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </button>

                <!-- Main Centered Preview Media -->
                <div class="flex-1 flex items-center justify-center max-w-4xl w-full my-auto px-4 py-8" @click.stop>
                    <div class="relative flex items-center justify-center bg-slate-900/60 p-2 sm:p-3 rounded-2xl border border-white/10 shadow-2xl overflow-hidden max-h-[70vh] max-w-[80vw]">
                        <template x-if="galleryImages[galleryLightboxIndex]?.isVideo">
                            <video :src="galleryImages[galleryLightboxIndex]?.src"
                                   controls
                                   autoplay
                                   playsinline
                                   class="rounded-xl shadow-2xl transition-all duration-300"
                                   style="max-height: 60vh; max-width: 70vw; width: auto; height: auto; outline: none;">
                            </video>
                        </template>
                        <template x-if="!galleryImages[galleryLightboxIndex]?.isVideo">
                            <img :src="galleryImages[galleryLightboxIndex]?.src" 
                                 :alt="galleryImages[galleryLightboxIndex]?.caption || 'Event Media'"
                                 class="rounded-xl shadow-lg transition-all duration-300 select-none pointer-events-auto"
                                 style="max-height: 58vh; max-width: 65vw; width: auto; height: auto; object-fit: contain; display: block;">
                        </template>
                    </div>
                </div>

                <!-- Bottom Image Counter -->
                <div x-show="galleryImages.length > 0"
                     style="position: absolute; bottom: 1.5rem; left: 50%; transform: translateX(-50%); z-index: 1000000;"
                     class="px-4 py-1.5 rounded-full bg-slate-900/90 backdrop-blur-md border border-white/20 text-white font-bold text-xs tracking-wider shadow-xl flex items-center gap-1.5">
                    <span class="text-primary-400 font-black" x-text="galleryLightboxIndex + 1"></span>
                    <span class="text-white/40">/</span>
                    <span class="text-white/80" x-text="galleryImages.length"></span>
                </div>
            </div>
        </template>
    </div>

    <script>
        function adminEventShowData() {
            return {
                mainTab: @json(request('tab', 'details')),
                sponsorshipSubTab: @json(request('subtab', 'types')),
                statusTab: 'all',
                topRankFilter: 'top5',
                selectedEduType: 'all',
                selectedStandard: 'all',
                search: '',
                showUploadModal: false,
                showDetailsModal: false,
                selectedRegistration: {},
                previewLang: @json(app()->getLocale() === 'gu' ? 'gu' : 'en'),

                // Gallery Lightbox State
                galleryLightbox: false,
                galleryLightboxIndex: 0,
                galleryImages: [
                    @foreach($gallery as $photo)
                        {
                            src: '{{ $photo->url }}',
                            caption: '{{ addslashes($photo->caption ?? $event->title) }}',
                            isVideo: {{ $photo->isVideo() ? 'true' : 'false' }}
                        },
                    @endforeach
                ],
                nextGalleryImage() {
                    if (this.galleryImages.length > 0) {
                        this.galleryLightboxIndex = (this.galleryLightboxIndex + 1) % this.galleryImages.length;
                    }
                },
                prevGalleryImage() {
                    if (this.galleryImages.length > 0) {
                        this.galleryLightboxIndex = (this.galleryLightboxIndex - 1 + this.galleryImages.length) % this.galleryImages.length;
                    }
                },

                // Sponsorship state
                sponsorFilterType: 'all',
                sponsorFilterStatus: 'all',
                sponsorFilterPayment: 'all',
                sponsorSearch: '',
                showAddTypeModal: false,
                showEditTypeModal: false,
                editingType: { id: '', title: '', amount: '', max_sponsors: 0, description: '', status: true },
                showAddSponsorModal: false,
                showEditSponsorModal: false,
                editingSponsor: { id: '', name: '', contact_person: '', mobile: '', email: '', sponsorship_type_id: '', amount: '', city: '', address: '', notes: '', payment_status: 'pending', status: 'pending' },
                showViewSponsorModal: false,
                viewingSponsor: {},

                openEditType(type) {
                    this.editingType = Object.assign({}, type);
                    this.showEditTypeModal = true;
                },
                openAddSponsor(typeId = '') {
                    this.editingSponsor = {
                        sponsorship_type_id: typeId || '',
                        amount: '',
                        payment_status: 'pending',
                        status: 'approved'
                    };
                    this.showAddSponsorModal = true;
                },
                openEditSponsor(sponsor) {
                    this.editingSponsor = Object.assign({}, sponsor);
                    this.showEditSponsorModal = true;
                },
                openViewSponsor(sponsor) {
                    this.viewingSponsor = sponsor || {};
                    this.showViewSponsorModal = true;
                },
                async toggleTypeStatus(typeId) {
                    const btn = document.getElementById('status-btn-' + typeId);
                    try {
                        const res = await fetch('{{ url('admin/events/sponsorship-types') }}/' + typeId + '/toggle-status', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        });
                        const data = await res.json();
                        if (data.success && btn) {
                            if (data.status) {
                                btn.className = 'px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider transition-colors cursor-pointer bg-emerald-50 text-emerald-700 border border-emerald-200/60';
                                btn.textContent = this.previewLang === 'gu' ? 'સક્રિય' : 'Active';
                            } else {
                                btn.className = 'px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider transition-colors cursor-pointer bg-slate-100 text-slate-600 border border-slate-200';
                                btn.textContent = this.previewLang === 'gu' ? 'નિષ્ક્રિય' : 'Inactive';
                            }
                        }
                    } catch (e) {
                        console.error(e);
                    }
                },
                sponsorMatches(s) {
                    if (!s) return false;
                    if (this.sponsorFilterType !== 'all') {
                        if (String(s.sponsorship_type_id) !== String(this.sponsorFilterType)) return false;
                    }
                    if (this.sponsorFilterStatus !== 'all') {
                        if (s.status !== this.sponsorFilterStatus) return false;
                    }
                    if (this.sponsorFilterPayment !== 'all') {
                        if (s.payment_status !== this.sponsorFilterPayment) return false;
                    }
                    if (this.sponsorSearch && this.sponsorSearch.trim()) {
                        const q = this.sponsorSearch.toLowerCase().trim();
                        const nameMatch = (s.name || '').toLowerCase().includes(q);
                        const contactMatch = (s.contact_person || '').toLowerCase().includes(q);
                        const mobileMatch = (s.mobile || '').toLowerCase().includes(q);
                        const cityMatch = (s.city || '').toLowerCase().includes(q);
                        const typeMatch = (s.type_title || '').toLowerCase().includes(q);
                        if (!nameMatch && !contactMatch && !mobileMatch && !cityMatch && !typeMatch) return false;
                    }
                    return true;
                },

                openBiodata(data) {
                    this.selectedRegistration = data || {};
                    this.showDetailsModal = true;
                },
                printBiodata() {
                    const el = document.getElementById('printableBiodata');
                    if (!el) return;
                    const w = window.open('', '_blank', 'width=850,height=950');
                    if (!w) {
                        alert('Please allow pop-ups for this website to print/download biodata.');
                        return;
                    }
                    const htmlContent = el.innerHTML;
                    const docContent = '<!DOCTYPE html>' +
'<html>' +
'<head>' +
'    <meta charset="utf-8">' +
'    <title>Candidate Biodata - Satwara Yuva Melo</title>' +
'    <script src="https://cdn.tailwindcss.com"><\/script>' +
'    <style>' +
'        * { box-sizing: border-box; margin: 0; padding: 0; }' +
'        body {' +
'            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;' +
'            background: #f1f5f9;' +
'            color: #0f172a;' +
'            padding: 16px;' +
'            display: flex;' +
'            justify-content: center;' +
'        }' +
'        .biodata-sheet {' +
'            background: #ffffff;' +
'            width: 100%;' +
'            max-width: 680px;' +
'            padding: 14px;' +
'            border: 2px solid #0f172a;' +
'            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);' +
'        }' +
'        table { width: 100%; border-collapse: collapse; table-layout: fixed; }' +
'        td, th { padding: 3px 6px; word-break: break-word; font-size: 11px; }' +
'        @media print {' +
'            body {' +
'                background: #ffffff !important;' +
'                padding: 0 !important;' +
'                margin: 0 !important;' +
'                display: block !important;' +
'            }' +
'            @page {' +
'                margin: 6mm 8mm;' +
'                size: A4 portrait;' +
'            }' +
'            .biodata-sheet {' +
'                border: 2px solid #0f172a !important;' +
'                box-shadow: none !important;' +
'                max-width: 100% !important;' +
'                padding: 10px !important;' +
'                page-break-inside: avoid;' +
'            }' +
'            .no-print { display: none !important; }' +
'        }' +
'    </style>' +
'</head>' +
'<body>' +
'    <div class="biodata-sheet">' +
        htmlContent +
'    </div>' +
'</body>' +
'</html>';
                    w.document.write(docContent);
                    w.document.close();
                    setTimeout(() => { 
                        w.focus(); 
                        w.print(); 
                    }, 500);
                },
                getPhotoUrl(fd) {
                    if (!fd) return '';
                    if (fd.member_photo_url) return fd.member_photo_url;
                    if (fd.selfie_url) return fd.selfie_url;
                    if (fd.whatsapp_image_url) return fd.whatsapp_image_url;
                    if (fd.member_photo && typeof fd.member_photo === 'string' && (fd.member_photo.startsWith('http') || fd.member_photo.startsWith('/storage/'))) return fd.member_photo;
                    return '';
                },
                getSiblingStat(fd, type, isMarried) {
                    const noneText = this.previewLang === 'en' ? 'None' : 'નથી';
                    if (!fd) return noneText;
                    let arr = [];
                    if (fd.siblings_json) {
                        try {
                            arr = typeof fd.siblings_json === 'string' ? JSON.parse(fd.siblings_json) : fd.siblings_json;
                        } catch (e) {
                            arr = [];
                        }
                    }
                    if (Array.isArray(arr) && arr.length > 0) {
                        const filtered = arr.filter(function (s) {
                            const relMatch = s.relation && s.relation.toLowerCase().includes(type.toLowerCase());
                            const marMatch = isMarried ? (s.married === 'Yes' || s.married === 'Married') : (s.married !== 'Yes' && s.married !== 'Married');
                            return relMatch && marMatch;
                        });
                        if (filtered.length > 0) {
                            return filtered.length + ' (' + filtered.map(function (s) { return s.details || '1'; }).join(', ') + ')';
                        }
                    }
                    if (type.toLowerCase().includes('elder') && type.toLowerCase().includes('brother')) {
                        const val = isMarried ? fd.elder_brother_married : fd.elder_brother;
                        return (val && val !== 'No' && val !== '0') ? val : noneText;
                    }
                    if (type.toLowerCase().includes('younger') && type.toLowerCase().includes('brother')) {
                        const val = isMarried ? fd.younger_brother_married : fd.younger_brother;
                        return (val && val !== 'No' && val !== '0') ? val : noneText;
                    }
                    if (type.toLowerCase().includes('elder') && type.toLowerCase().includes('sister')) {
                        const val = isMarried ? fd.elder_sister_married : fd.elder_sister;
                        return (val && val !== 'No' && val !== '0') ? val : noneText;
                    }
                    if (type.toLowerCase().includes('younger') && type.toLowerCase().includes('sister')) {
                        const val = isMarried ? fd.younger_sister_married : fd.younger_sister;
                        return (val && val !== 'No' && val !== '0') ? val : noneText;
                    }
                    return noneText;
                }
            };
        }
    </script>
@endsection