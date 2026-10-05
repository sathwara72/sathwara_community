@extends('layouts.admin')

@php
    $isGu = (app()->getLocale() === 'gu');
    $totalCount = count($registrations);
    $totalPersonsSum = $registrations->sum(function($r) {
        return (int) ($r->form_data['person_count'] ?? 1);
    });
    $totalFeeCollected = $registrations->where('payment_status', 'paid')->sum('payment_amount');
    $paidCount = $registrations->where('payment_status', 'paid')->count();
@endphp

@section('page_title', $isGu ? 'ઇવેન્ટ રજીસ્ટ્રેશન યાદી - ' . $event->title : 'Pass Registrations - ' . $event->title)

@section('content')
<div class="space-y-4" x-data="{ showDetailsModal: false, selectedRegistration: {}, search: '' }">
    <!-- Summary Stats Bar -->
    <div class="bg-white p-2.5 rounded-xl border border-slate-100 shadow-sm flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-2.5">
        <!-- Badges & Counters -->
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('admin.events.index') }}" 
               class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-extrabold text-xs rounded-xl border border-slate-200 transition-colors inline-flex items-center gap-1.5 shrink-0 shadow-2xs" 
               title="{{ $isGu ? 'ઇવેન્ટ્સ લિસ્ટ પર પાછા જાઓ' : __('messages.back') }}">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>{{ $isGu ? 'પાછા જાઓ' : __('messages.back') }}</span>
            </a>

            <!-- Total Registrations Badge -->
            <div class="px-2.5 py-1.5 bg-slate-100/90 rounded-xl border border-slate-200 text-slate-800 text-xs font-bold inline-flex items-center gap-1.5 whitespace-nowrap shrink-0">
                <span>{{ $isGu ? '📋 કુલ:' : '📋 Total:' }}</span>
                <span class="font-black text-slate-900">{{ $totalCount }}</span>
            </div>

            <!-- Total Persons Attending Badge -->
            <div class="px-2.5 py-1.5 bg-primary-50/90 border border-primary-200/80 rounded-xl text-primary-900 text-xs font-bold inline-flex items-center gap-1.5 shadow-2xs whitespace-nowrap shrink-0">
                <span>{{ $isGu ? '👥 હાજરી:' : '👥 Attending:' }}</span>
                <span class="font-black text-primary-700">{{ $totalPersonsSum }} {{ $isGu ? 'વ્યક્તિઓ' : 'Persons' }}</span>
            </div>

            @if($event->pass_fee > 0)
                <div class="px-2.5 py-1.5 bg-amber-50/90 border border-amber-200/80 rounded-xl text-amber-900 text-xs font-bold inline-flex items-center gap-1.5 shadow-2xs whitespace-nowrap shrink-0">
                    <span>{{ $isGu ? '💰 જમા ફી:' : '💰 Fee Collected:' }}</span>
                    <span class="font-black text-amber-700">₹{{ number_format($totalFeeCollected, 0) }}</span>
                    <span class="text-[10px] text-amber-600 font-semibold">({{ $paidCount }} {{ $isGu ? 'ચૂકવેલ' : 'paid' }})</span>
                </div>
            @endif
        </div>

        <!-- Search input & Export -->
        <div class="flex items-center gap-2 shrink-0 justify-end">
            <div class="relative w-44 sm:w-56">
                <input type="text" x-model="search" placeholder="{{ $isGu ? 'રજીસ્ટ્રેશન, નામ, ફોન શોધો...' : __('messages.search_registrations') }}" 
                       class="text-xs font-semibold pl-8 pr-7 py-1.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:border-primary-500 w-full transition-colors">
                <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <button type="button" x-show="search" @click="search = ''" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-rose-500 font-extrabold text-xs" title="Clear search">
                    &times;
                </button>
            </div>

            <a href="{{ route('admin.events.registrations.export', $event->id) }}" 
               class="px-3.5 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold text-xs rounded-xl border border-emerald-200/60 shadow-xs transition-colors shrink-0 inline-flex items-center gap-1.5 whitespace-nowrap">
                <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>{{ $isGu ? 'એક્સેલ એક્સપોર્ટ' : __('messages.export_excel') }}</span>
            </a>
        </div>
    </div>

    <!-- Registrations Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
        @forelse($registrations as $index => $reg)
            @php
                $fd = $reg->form_data ?? [];
                $memberCode = $reg->user ? sprintf('#%05d', $reg->user->id) : (is_scalar($fd['member_id'] ?? null) ? (string)$fd['member_id'] : '-');
                $rawName = $reg->user ? $reg->user->name : ($fd['full_name'] ?? $fd['student_name'] ?? $fd['first_name'] ?? ($isGu ? 'અતિથિ સહભાગી' : 'Guest Participant'));
                $userName = is_scalar($rawName) ? (string)$rawName : 'Participant';

                $rawEmail = $reg->user ? $reg->user->email : ($fd['email'] ?? null);
                $userEmail = is_scalar($rawEmail) ? (string)$rawEmail : null;

                $rawPhone = $fd['mobile'] ?? $fd['contact_number'] ?? $fd['mobile_no'] ?? ($reg->user ? ($reg->user->memberProfile->phone ?? null) : null);
                $userPhone = is_scalar($rawPhone) ? (string)$rawPhone : null;

                $rawCity = $fd['city'] ?? $fd['area'] ?? $fd['district'] ?? ($reg->user ? ($reg->user->memberProfile->city ?? null) : null);
                if (is_array($rawCity)) {
                    $userCity = implode(', ', array_filter($rawCity, 'is_scalar'));
                } elseif (is_object($rawCity)) {
                    $userCity = $rawCity->name ?? (string)$rawCity;
                } else {
                    $userCity = is_scalar($rawCity) ? (string)$rawCity : null;
                }

                $personCount = (int)($fd['person_count'] ?? 1);
                $passNoFormatted = $reg->pass_number ? sprintf('%03d', $reg->pass_number) : (isset($fd['registration_no']) && is_numeric($fd['registration_no']) ? sprintf('%03d', (int)$fd['registration_no']) : sprintf('%03d', $index + 1));
                $regNo = $passNoFormatted;

                $modalData = [
                    'member_code' => $memberCode,
                    'pass_number' => $passNoFormatted,
                    'user_name' => $userName,
                    'email' => $userEmail ?? '-',
                    'phone' => $userPhone ?? '-',
                    'city' => $userCity ?? '-',
                    'person_count' => $personCount,
                    'payment_status' => $reg->payment_status ?? 'unpaid',
                    'payment_amount' => $reg->payment_amount ?? 0,
                    'payment_id' => $reg->payment_id ?? '-',
                    'transaction_nos' => $reg->transactions->pluck('transaction_no')->implode(', '),
                    'date' => $reg->created_at->format('d-M-Y h:i A'),
                    'form_data' => $fd,
                ];
            @endphp
            <div x-show="!search || 
                         '{{ addslashes(strtolower($userName)) }}'.includes(search.toLowerCase()) || 
                         '{{ addslashes(strtolower($memberCode)) }}'.includes(search.toLowerCase()) || 
                         '{{ addslashes(strtolower($userEmail ?? '')) }}'.includes(search.toLowerCase()) || 
                         '{{ addslashes(strtolower($userPhone ?? '')) }}'.includes(search.toLowerCase()) || 
                         '{{ addslashes(strtolower($userCity ?? '')) }}'.includes(search.toLowerCase())" 
                 class="bg-white border border-slate-200/90 rounded-xl p-3 shadow-2xs hover:shadow-sm hover:border-primary-400 transition-all space-y-2 relative group flex flex-col justify-between">
                
                <div class="space-y-2">
                    <!-- Participant Name & Date Header -->
                    <div class="flex items-center justify-between gap-1.5 border-b border-slate-100 pb-1.5">
                        <h4 class="text-xs font-black text-slate-900 truncate group-hover:text-primary-600 transition-colors" title="{{ $userName }}">
                            {{ $userName }}
                        </h4>
                        <span class="text-[10px] font-bold text-slate-400 shrink-0 whitespace-nowrap">
                            {{ $reg->created_at->format('d-M-Y') }}
                        </span>
                    </div>

                    <!-- Mobile & Email Contact Pill -->
                    <div class="bg-slate-50 p-2 rounded-lg border border-slate-100/80 space-y-0.5 text-[10px]">
                        <div class="flex items-center gap-1.5 font-bold text-slate-800">
                            <span>📞</span>
                            <a href="tel:{{ $userPhone }}" class="hover:text-primary-600 transition-colors">{{ $userPhone ?: '-' }}</a>
                        </div>
                        @if(!empty($userEmail))
                            <div class="flex items-center gap-1.5 text-[9px] text-slate-500 font-medium truncate" title="{{ $userEmail }}">
                                <span>✉️</span>
                                <span class="truncate">{{ $userEmail }}</span>
                            </div>
                        @endif
                    </div>

                    <!-- Persons Attending Pill -->
                    <div class="bg-primary-50/80 px-2.5 py-1.5 rounded-lg border border-primary-100 flex items-center justify-between text-[10px]">
                        <span class="text-[10px] font-bold text-primary-800">
                            👥 {{ $isGu ? 'હાજર વ્યક્તિઓ:' : 'Attending Persons:' }}
                        </span>
                        <span class="font-black text-primary-700 text-xs px-2 py-0.5 bg-white rounded-md border border-primary-200/80 shadow-2xs">
                            {{ $personCount }} {{ $isGu ? 'વ્યક્તિ' : ($personCount > 1 ? 'Persons' : 'Person') }}
                        </span>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-12 text-center text-slate-400 font-medium bg-white rounded-2xl border border-slate-100 shadow-2xs">
                {{ $isGu ? 'હજુ સુધી કોઈ રજીસ્ટ્રેશન થયેલ નથી.' : __('messages.no_registrations_found') }}
            </div>
        @endforelse
    </div>

    <!-- Registration Details Pop-up Modal -->
    <template x-teleport="body">
        <div x-show="showDetailsModal" 
             class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-5 bg-slate-900/60 backdrop-blur-sm"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             x-cloak>
            <div @click.away="showDetailsModal = false" 
                 class="bg-white rounded-2xl border border-slate-100 shadow-2xl max-w-5xl w-full max-h-[90vh] flex flex-col overflow-hidden relative">
                
                <!-- Modal Header -->
                <div class="px-5 py-4 bg-slate-900 text-white flex items-center justify-between shrink-0">
                    <div>
                        <h3 class="text-sm font-extrabold flex items-center gap-2" x-text="selectedRegistration.user_name + ' - ' + '{{ $isGu ? 'સબમિટ વિગત' : __('messages.submitted_details') }}'"></h3>
                        <p class="text-[11px] text-slate-400 font-medium mt-0.5" x-text="'{{ $isGu ? 'નોંધણી તારીખ' : __('messages.registered_date') }}: ' + (selectedRegistration.date || '')"></p>
                    </div>
                    <button type="button" @click="showDetailsModal = false" 
                            class="w-7 h-7 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-colors text-xs font-bold cursor-pointer">
                        ✕
                    </button>
                </div>

                <!-- Modal Content Body -->
                <div class="p-5 space-y-4 overflow-y-auto max-h-[78vh] text-xs">
                    <!-- Member Summary Card -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-slate-50 p-3.5 rounded-xl border border-slate-200/80 text-xs">
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">{{ $isGu ? 'ઈમેલ' : __('messages.applicant_email') }}</span>
                            <span class="font-extrabold text-slate-800 text-xs truncate block" x-text="selectedRegistration.email"></span>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">{{ $isGu ? 'સંપર્ક નંબર' : __('messages.contact_info') }}</span>
                            <span class="font-extrabold text-slate-800 text-xs block" x-text="selectedRegistration.phone"></span>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">{{ $isGu ? 'શહેર' : __('messages.city') }}</span>
                            <span class="font-extrabold text-slate-800 text-xs block" x-text="selectedRegistration.city"></span>
                        </div>
                        <template x-if="selectedRegistration.payment_amount > 0 || selectedRegistration.payment_status === 'paid'">
                            <div>
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">{{ $isGu ? 'ચુકવણી સ્ટેટસ' : 'Payment' }}</span>
                                <span class="font-extrabold text-[10px] uppercase tracking-wider px-2.5 py-0.5 rounded inline-block mt-0.5"
                                      :class="selectedRegistration.payment_status === 'paid' ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-rose-100 text-rose-700 border border-rose-200'"
                                      x-text="'₹' + selectedRegistration.payment_amount + ' (' + (selectedRegistration.payment_status === 'paid' ? '{{ $isGu ? 'ચૂકવેલ' : 'PAID' }}' : '{{ $isGu ? 'બાકી' : 'UNPAID' }}') + ')'"></span>
                            </div>
                        </template>
                        <template x-if="selectedRegistration.transaction_nos">
                            <div>
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">{{ $isGu ? 'વ્યવહાર ID' : 'Transaction ID' }}</span>
                                <span class="font-mono font-extrabold text-slate-800 text-xs block" x-text="selectedRegistration.transaction_nos"></span>
                            </div>
                        </template>
                    </div>

                    <!-- Uploaded Documents & Media Grid -->
                    <template x-if="Object.keys(selectedRegistration.form_data || {}).some(k => k.endsWith('_url') || k.endsWith('_image') || k.endsWith('_photo'))">
                        <div class="space-y-1.5">
                            <h4 class="text-[11px] font-black text-slate-400 uppercase tracking-wider">
                                {{ $isGu ? 'અપલોડ કરેલ દસ્તાવેજો અને ફોટોગ્રાફ્સ' : 'Uploaded Documents & Attachments' }}
                            </h4>
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2.5 bg-slate-50/80 p-3 rounded-xl border border-slate-200/60">
                                <template x-for="(val, key) in (selectedRegistration.form_data || {})" :key="key">
                                    <template x-if="(key.endsWith('_url') || key.endsWith('_image') || key.endsWith('_photo')) && val">
                                        <div class="bg-white p-2 rounded-lg border border-slate-200 shadow-2xs flex items-center gap-2">
                                            <a :href="val" target="_blank" class="block w-10 h-10 shrink-0 overflow-hidden rounded-lg bg-slate-100 border border-slate-200">
                                                <img :src="val" class="w-full h-full object-cover" onerror="this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' fill=\'none\' viewBox=\'0 0 24 24\' stroke=\'%2364748b\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z\'/></svg>'">
                                            </a>
                                            <div class="min-w-0 flex-1">
                                                <span class="text-[9px] font-black text-slate-700 uppercase block truncate" x-text="key.replace('_url', '').replace('_photo', '').replace('_image', '').replace(/_/g, ' ')"></span>
                                                <a :href="val" target="_blank" class="text-[10px] font-bold text-primary-600 hover:underline">
                                                    {{ $isGu ? 'ફાઇલ જુઓ ↗' : 'Open File ↗' }}
                                                </a>
                                            </div>
                                        </div>
                                    </template>
                                </template>
                            </div>
                        </div>
                    </template>

                    <!-- Form Fields Grid (4 Columns) -->
                    <div class="space-y-1.5">
                        <h4 class="text-[11px] font-black text-slate-400 uppercase tracking-wider">
                            {{ $isGu ? 'ઉમેદવાર ફોર્મ વિગતો' : __('messages.candidate_form_details') }}
                        </h4>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-2.5">
                            <template x-for="(val, key) in (selectedRegistration.form_data || {})" :key="key">
                                <template x-if="!key.endsWith('_url') && !key.endsWith('_image') && !key.endsWith('_photo') && key !== 'submission_date'">
                                    <div class="bg-slate-50/90 p-2.5 rounded-xl border border-slate-200/80 hover:bg-slate-100/70 transition-colors">
                                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-wider block truncate" x-text="key.replace(/_/g, ' ')"></span>
                                        
                                        <!-- If json formatted data (e.g. siblings_json) -->
                                        <template x-if="typeof val === 'string' && (val.startsWith('[') || val.startsWith('{'))">
                                            <span class="font-bold text-slate-800 text-xs block break-words mt-1 bg-white p-1.5 rounded border border-slate-200 font-mono text-[10px]" x-text="val"></span>
                                        </template>
                                        
                                        <template x-if="!(typeof val === 'string' && (val.startsWith('[') || val.startsWith('{')))">
                                            <span class="font-bold text-slate-900 text-xs block break-words mt-0.5" x-text="val || '-'"></span>
                                        </template>
                                    </div>
                                </template>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Footer Action Buttons -->
                <div class="px-5 py-3 bg-slate-50 border-t border-slate-100 flex items-center justify-end shrink-0">
                    <button type="button" @click="showDetailsModal = false" 
                            class="px-5 py-2 bg-slate-900 hover:bg-slate-800 text-white font-extrabold text-xs rounded-xl shadow-xs transition-colors cursor-pointer">
                        {{ $isGu ? 'બંધ કરો' : __('messages.close') }}
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>
@endsection
