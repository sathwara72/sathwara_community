@extends('layouts.member')

@php
    $isGu = (app()->getLocale() === 'gu');
    $logoUrl = App\Models\Setting::get('website_logo') ? asset('storage/' . App\Models\Setting::get('website_logo')) : asset('logo.png');
    $userName = auth()->user() ? auth()->user()->name : 'Member';
    $memberCode = auth()->user() ? (auth()->user()->member_code ?: sprintf('#%05d', auth()->user()->id)) : '-';
@endphp

@section('page_title', __('messages.dashboard_overview'))

@section('content')
    <div class="space-y-3.5" x-data="{ 
            showPassModal: false, 
            activeEvent: null, 
            activePasses: [],
            activeAttendee: '{{ addslashes($userName) }}',
            activeMemberId: '{{ $memberCode }}',
            openPassModal(eventObj, passesList, attendeeName) {
                this.activeEvent = eventObj;
                this.activePasses = passesList;
                this.activeAttendee = attendeeName || '{{ addslashes($userName) }}';
                this.showPassModal = true;
            }
        }">

        <!-- Welcome Header & Quick Stats Card (Compact) -->
        <div
            class="bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 text-white rounded-xl p-3.5 sm:p-4 shadow-sm border border-slate-700/60 relative overflow-hidden">
            <div class="absolute -right-8 -top-8 w-28 h-28 bg-primary-500/10 rounded-full blur-xl pointer-events-none">
            </div>

            <div class="flex items-center justify-between gap-3 relative z-10">
                <h2 class="text-base sm:text-lg font-black text-white leading-tight">
                    {{ $isGu ? 'નમસ્તે, ' . $user->name . '!' : 'Welcome, ' . $user->name . '!' }}
                </h2>

                <!-- Quick Action: Membership Card -->
                <a href="{{ route('member.card') }}"
                    class="px-3 py-1.5 bg-primary-500 hover:bg-primary-600 text-white font-bold text-xs rounded-lg shadow-xs transition-all flex items-center gap-1.5 cursor-pointer shrink-0">
                    <svg class="w-4 h-4 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                    </svg>
                    <span>{{ $isGu ? 'મેમ્બરશિપ કાર્ડ જુઓ' : 'View Membership Card' }}</span>
                </a>
            </div>

            <!-- Quick Summary Mini Strip -->
            <div class="grid grid-cols-3 gap-2 pt-2.5 mt-2.5 border-t border-slate-700/60">
                <div class="bg-slate-800/70 rounded-lg p-2 border border-slate-700/50">
                    <span
                        class="text-[9px] font-bold text-slate-400 block uppercase tracking-wider">{{ $isGu ? 'સક્રિય કાર્યક્રમો' : 'Active Events' }}</span>
                    <span
                        class="text-sm sm:text-base font-black text-white block mt-0.5">{{ $activeEvents->count() }}</span>
                </div>
                <div class="bg-slate-800/70 rounded-lg p-2 border border-slate-700/50">
                    <span
                        class="text-[9px] font-bold text-slate-400 block uppercase tracking-wider">{{ $isGu ? 'મારા બુક કરેલ પાસ' : 'My Booked Passes' }}</span>
                    <span class="text-sm sm:text-base font-black text-emerald-400 block mt-0.5">{{ $totalPersonsSum }}
                        {{ $isGu ? 'વ્યક્તિઓ' : 'Persons' }}</span>
                </div>
                <div class="bg-slate-800/70 rounded-lg p-2 border border-slate-700/50">
                    <span
                        class="text-[9px] font-bold text-slate-400 block uppercase tracking-wider">{{ $isGu ? 'પરિવારના સભ્યો' : 'Family Members' }}</span>
                    <span class="text-sm sm:text-base font-black text-primary-300 block mt-0.5">{{ $familyCount }}</span>
                </div>
            </div>
        </div>

        <!-- ================= ACTIVE EVENTS CARDS (Compact) ================= -->
        <div class="bg-white rounded-xl border border-slate-200/90 shadow-2xs p-3 sm:p-3.5 space-y-3">
            @if($activeEvents->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach($activeEvents as $event)
                        @php
                            // Specifically check for pass registrations (excluding candidate/student form entries)
                            $alreadyRegistered = $myRegistrations->where('event_id', $event->id)->filter(function ($r) use ($event) {
                                if ($event->event_type === 'inam_vitaran' && !empty($r->form_data['student_name'])) {
                                    return false;
                                }
                                if ($event->event_type === 'yuva_melo' && (!empty($r->form_data['surname']) || !empty($r->form_data['qualification']))) {
                                    return false;
                                }
                                return true;
                            })->first();

                            $isPassFeeRequired = (float) ($event->pass_fee ?? 0) > 0;
                            $hasPaidPass = $alreadyRegistered && (!$isPassFeeRequired || ($alreadyRegistered->payment_status === 'paid'));

                            $eventTypeBadge = match ($event->event_type ?? 'normal') {
                                'inam_vitaran' => ['bg' => 'bg-amber-50 text-amber-700 border-amber-200', 'label' => ($isGu ? 'ઇનામ વિતરણ' : 'Inam Vitran'), 'icon' => 'academic'],
                                'yuva_melo' => ['bg' => 'bg-purple-50 text-purple-700 border-purple-200', 'label' => ($isGu ? 'યુવા મેળો' : 'Yuva Melo'), 'icon' => 'bolt'],
                                default => ['bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'label' => ($isGu ? 'સામાન્ય કાર્યક્રમ' : 'General Event'), 'icon' => 'sparkles'],
                            };

                            $regPasses = [];
                            if ($hasPaidPass) {
                                $pCount = max(1, (int) ($alreadyRegistered->form_data['person_count'] ?? 1));
                                $tokens = \App\Services\PassTokenService::getOrGenerateTokens($alreadyRegistered);
                                $basePassNo = (int) ($alreadyRegistered->pass_number ?: ($alreadyRegistered->form_data['registration_no'] ?? $alreadyRegistered->id));
                                if ($tokens->isNotEmpty()) {
                                    foreach ($tokens as $idx => $tk) {
                                        $regPasses[] = [
                                            'passNo' => sprintf('%03d', $basePassNo + $idx),
                                            'tokenHash' => $tk->token_hash,
                                            'passCode' => $tk->pass_code,
                                            'qrUrl' => \App\Services\PassTokenService::getQrCodeImageUrl($tk->token_hash),
                                        ];
                                    }
                                } else {
                                    for ($i = 0; $i < $pCount; $i++) {
                                        $regPasses[] = [
                                            'passNo' => sprintf('%03d', $basePassNo + $i),
                                            'tokenHash' => '',
                                            'passCode' => '',
                                            'qrUrl' => '',
                                        ];
                                    }
                                }
                            }

                            // Registration Form Check & Last Date Deadline
                            $hasForm = (bool) ($event->has_registration_form ?? $event->registration_option);
                            if (in_array($event->event_type ?? 'normal', ['inam_vitaran', 'yuva_melo'])) {
                                $hasForm = true;
                            }
                            $isFormDeadlinePassed = !empty($event->registration_end_date) && now()->toDateString() > \Carbon\Carbon::parse($event->registration_end_date)->toDateString();
                        @endphp
                        <div onclick="window.location.href='{{ route('event.details', $event->id) }}'"
                            class="bg-white rounded-xl border border-slate-200/90 p-3 shadow-2xs hover:shadow-md hover:border-primary-400 transition-all flex flex-col justify-between space-y-2.5 group cursor-pointer">

                            <div class="space-y-2">
                                <!-- Event Header: Type Badge (Left) & Pass Fee (Top-Right Corner) -->
                                <div class="flex items-center justify-between gap-2">
                                                <span
                                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[10.5px] font-black uppercase tracking-wider border {{ $eventTypeBadge['bg'] }}">
                                                    @if($eventTypeBadge['icon'] === 'academic')
                                                        <svg class="w-3.5 h-3.5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
                                                    @elseif($eventTypeBadge['icon'] === 'bolt')
                                                        <svg class="w-3.5 h-3.5 text-purple-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                                    @else
                                                        <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                                                    @endif
                                                    <span>{{ $eventTypeBadge['label'] }}</span>
                                                </span>

                                                <!-- Top Right Pass Fee -->
                                                <div class="text-right shrink-0">
                                                    <span
                                                        class="text-[9px] font-extrabold text-slate-400 uppercase tracking-wider block leading-tight">{{ $isGu ? 'પાસ ફી' : 'Pass Fee' }}</span>
                                                    <span
                                                        class="text-xs font-black {{ $event->pass_fee > 0 ? 'text-primary-600' : 'text-emerald-600' }}">
                                                        {{ $event->pass_fee > 0 ? '₹' . number_format($event->pass_fee, 0) : ($isGu ? 'મફત પ્રવેશ' : 'Free Entry') }}
                                                    </span>
                                                </div>
                                            </div>

                                            <h4 class="text-sm font-black text-slate-900 group-hover:text-primary-600 transition-colors line-clamp-1"
                                                title="{{ $event->title }}">
                                                {{ $event->title }}
                                            </h4>

                                            <!-- Date & Venue -->
                                            <div class="bg-slate-50 p-2 rounded-lg border border-slate-100 space-y-1 text-xs text-slate-700">
                                                <div class="flex items-center gap-1.5 font-bold text-slate-800">
                                                    <svg class="w-3.5 h-3.5 text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                    <span>{{ date('d M, Y', strtotime($event->date)) }}</span>
                                                    @if($event->time)
                                                        <span
                                                            class="text-slate-400 font-normal">({{ date('h:i A', strtotime($event->time)) }})</span>
                                                    @endif
                                                </div>
                                                <div class="flex items-center gap-1.5 text-[11px] text-slate-500 font-medium truncate"
                                                    title="{{ $event->venue }}">
                                                    <svg class="w-3.5 h-3.5 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                    <span
                                                        class="truncate">{{ $event->venue ?: ($isGu ? 'સ્થળ જાહેર થશે' : 'Venue TBA') }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Bottom Action Area -->
                                        <div class="pt-1.5 border-t border-slate-100 flex items-center justify-between gap-2 flex-wrap" onclick="event.stopPropagation()">
                                            <!-- Form Action / Last Date Expired Badge -->
                                            @if($hasForm)
                                                @if($isFormDeadlinePassed)
                                                    <div class="flex items-center gap-1 text-[10.5px] font-bold text-rose-700 bg-rose-50 border border-rose-200/90 px-2 py-1 rounded-lg">
                                                        <svg class="w-3.5 h-3.5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                        <span>{{ $isGu ? 'છેલ્લી તારીખ:' : 'Last Date:' }} {{ date('d M, Y', strtotime($event->registration_end_date)) }}</span>
                                                        <span class="text-[9px] bg-rose-600 text-white font-black px-1.5 py-0.5 rounded ml-0.5">{{ $isGu ? 'પૂર્ણ' : 'Closed' }}</span>
                                                    </div>
                                                @else
                                                    <a href="{{ route('member.events.register_form', $event->id) }}"
                                                        onclick="event.stopPropagation()"
                                                        class="px-2.5 py-1.5 bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs rounded-lg shadow-xs transition-colors inline-flex items-center gap-1.5 cursor-pointer shrink-0">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                        <span>{{ $isGu ? 'ફોર્મ ભરો' : 'Fill Form' }}</span>
                                                        <span>&rarr;</span>
                                                    </a>
                                                @endif
                                            @else
                                                <div></div>
                                            @endif

                                            <!-- Pass Booking / View Pass Button -->
                                            @if($hasPaidPass)
                                                <button type="button"
                                                    @click.stop="openPassModal({{ json_encode(['id' => $event->id, 'title' => $event->title, 'date' => date('d-M-Y', strtotime($event->date)), 'time' => $event->time ? date('h:i A', strtotime($event->time)) : '', 'venue' => $event->venue, 'url' => route('event.details', $event->id)]) }}, {{ json_encode($regPasses) }}, '{{ addslashes($userName) }}')"
                                                    class="px-3 py-1.5 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-lg shadow-xs transition-colors inline-flex items-center gap-1.5 cursor-pointer shrink-0">
                                                    <svg class="w-3.5 h-3.5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                                                    <span>{{ $isGu ? 'પાસ જુઓ (' . count($regPasses) . ')' : 'View Pass (' . count($regPasses) . ')' }}</span>
                                                    <span>&rarr;</span>
                                                </button>
                                            @elseif($alreadyRegistered && !$hasPaidPass)
                                                <a href="{{ route('event.details', $event->id) }}"
                                                    onclick="event.stopPropagation()"
                                                    class="px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs rounded-lg shadow-xs transition-colors inline-flex items-center gap-1.5 cursor-pointer shrink-0">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                                    <span>{{ $isGu ? 'ચૂકવણી બાકી (પે કરો)' : 'Pay Now' }}</span>
                                                    <span>&rarr;</span>
                                                </a>
                                            @else
                                                <a href="{{ route('event.details', $event->id) }}"
                                                    onclick="event.stopPropagation()"
                                                    class="px-3 py-1.5 bg-primary-500 hover:bg-primary-600 text-white font-bold text-xs rounded-lg shadow-xs transition-colors inline-flex items-center gap-1.5 cursor-pointer shrink-0">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                                                    <span>{{ $isGu ? 'પાસ બુક કરો' : 'Book Pass' }}</span>
                                                    <span>&rarr;</span>
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                    @endforeach
                        </div>
            @else
                    <!-- Empty State for Active Events -->
                    <div class="text-center py-8 bg-slate-50/70 rounded-lg border border-slate-100 p-4 space-y-1.5">
                        <svg class="w-8 h-8 text-slate-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <h4 class="text-xs font-black text-slate-800">
                            {{ $isGu ? 'હાલમાં કોઈ સક્રિય કાર્યક્રમ ઉપલબ્ધ નથી' : 'No active events currently scheduled' }}
                        </h4>
                        <p class="text-[10px] text-slate-400 font-medium">
                            {{ $isGu ? 'નવા કાર્યક્રમો જાહેર થતાં જ અહીં દેખાશે.' : 'Upcoming community events will appear here once announced.' }}
                        </p>
                    </div>
                @endif
            </div>

            <!-- ================= VIEW PASSES MODAL (TELEPORTED TO BODY) ================= -->
            <template x-teleport="body">
                <div x-show="showPassModal"
                    class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-950/70 backdrop-blur-md"
                    x-transition x-cloak>
                    <div @click.away="showPassModal = false"
                        class="bg-white rounded-3xl border border-slate-100 shadow-2xl max-w-2xl w-full max-h-[90vh] flex flex-col overflow-hidden relative">

                        <!-- Modal Header -->
                        <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between shrink-0">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-9 h-9 rounded-xl bg-primary-600/30 border border-primary-500/40 text-primary-400 flex items-center justify-center">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                                </div>
                                <div>
                                    <h3 class="text-sm font-extrabold flex items-center gap-2">
                                        <span>{{ $isGu ? 'ઇવેન્ટ પ્રવેશ પાસ' : 'Event Entry Passes' }}</span>
                                        <span class="text-[10px] bg-primary-500 text-white font-black px-2 py-0.5 rounded-full"
                                            x-text="activePasses.length + ' Passes'"></span>
                                    </h3>
                                    <p class="text-[11px] text-slate-400 font-medium truncate max-w-[280px] sm:max-w-md"
                                        x-text="activeEvent?.title"></p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" @click="showPassModal = false"
                                    class="w-7 h-7 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-colors text-xs font-bold cursor-pointer"
                                    title="Close">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </div>

                        <!-- Modal Scrollable Content containing all passes -->
                        <div class="p-4 sm:p-6 overflow-y-auto space-y-6 bg-slate-50 flex-1">
                            <template x-for="(pObj, idx) in activePasses" :key="idx">
                                <div class="relative bg-white rounded-2xl border-2 border-slate-900 shadow-md overflow-hidden text-slate-900 print-pass-member-item transition-all hover:shadow-xl"
                                    :id="'dashboard-pass-card-' + idx"
                                    :data-pass-no="typeof pObj === 'object' ? pObj.passNo : pObj"
                                    :data-qr-url="typeof pObj === 'object' ? pObj.qrUrl : ''"
                                    :data-event-title="activeEvent?.title || ''" data-mandal="Shree Satwara Gnati Mandal, Ahmedabad"
                                    :data-date="(activeEvent?.date || '') + (activeEvent?.time ? ' | ' + activeEvent?.time : '')"
                                    :data-venue="activeEvent?.venue || ''" :data-attendee="activeAttendee || ''"
                                    :data-member-code="activeMemberId || ''" data-logo="{{ $logoUrl }}">

                                    <!-- Left & Right Ticket Notches -->
                                    <div class="absolute -left-3 top-1/2 -translate-y-1/2 w-6 h-6 rounded-full bg-slate-50 border-2 border-slate-900 z-20"></div>
                                    <div class="absolute -right-3 top-1/2 -translate-y-1/2 w-6 h-6 rounded-full bg-slate-50 border-2 border-slate-900 z-20"></div>

                                    <!-- Top Bar Header -->
                                    <div class="bg-gradient-to-r from-slate-950 via-slate-900 to-indigo-950 text-white px-5 py-2.5 flex items-center justify-between text-[11px] font-black uppercase tracking-wider">
                                        <span class="flex items-center gap-2 truncate">
                                            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                                            <span x-text="activeAttendee"></span>
                                            <span class="text-indigo-300 font-mono" x-show="activeMemberId" x-text="'(' + activeMemberId + ')'"></span>
                                        </span>
                                        <span class="px-2.5 py-0.5 rounded-full bg-amber-400/20 border border-amber-400/40 text-amber-300 text-[10px] font-black shrink-0 tracking-widest">
                                            VERIFIED PASS #<span x-text="idx + 1"></span>
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
                                            <div class="text-base sm:text-lg font-black text-slate-950 leading-tight tracking-tight break-words"
                                                x-text="activeEvent?.title">
                                            </div>
                                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-xl bg-slate-100 text-slate-800 text-xs font-bold border border-slate-200">
                                                <svg class="w-3.5 h-3.5 text-indigo-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                <span>{{ $isGu ? 'તારીખ:' : 'Date:' }}</span>
                                                <span x-text="activeEvent?.date" class="font-extrabold text-slate-950"></span>
                                                <span x-show="activeEvent?.time" class="text-slate-400">|</span>
                                                <span x-show="activeEvent?.time" x-text="activeEvent?.time" class="font-extrabold text-indigo-700"></span>
                                            </div>
                                        </div>

                                        <!-- Right: Anti-Fraud QR Code & Pass Number Badge -->
                                        <div class="shrink-0 flex items-center gap-3 bg-slate-50 border-2 border-slate-900 p-2 rounded-2xl shadow-xs">
                                            <template x-if="typeof pObj === 'object' && pObj.qrUrl">
                                                <div class="w-18 h-18 sm:w-20 sm:h-20 bg-white border border-slate-300 rounded-xl p-1 flex items-center justify-center shrink-0">
                                                    <img :src="pObj.qrUrl" alt="QR Gate Pass" class="w-full h-full object-contain">
                                                </div>
                                            </template>
                                            <div class="text-center px-2">
                                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block">PASS NO</span>
                                                <span class="text-2xl font-black text-indigo-950 block mt-0.5 tracking-wider"
                                                    x-text="typeof pObj === 'object' ? pObj.passNo : pObj"></span>
                                                <template x-if="typeof pObj === 'object' && pObj.passCode">
                                                    <span class="inline-block mt-1 text-[8px] font-mono font-bold text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200"
                                                        x-text="pObj.passCode"></span>
                                                </template>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Bottom Location Strip -->
                                    <div class="border-t-2 border-dashed border-slate-200 bg-slate-50 px-5 py-2.5 text-xs font-bold text-slate-800 flex items-center justify-between gap-2">
                                        <span class="flex items-center gap-1.5 min-w-0">
                                            <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            <span class="truncate"><strong>{{ $isGu ? 'સ્થળ / સરનામું:' : 'Location / Venue:' }}</strong> <span x-text="activeEvent?.venue"></span></span>
                                        </span>
                                        <div class="flex items-center gap-2 shrink-0">
                                            <span class="text-[10px] text-slate-400 font-mono font-semibold uppercase tracking-widest">GATE SCANNER VALID</span>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Modal Footer -->
                        <div
                            class="px-6 py-3 bg-white border-t border-slate-100 flex flex-wrap items-center justify-between gap-3 shrink-0">
                            <span class="text-[11px] text-slate-400 font-medium inline-flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                {{ $isGu ? 'કૃપા કરીને કાર્યક્રમ સ્થળે પ્રવેશ વખતે આ પાસ દર્શાવો.' : 'Please present this pass at the event entrance.' }}</span>
                            <div class="flex items-center gap-2">
                                <template x-if="activeEvent?.url">
                                    <a :href="activeEvent.url"
                                        class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs rounded-xl shadow-xs transition-colors cursor-pointer inline-flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        <span>{{ $isGu ? 'વધુ પાસ ખરીદો' : 'Buy More Passes' }}</span>
                                    </a>
                                </template>
                                <button type="button" @click="showPassModal = false"
                                    class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-colors cursor-pointer">
                                    {{ $isGu ? 'બંધ કરો' : 'Close' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

        </div>
@endsection

@push('scripts')
    <script>
        /* ===== MEMBER DASHBOARD PASS PDF DOWNLOAD ===== */

        function _renderMemberPassHtmlCard(passData) {
            const logoSrc = passData.logo || '/logo.png';
            const mandal = passData.mandal || 'Shree Satwara Gnati Mandal, Ahmedabad';
            const title = passData.title || '';
            const date = passData.date || '';
            const passNo = passData.passNo || '001';
            const venue = passData.venue || '';
            const attendee = passData.attendee || '';
            const memberCode = passData.memberCode || '';
            const topNameWithCode = attendee ? (attendee + (memberCode ? ' (' + memberCode + ')' : '')) : 'SHREE SATWARA GNATI MANDAL, AHMEDABAD';

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

        function _openMemberPassesWindow(cardsHtml, title) {
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

        function downloadAllPassesMember() {
            const cards = document.querySelectorAll('.print-pass-member-item');
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
                html += _renderMemberPassHtmlCard(data);
            });
            _openMemberPassesWindow(html, 'Event Entry Passes');
        }

        function downloadSinglePassMember(cardId) {
            const card = document.getElementById(cardId);
            if (!card) { console.error('Pass card not found:', cardId); return; }
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
            _openMemberPassesWindow(_renderMemberPassHtmlCard(data), 'Event Entry Pass - ' + data.passNo);
        }
    </script>
@endpush