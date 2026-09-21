@extends('layouts.member')

@section('page_title', __('messages.events'))

@section('content')
    @php
        $isGu = (app()->getLocale() === 'gu');
        $logoUrl = App\Models\Setting::get('website_logo') ? asset('storage/' . App\Models\Setting::get('website_logo')) : asset('logo.png');
        $userName = auth()->user() ? auth()->user()->name : 'Member';
        $memberCode = auth()->user() ? sprintf('#%05d', auth()->user()->id) : '-';
    @endphp

    <div class="space-y-5" x-data="{ 
        currentTab: 'all',
        searchQuery: '{{ addslashes(request('search', '')) }}',
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

        <!-- Header Controls: Search Filter (Left) & Tab Navigation (Right) -->
        <div class="bg-white rounded-2xl border border-slate-100 p-3 sm:p-3.5 shadow-2xs">
            <div class="flex items-center justify-between gap-3 flex-wrap">
                <!-- Search Filter (Left) -->
                <div class="flex-1 min-w-[200px] max-w-md">
                    <form method="GET" action="{{ route('member.events.index') }}" class="relative flex items-center">
                        <input type="text" name="search" value="{{ request('search') }}" x-model="searchQuery"
                            placeholder="{{ $isGu ? 'કાર્યક્રમ શોધો (નામ, સ્થળ)...' : 'Search events by name, venue...' }}"
                            class="w-full px-3.5 pr-8 py-1.5 bg-slate-50 hover:bg-slate-100/70 focus:bg-white border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all">
                        <template x-if="searchQuery">
                            <button type="button"
                                @click="searchQuery = ''; window.location.href = '{{ route('member.events.index') }}';"
                                class="absolute right-2.5 text-slate-400 hover:text-slate-600 text-xs font-bold p-0.5 cursor-pointer">
                                ✕
                            </button>
                        </template>
                    </form>
                </div>

                <!-- Tab Buttons (Right) -->
                <div class="flex items-center gap-1.5 bg-slate-100/90 p-1 rounded-xl border border-slate-200/80 shrink-0">
                    <button type="button" @click="currentTab = 'all'"
                        :class="currentTab === 'all' ? 'bg-white text-slate-900 shadow-xs font-black' : 'text-slate-600 hover:text-slate-900 font-bold'"
                        class="px-3 py-1.5 rounded-lg text-xs transition-all flex items-center gap-1.5 cursor-pointer">
                        <svg class="w-3.5 h-3.5 text-slate-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>{{ $isGu ? 'બધા કાર્યક્રમો' : 'All Events' }}</span>
                        <span class="text-[10px] px-1.5 py-0.2 rounded-full font-bold"
                            :class="currentTab === 'all' ? 'bg-slate-100 text-slate-800' : 'bg-slate-200/80 text-slate-600'">
                            {{ $events->total() ?? $events->count() }}
                        </span>
                    </button>

                    <button type="button" @click="currentTab = 'my_passes'"
                        :class="currentTab === 'my_passes' ? 'bg-primary-600 text-white shadow-xs font-black' : 'text-slate-600 hover:text-slate-900 font-bold'"
                        class="px-3 py-1.5 rounded-lg text-xs transition-all flex items-center gap-1.5 cursor-pointer">
                        <svg class="w-3.5 h-3.5 text-primary-200 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                        <span>{{ $isGu ? 'મારા પાસ' : 'My Passes' }}</span>
                        @if(isset($myRegistrations) && $myRegistrations->isNotEmpty())
                            <span class="text-[10px] px-1.5 py-0.2 rounded-full font-black"
                                :class="currentTab === 'my_passes' ? 'bg-white text-primary-700' : 'bg-primary-500 text-white'">
                                {{ $myRegistrations->count() }}
                            </span>
                        @endif
                    </button>
                </div>
            </div>
        </div>

        <!-- ================= TAB 2: MY REGISTERED EVENTS & PASSES ================= -->
        <div x-show="currentTab === 'my_passes'" x-cloak class="space-y-4">
            @if(isset($myRegistrations) && $myRegistrations->isNotEmpty())
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                    @foreach($myRegistrations as $myReg)
                        @php
                            $rEvent = $myReg->event;
                            $pCount = max(1, (int) ($myReg->form_data['person_count'] ?? 1));
                            $regPasses = [];
                            if ($rEvent) {
                                $tokens = \App\Services\PassTokenService::getOrGenerateTokens($myReg);
                                $basePassNo = (int) ($myReg->pass_number ?: ($myReg->form_data['registration_no'] ?? $myReg->id));
                                foreach ($tokens as $idx => $tk) {
                                    $regPasses[] = [
                                        'passNo' => sprintf('%03d', $basePassNo + $idx),
                                        'tokenHash' => $tk->token_hash,
                                        'passCode' => $tk->pass_code,
                                        'qrUrl' => \App\Services\PassTokenService::getQrCodeImageUrl($tk->token_hash),
                                    ];
                                }
                            }
                            $cardAttendee = $myReg->form_data['full_name'] ?? $userName;
                        @endphp
                        @if($rEvent)
                            <div class="bg-white border border-slate-200 hover:border-primary-400 rounded-xl p-3 flex flex-col justify-between space-y-2.5 shadow-2xs hover:shadow-xs transition-all"
                                x-show="!searchQuery || '{{ strtolower(addslashes($rEvent->title . ' ' . $rEvent->venue)) }}'.includes(searchQuery.toLowerCase())">

                                <div class="space-y-1.5">
                                    <a href="{{ route('event.details', $rEvent->id) }}" class="block">
                                        <h3
                                            class="text-xs sm:text-sm font-extrabold text-slate-900 hover:text-primary-600 transition-colors line-clamp-1">
                                            {{ $rEvent->title }}
                                        </h3>
                                    </a>

                                    <div
                                        class="px-2.5 py-1.5 bg-slate-50 border border-slate-100 rounded-lg flex items-center justify-between text-[11px]">
                                        <span class="font-bold text-slate-600 inline-flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                            {{ $isGu ? 'પાસ:' : 'Passes:' }}
                                        </span>
                                        <span class="font-black text-primary-700">{{ $pCount }}
                                            {{ $isGu ? 'વ્યક્તિ' : 'Person(s)' }}</span>
                                        @if(($rEvent->pass_fee ?? 0) > 0)
                                            <span class="text-slate-300">|</span>
                                            <span
                                                class="font-extrabold text-emerald-600">₹{{ number_format($myReg->payment_amount ?? ($rEvent->pass_fee * $pCount)) }}</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="pt-2 border-t border-slate-100 flex items-center gap-1.5">
                                    <button type="button"
                                        @click="openPassModal({{ json_encode(['id' => $rEvent->id, 'title' => $rEvent->title, 'date' => date('d-M-Y', strtotime($rEvent->date)), 'time' => $rEvent->time ? date('h:i A', strtotime($rEvent->time)) : '', 'venue' => $rEvent->venue, 'url' => route('event.details', $rEvent->id)]) }}, {{ json_encode($regPasses) }}, '{{ addslashes($cardAttendee) }}')"
                                        class="flex-1 py-1.5 px-2 bg-primary-600 hover:bg-primary-700 active:scale-95 text-white text-[11px] font-extrabold rounded-lg shadow-2xs transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                                        <span>{{ $isGu ? 'પાસ જુઓ (' . $pCount . ')' : 'View Passes (' . $pCount . ')' }}</span>
                                    </button>
                                    <a href="{{ route('event.details', $rEvent->id) }}"
                                        class="py-1.5 px-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] font-bold rounded-lg transition-colors shrink-0">
                                        {{ $isGu ? 'વિગતો →' : 'Details →' }}
                                    </a>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @else
                <div class="bg-white border border-slate-100 rounded-2xl p-10 text-center space-y-3 shadow-2xs">
                    <div class="w-12 h-12 rounded-2xl bg-primary-50 text-primary-600 flex items-center justify-center mx-auto">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                    </div>
                    <h3 class="text-sm font-black text-slate-800">
                        {{ $isGu ? 'હજી સુધી કોઈ પાસ બુક કરેલા નથી' : 'No Event Passes Registered Yet' }}
                    </h3>
                    <p class="text-xs text-slate-500 max-w-sm mx-auto">
                        {{ $isGu ? 'તમે આગામી કાર્યક્રમો માટે રજીસ્ટ્રેશન કરી પ્રવેશ પાસ મેળવી શકો છો.' : 'You have not registered for any events yet. Browse events and book your entry passes.' }}
                    </p>
                    <button type="button" @click="currentTab = 'all'"
                        class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs rounded-xl shadow-xs transition-colors cursor-pointer inline-flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>{{ $isGu ? 'કાર્યક્રમો જુઓ' : 'Browse All Events' }}</span>
                    </button>
                </div>
            @endif
        </div>

        <!-- ================= TAB 1: ALL COMMUNITY EVENTS ================= -->
        <div x-show="currentTab === 'all'" class="space-y-4">
            <!-- Events Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse($events as $event)
                    @php
                        $thisReg = isset($myRegistrations) ? $myRegistrations->where('event_id', $event->id)->filter(function($r) use ($event) {
                            if ($event->event_type === 'inam_vitaran' && !empty($r->form_data['student_name'])) {
                                return false;
                            }
                            if ($event->event_type === 'yuva_melo' && (!empty($r->form_data['surname']) || !empty($r->form_data['qualification']))) {
                                return false;
                            }
                            return true;
                        })->first() : null;

                        $isPassFeeRequired = (float)($event->pass_fee ?? 0) > 0;
                        $hasPaidPass = $thisReg && (!$isPassFeeRequired || ($thisReg->payment_status === 'paid'));
                        $isRegistered = $hasPaidPass;
                        $pCount = $hasPaidPass ? max(1, (int) ($thisReg->form_data['person_count'] ?? 1)) : 1;
                        $cardPasses = [];
                        if ($hasPaidPass) {
                            $tokens = \App\Services\PassTokenService::getOrGenerateTokens($thisReg);
                            $basePassNo = (int) ($thisReg->pass_number ?: ($thisReg->form_data['registration_no'] ?? $thisReg->id));
                            if ($tokens->isNotEmpty()) {
                                foreach ($tokens as $idx => $tk) {
                                    $cardPasses[] = [
                                        'passNo' => sprintf('%03d', $basePassNo + $idx),
                                        'tokenHash' => $tk->token_hash,
                                        'passCode' => $tk->pass_code,
                                        'qrUrl' => \App\Services\PassTokenService::getQrCodeImageUrl($tk->token_hash),
                                    ];
                                }
                            } else {
                                for ($pi = 0; $pi < $pCount; $pi++) {
                                    $cardPasses[] = [
                                        'passNo' => sprintf('%03d', $basePassNo + $pi),
                                        'tokenHash' => '',
                                        'passCode' => '',
                                        'qrUrl' => '',
                                    ];
                                }
                            }
                        }
                        $attendeeStr = $thisReg->form_data['full_name'] ?? $userName;
                    @endphp
                    <div class="group bg-white rounded-xl border border-slate-100 shadow-2xs flex flex-col overflow-hidden hover:shadow-md transition-all"
                        x-show="!searchQuery || '{{ strtolower(addslashes($event->title . ' ' . $event->venue)) }}'.includes(searchQuery.toLowerCase())">
                        <!-- Event Banner (Clickable to website details) -->
                        <a href="{{ route('event.details', $event->id) }}"
                            class="relative h-36 sm:h-40 overflow-hidden block bg-white">
                            {{-- Always-visible Red Background + Calendar Icon (base layer) --}}
                            <div
                                style="position:absolute; inset:0; display:flex; flex-direction:column; align-items:center; justify-content:center; padding: 14px; background: linear-gradient(135deg, #dc2626 0%, #e11d48 60%, #be123c 100%);">
                                {{-- Dot grid texture --}}
                                <div
                                    style="position:absolute; inset:0; background-image: radial-gradient(circle, rgba(255,255,255,0.12) 1px, transparent 1px); background-size: 12px 12px; pointer-events:none;">
                                </div>

                                {{-- Calendar card --}}
                                <div
                                    style="position:relative; display:flex; flex-direction:column; align-items:center; gap:6px;">
                                    <div
                                        style="width:54px; height:56px; border-radius:10px; background:#fff; overflow:hidden; display:flex; flex-direction:column; box-shadow: 0 4px 14px rgba(0,0,0,0.25);">
                                        {{-- Month header --}}
                                        <div
                                            style="background: linear-gradient(90deg, #dc2626, #e11d48); padding: 3px 0; text-align:center; flex-shrink:0;">
                                            <span
                                                style="font-size:9.5px; font-weight:900; color:#fff; letter-spacing:0.12em; text-transform:uppercase; display:block; line-height:1;">
                                                {{ date('M', strtotime($event->date)) }}
                                            </span>
                                        </div>
                                        {{-- Day number --}}
                                        <div
                                            style="flex:1; display:flex; flex-direction:column; align-items:center; justify-content:center;">
                                            <span style="font-size:19px; font-weight:900; color:#1e293b; line-height:1;">
                                                {{ date('d', strtotime($event->date)) }}
                                            </span>
                                        </div>
                                    </div>
                                    <span
                                        style="font-size:8px; font-weight:800; text-transform:uppercase; letter-spacing:0.08em; color:#fff; background:rgba(0,0,0,0.35); border:1px solid rgba(255,255,255,0.15); padding:1.5px 6px; border-radius:999px; white-space:nowrap;">Community
                                        Event</span>
                                </div>
                            </div>

                            {{-- Actual image on top (covers calendar when loaded successfully) --}}
                            @if(!empty($event->banner_path))
                                <img class="absolute inset-0 w-full h-full object-contain bg-white group-hover:scale-105 transition-transform duration-500"
                                    src="{{ str_starts_with($event->banner_path, 'http') ? $event->banner_path : asset('storage/' . $event->banner_path) }}"
                                    alt="{{ $event->title }}" onerror="this.style.display='none'">
                            @endif

                            <div class="absolute top-2.5 left-2.5 z-10 flex items-center gap-1.5 flex-wrap">
                                @if($event->date < now()->toDateString())
                                    <span
                                        class="text-[10px] sm:text-[11px] font-black text-slate-600 bg-white/95 backdrop-blur-sm border border-slate-200 px-2.5 py-1 rounded-lg uppercase tracking-wider shadow-xs">{{ __('messages.passed') }}</span>
                                @else
                                    <span
                                        class="text-[10px] sm:text-[11px] font-black text-emerald-700 bg-white/95 backdrop-blur-sm border border-emerald-200 px-2.5 py-1 rounded-lg uppercase tracking-wider shadow-xs">{{ __('messages.upcoming') }}</span>
                                @endif

                                @if(($event->event_type ?? 'normal') === 'inam_vitaran')
                                    <span
                                        class="inline-flex items-center gap-1 text-[10px] sm:text-[11px] font-black text-amber-800 bg-amber-50/95 backdrop-blur-sm border border-amber-300 px-2.5 py-1 rounded-lg uppercase tracking-wider shadow-xs">
                                        <svg class="w-3.5 h-3.5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
                                        {{ __('messages.inam_vitaran') }}
                                    </span>
                                @elseif(($event->event_type ?? 'normal') === 'yuva_melo')
                                    <span
                                        class="inline-flex items-center gap-1 text-[10px] sm:text-[11px] font-black text-purple-800 bg-purple-50/95 backdrop-blur-sm border border-purple-300 px-2.5 py-1 rounded-lg uppercase tracking-wider shadow-xs">
                                        <svg class="w-3.5 h-3.5 text-purple-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                        {{ __('messages.yuva_melo') }}
                                    </span>
                                @endif
                            </div>
                        </a>

                        <!-- Event Details -->
                        <div class="p-3.5 flex-grow flex flex-col justify-between space-y-3">
                            <div class="space-y-1.5">
                                <div class="flex items-center justify-between text-xs font-bold text-slate-500 flex-wrap gap-1">
                                    <span class="inline-flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        {{ date('d-M-Y', strtotime($event->date)) }}
                                    </span>
                                    @if(!empty($event->time))
                                        <span class="text-slate-400 inline-flex items-center gap-1">
                                            <svg class="w-3 h-3 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            {{ date('h:i A', strtotime($event->time)) }}
                                        </span>
                                    @endif
                                </div>

                                <a href="{{ route('event.details', $event->id) }}" class="block">
                                    <h3
                                        class="text-sm sm:text-base font-black text-slate-900 line-clamp-1 hover:text-primary-600 transition-colors">
                                        {{ $event->title }}</h3>
                                </a>
                                @php
                                    $cleanDesc = preg_replace('/<span class="ql-ui"[^>]*>.*?<\/span>/i', '', $event->description);
                                    $cleanDesc = strip_tags(str_replace(['<br>', '</p>', '</li>', '</div>'], ' ', $cleanDesc));
                                    $cleanDesc = trim(preg_replace('/\s+/', ' ', $cleanDesc));
                                @endphp
                                <a href="{{ route('event.details', $event->id) }}" class="block">
                                    <p class="text-xs text-slate-500 line-clamp-2 leading-relaxed break-words">
                                        {{ \Illuminate\Support\Str::limit($cleanDesc, 80, '...') }}
                                    </p>
                                </a>
                            </div>

                            <!-- Action and Status -->
                            <div class="pt-2 mt-auto border-t border-slate-100 space-y-1.5">
                                <div class="flex items-center justify-between gap-1.5 flex-wrap">
                                    <!-- Registration Status Badge -->
                                    <div>
                                        @if($hasPaidPass)
                                            <button type="button"
                                                @click.stop="openPassModal({{ json_encode(['id' => $event->id, 'title' => $event->title, 'date' => date('d-M-Y', strtotime($event->date)), 'time' => $event->time ? date('h:i A', strtotime($event->time)) : '', 'venue' => $event->venue, 'url' => route('event.details', $event->id)]) }}, {{ json_encode($cardPasses) }}, '{{ addslashes($attendeeStr) }}')"
                                                class="inline-flex items-center gap-1 px-3 py-1 text-xs font-black text-white bg-slate-900 hover:bg-slate-800 rounded-lg uppercase tracking-wide cursor-pointer transition-colors shadow-2xs">
                                                <svg class="w-3.5 h-3.5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                                                <span>{{ $isGu ? 'પાસ જુઓ (' . count($cardPasses) . ')' : 'View Pass (' . count($cardPasses) . ')' }}</span>
                                            </button>
                                        @elseif($thisReg && !$hasPaidPass)
                                            <a href="{{ route('event.details', $event->id) }}"
                                                class="inline-flex items-center gap-1 px-3 py-1 text-xs font-bold text-amber-700 bg-amber-50 border border-amber-300 rounded-lg uppercase tracking-wide hover:bg-amber-100">
                                                <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                                <span>{{ $isGu ? 'ચૂકવણી બાકી (પે કરો)' : 'Pay Now' }}</span>
                                            </a>
                                        @elseif(($event->event_type ?? 'normal') === 'normal' || !($event->has_registration_form || $event->registration_option))
                                            <span
                                                class="inline-flex items-center px-3 py-1 text-xs font-bold text-slate-600 bg-slate-100 border border-slate-200 rounded-lg uppercase tracking-wide">{{ __('messages.open_entry') }}</span>
                                        @else
                                            <span
                                                class="inline-flex items-center px-3 py-1 text-xs font-bold text-slate-600 bg-slate-100 border border-slate-200 rounded-lg uppercase tracking-wide">{{ __('messages.not_registered') }}</span>
                                        @endif
                                    </div>

                                    @if(($event->event_type ?? 'normal') === 'inam_vitaran' || ($event->event_type ?? 'normal') === 'yuva_melo')
                                        <a href="{{ route('member.events.register_form', $event->id) }}"
                                            class="inline-flex items-center px-3.5 py-1.5 bg-primary-600 hover:bg-primary-700 active:scale-95 text-white text-xs font-extrabold rounded-lg transition-all gap-1.5 shadow-2xs">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            <span>{{ $isGu ? 'ફોર્મ ભરો' : 'Fill Form' }}</span>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-4 text-center py-16 bg-white border border-slate-100 rounded-xl">
                        <p class="text-xs text-slate-400">{{ __('messages.no_events_listed') }}</p>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($events->hasPages())
                <div class="mt-4">
                    {{ $events->links() }}
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
                                :id="'member-pass-card-' + idx"
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
                                        <span class="truncate"><strong>{{ $isGu ? 'સ્થળ / સરનામું:' : 'Venue:' }}</strong> <span x-text="activeEvent?.venue"></span></span>
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
        /* ===== MEMBER PANEL PASS PDF DOWNLOAD ===== */

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