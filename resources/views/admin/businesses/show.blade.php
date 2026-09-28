@extends('layouts.admin')

@section('page_title', __('messages.business_details'))

@section('content')
<div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm space-y-4">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-4 border-b border-slate-100">
        <div class="flex items-center space-x-3.5">
            <img class="w-12 h-12 rounded-xl object-cover bg-slate-50 border border-slate-200/80 shrink-0 shadow-2xs" 
                 src="{{ str_starts_with($business->logo_path, 'http') ? $business->logo_path : asset('storage/' . $business->logo_path) }}" 
                 alt="Business Logo">
            <div class="min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <h3 class="text-base font-black text-slate-900 leading-tight truncate">{{ $business->business_name }}</h3>
                    <span class="px-2.5 py-0.5 bg-primary-50 text-primary-600 border border-primary-200/60 rounded-lg text-xs font-bold uppercase">
                        {{ $business->category?->name ?? 'N/A' }}
                    </span>
                </div>
                <p class="text-xs text-slate-500 font-medium truncate mt-0.5">{{ __('messages.owner') }}: <strong class="text-slate-800">{{ $business->owner_name }}</strong></p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2 shrink-0">
            <!-- Edit Button -->
            <a href="{{ route('admin.businesses.edit', $business->id) }}" 
               class="px-3 py-1.5 border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs rounded-xl transition-colors bg-white shadow-2xs flex items-center gap-1.5">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                <span>{{ __('messages.edit') }}</span>
            </a>

            <!-- Approve Action -->
            @if($business->status !== 'approved')
                <form method="POST" action="{{ route('admin.businesses.approve', $business->id) }}" class="inline">
                    @csrf
                    <button type="submit" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs rounded-xl shadow-2xs transition-colors flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>{{ __('messages.approve') }}</span>
                    </button>
                </form>
            @endif

            <!-- Reject Action (Only if pending) -->
            @if($business->status === 'pending')
                <form method="POST" action="{{ route('admin.businesses.reject', $business->id) }}" class="inline">
                    @csrf
                    <button type="submit" class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white font-extrabold text-xs rounded-xl shadow-2xs transition-colors flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <span>{{ __('messages.reject') }}</span>
                    </button>
                </form>
            @endif

            <!-- Mark Inactive / Active Action (Only if approved) -->
            @if($business->status === 'approved')
                @if($business->membership_status === 'active')
                    <form method="POST" action="{{ route('admin.businesses.deactivate', $business->id) }}" class="inline">
                        @csrf
                        <button type="submit" class="px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white font-extrabold text-xs rounded-xl shadow-2xs transition-colors flex items-center gap-1.5" title="Mark Inactive">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                            </svg>
                            <span>{{ __('messages.mark_inactive') }}</span>
                        </button>
                    </form>
                @else
                    <form method="POST" action="{{ route('admin.businesses.activate', $business->id) }}" class="inline">
                        @csrf
                        <button type="submit" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs rounded-xl shadow-2xs transition-colors flex items-center gap-1.5" title="Mark Active">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>{{ __('messages.mark_active') }}</span>
                        </button>
                    </form>
                @endif
            @endif

            <!-- Back Button -->
            <a href="{{ route('admin.businesses.index') }}" 
               class="px-3 py-1.5 border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs rounded-xl transition-colors bg-white shadow-2xs flex items-center gap-1.5">
                <span>&larr;</span>
                <span>{{ __('messages.back') }}</span>
            </a>
        </div>
    </div>

    <!-- Contact & Overview Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-4 py-2">
        <div>
            <h5 class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider mb-1">{{ __('messages.status') }}</h5>
            <span class="inline-block px-2.5 py-1 text-xs font-extrabold rounded-lg uppercase {{ $business->status === 'approved' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/60' : ($business->status === 'rejected' ? 'bg-rose-50 text-rose-700 border border-rose-200/60' : 'bg-amber-50 text-amber-700 border border-amber-200/60') }}">
                {{ $business->status }}
            </span>
        </div>

        <div>
            <h5 class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider mb-1">{{ __('messages.membership_status') }}</h5>
            <span class="inline-block px-2.5 py-1 text-xs font-extrabold rounded-lg uppercase {{ $business->membership_status === 'active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/60' : 'bg-slate-100 text-slate-700 border border-slate-200/60' }}">
                @if($business->membership_status === 'active')
                    {{ __('messages.active') }}
                @else
                    {{ __('messages.inactive') }}
                @endif
            </span>
        </div>

        <div>
            <h5 class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider mb-1">{{ __('messages.member_id_prefix') }}</h5>
            <p class="font-bold text-slate-900 text-sm">{{ $business->member_id ?? 'N/A' }}</p>
        </div>

        <div>
            <h5 class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider mb-1">{{ __('messages.phone') }}</h5>
            <p class="font-bold text-slate-900 text-sm">{{ $business->phone }}</p>
        </div>

        <div>
            <h5 class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider mb-1">{{ __('messages.whatsapp') }}</h5>
            <p class="font-bold text-slate-900 text-sm">{{ $business->whatsapp ?? 'N/A' }}</p>
        </div>

        <div>
            <h5 class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider mb-1">{{ __('messages.email') }}</h5>
            <p class="font-bold text-slate-900 text-sm truncate" title="{{ $business->email }}">{{ $business->email ?? 'N/A' }}</p>
        </div>

        <div>
            <h5 class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider mb-1">{{ __('messages.area') }}</h5>
            <p class="font-bold text-slate-900 text-sm">{{ $business->area?->name ?? 'N/A' }}</p>
        </div>

        <div>
            <h5 class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider mb-1">{{ __('messages.category') }}</h5>
            <p class="font-bold text-slate-900 text-sm">{{ $business->category?->name ?? 'N/A' }}</p>
        </div>

        <div>
            <h5 class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider mb-1">{{ __('messages.website') }}</h5>
            <p class="font-bold text-primary-600 text-sm truncate">
                @if($business->website)
                    <a href="{{ $business->website }}" target="_blank" class="hover:underline">{{ $business->website }}</a>
                @else
                    <span class="text-slate-400">N/A</span>
                @endif
            </p>
        </div>

        <div>
            <h5 class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider mb-1">{{ __('messages.registered_at') }}</h5>
            <p class="font-bold text-slate-900 text-sm">{{ $business->created_at ? $business->created_at->format('d-M-Y') : 'N/A' }}</p>
        </div>

        <div>
            <h5 class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider mb-1">{{ __('messages.approved_at') }}</h5>
            <p class="font-bold text-slate-900 text-sm">{{ $business->approved_at ? $business->approved_at->format('d-M-Y') : 'N/A' }}</p>
        </div>
    </div>

    <!-- Description & Address Section -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-3 border-t border-slate-100">
        <div class="space-y-1">
            <span class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider block">{{ __('messages.description') }}</span>
            <p class="text-slate-800 leading-relaxed text-xs sm:text-sm font-medium">{{ $business->description ?? 'No description provided.' }}</p>
        </div>

        <div class="space-y-1">
            <span class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider block">{{ __('messages.address') }}</span>
            <p class="text-slate-800 leading-relaxed text-xs sm:text-sm font-medium">{{ $business->address ?? 'No address provided.' }}</p>
        </div>
    </div>

    <!-- Social Links & Product Showcase -->
    <div class="pt-3 border-t border-slate-100 space-y-2.5">
        <div class="flex items-center justify-between flex-wrap gap-2">
            <span class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider block">{{ __('messages.product_showcase_social') }}</span>
            
            @if($business->facebook || $business->instagram || $business->youtube || $business->linkedin)
                <div class="flex items-center space-x-2">
                    @if($business->facebook)
                        <a href="{{ $business->facebook }}" target="_blank" class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors flex items-center justify-center shadow-2xs" title="Facebook">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M22 12c0-5.52-4.48-10-10-10S2 6.48 2 12c0 4.84 3.44 8.87 8 9.8V15H8v-3h2V9.5C10 7.57 11.57 6 13.5 6H16v3h-2c-.55 0-1 .45-1 1v2h3v3h-3v6.95c4.56-.93 8-4.96 8-9.75z"/></svg>
                        </a>
                    @endif
                    @if($business->instagram)
                        <a href="{{ $business->instagram }}" target="_blank" class="w-7 h-7 rounded-lg bg-pink-50 text-pink-600 hover:bg-pink-100 transition-colors flex items-center justify-center shadow-2xs" title="Instagram">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.051.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                        </a>
                    @endif
                    @if($business->youtube)
                        <a href="{{ $business->youtube }}" target="_blank" class="w-7 h-7 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 transition-colors flex items-center justify-center shadow-2xs" title="YouTube">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.163a3.003 3.003 0 00-2.11-2.11C19.518 3.545 12 3.545 12 3.545s-7.518 0-9.388.508a3.003 3.003 0 00-2.11 2.11C0 8.033 0 12 0 12s0 3.967.502 5.837a3.003 3.003 0 002.11 2.11c1.87.508 9.388.508 9.388.508s7.518 0 9.388-.508a3.003 3.003 0 002.11-2.11C24 15.967 24 12 24 12s0-3.967-.502-5.837zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                        </a>
                    @endif
                    @if($business->linkedin)
                        <a href="{{ $business->linkedin }}" target="_blank" class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100 transition-colors flex items-center justify-center shadow-2xs" title="LinkedIn">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.779-1.75-1.75s.784-1.75 1.75-1.75 1.75.779 1.75 1.75-.784 1.75-1.75 1.75zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
                        </a>
                    @endif
                </div>
            @endif
        </div>

        @if(empty($business->gallery_images) || count($business->gallery_images) === 0)
            <div class="py-3 text-center text-slate-400 text-xs bg-slate-50 border border-dashed border-slate-200 rounded-xl">
                {{ __('messages.no_gallery_photos') }}
            </div>
        @else
            <div class="flex items-center gap-2.5 flex-wrap">
                @foreach($business->gallery_images as $img)
                    <a href="{{ asset('storage/' . $img) }}" target="_blank" 
                       class="w-16 h-16 rounded-xl overflow-hidden border border-slate-200 hover:shadow-md transition-all shrink-0 bg-slate-50 group relative block">
                        <img src="{{ asset('storage/' . $img) }}" class="w-full h-full object-cover" alt="Product image">
                        <div class="absolute inset-0 bg-slate-900/30 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-[10px] text-white font-extrabold">
                            {{ __('messages.view') }}
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</div>

<!-- Renewal Payment Links (Business Only) -->
<div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200 shadow-sm space-y-5 mt-6">
    <div class="flex items-center justify-between border-b border-slate-100 pb-4 flex-wrap gap-3">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-primary-50 text-primary-600 flex items-center justify-center text-lg shadow-sm border border-primary-100">
                💳
            </div>
            <div>
                <h4 class="text-base font-black text-slate-900 leading-tight">
                    Renewal Payment Links
                </h4>
                <p class="text-xs text-slate-500 font-medium mt-0.5">
                    Generate and track annual renewal payment links for this business.
                </p>
            </div>
        </div>
        @if($business->approved_at)
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-bold text-slate-700 shadow-sm">
                <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span>Current Approval Valid Until:</span>
                <span class="font-extrabold text-primary-600 font-mono">{{ $business->approved_at->copy()->addYear()->format('d-M-Y') }}</span>
            </div>
        @endif
    </div>

    @if($business->isRenewalDue())
        <div class="bg-gradient-to-r from-primary-50/50 via-slate-50 to-emerald-50/40 rounded-2xl p-4 sm:p-5 border border-primary-100">
            <form method="POST" action="{{ route('admin.businesses.paymentLinks.generate', $business->id) }}" class="flex flex-wrap items-end gap-3 sm:gap-4">
                @csrf
                <div class="space-y-1.5">
                    <label class="text-xs font-black text-slate-700 uppercase tracking-wider block">
                        Renewal Amount (₹) <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 font-black text-sm">₹</span>
                        <input type="number" name="amount" min="1" step="1"
                               value="{{ old('amount', \App\Models\Setting::get('business_registration_fee', '500')) }}"
                               required
                               class="h-10 w-40 text-sm font-black pl-8 pr-3 bg-white border border-slate-300 rounded-xl focus:bg-white focus:outline-none focus:border-primary-500 shadow-sm">
                    </div>
                </div>
                <button type="submit" class="h-10 px-5 bg-gradient-to-r from-primary-600 to-primary-500 hover:from-primary-700 hover:to-primary-600 active:scale-98 text-white font-extrabold text-xs sm:text-sm rounded-xl shadow-md hover:shadow-lg transition-all flex items-center gap-2 cursor-pointer">
                    <svg class="w-4 h-4 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                    <span>Generate &amp; Email Payment Link</span>
                </button>
                <span class="text-xs text-slate-500 font-medium inline-flex items-center gap-1.5 mb-2">
                    <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Link expires automatically in 24 hours.</span>
                </span>
            </form>
        </div>
    @else
        <div class="flex items-center gap-3 p-4 rounded-xl bg-slate-50 border border-slate-200 text-xs font-medium text-slate-600">
            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>
                This business's current 1-year approval is active. A renewal payment link can be generated once it completes on
                <strong class="text-slate-900 font-bold font-mono">{{ $business->approved_at ? $business->approved_at->copy()->addYear()->format('d-M-Y') : '1 year from approval' }}</strong>.
            </span>
        </div>
    @endif

    @if($business->paymentLinks->isNotEmpty())
        <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm mt-4">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[820px]">
                    <thead>
                        <tr class="bg-slate-50 text-[11px] font-black uppercase text-slate-600 tracking-wider border-b border-slate-200 whitespace-nowrap">
                            <th class="py-3.5 px-4">Amount</th>
                            <th class="py-3.5 px-3 text-center">Status</th>
                            <th class="py-3.5 px-4">Validity / Timeline</th>
                            <th class="py-3.5 px-4 text-center">Share Link</th>
                            <th class="py-3.5 px-5 text-right">Verification &amp; Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs font-semibold text-slate-700">
                        @foreach($business->paymentLinks as $link)
                            @php
                                $whatsappDigits = preg_replace('/[^0-9]/', '', $business->whatsapp ?: $business->phone ?: '');
                                $waMessage = "Renew your business listing for {$business->business_name} - ₹" . number_format((float) $link->amount, 2) . ". Pay here: {$link->razorpay_link_url} (expires {$link->expires_at->format('d-M-Y h:i A')})";
                            @endphp
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <!-- Col 1: Amount -->
                                <td class="py-4 px-4 whitespace-nowrap align-middle">
                                    <div class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-50 rounded-xl border border-slate-200">
                                        <span class="text-sm font-black text-slate-900 font-mono">₹{{ number_format((float) $link->amount, 2) }}</span>
                                    </div>
                                </td>

                                <!-- Col 2: Status -->
                                <td class="py-4 px-3 text-center whitespace-nowrap align-middle">
                                    @if($link->status === 'paid')
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-[11px] font-black uppercase tracking-wider shadow-xs">
                                            <svg class="w-3 h-3 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                            <span>PAID</span>
                                        </span>
                                    @elseif($link->isExpired())
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-100 text-slate-500 border border-slate-200 text-[11px] font-bold uppercase tracking-wider">
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                            <span>EXPIRED</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-50 text-amber-700 border border-amber-200 text-[11px] font-black uppercase tracking-wider shadow-xs">
                                            <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                                            <span>ACTIVE</span>
                                        </span>
                                    @endif
                                </td>

                                <!-- Col 3: Validity / Timeline -->
                                <td class="py-4 px-4 whitespace-nowrap align-middle">
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-2 text-slate-800 font-bold text-xs">
                                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wide w-14">Created:</span>
                                            <span class="font-mono text-slate-700">{{ $link->created_at->format('d-M-Y h:i A') }}</span>
                                        </div>
                                        <div class="flex items-center gap-2 text-xs {{ $link->isExpired() ? 'text-rose-600 font-bold' : 'text-slate-600 font-medium' }}">
                                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wide w-14">Expires:</span>
                                            <span class="font-mono">{{ $link->expires_at->format('d-M-Y h:i A') }}</span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Col 4: Share Link -->
                                <td class="py-4 px-4 text-center whitespace-nowrap align-middle">
                                    <div class="inline-flex items-center justify-center gap-1.5">
                                        <!-- Copy Button -->
                                        <button type="button"
                                                onclick="navigator.clipboard.writeText('{{ $link->razorpay_link_url }}'); const btn=this; const original=btn.innerHTML; btn.innerHTML='<span class=\'text-emerald-700 font-bold\'>✓ Copied</span>'; setTimeout(() => btn.innerHTML=original, 1800);"
                                                class="px-3 py-1.5 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 font-bold rounded-lg text-xs shadow-sm hover:shadow transition-all inline-flex items-center gap-1.5 cursor-pointer"
                                                title="Copy Payment URL">
                                            <svg class="w-3.5 h-3.5 text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                                            <span>Copy</span>
                                        </button>

                                        <!-- WhatsApp Button -->
                                        <a href="https://wa.me/{{ $whatsappDigits }}?text={{ urlencode($waMessage) }}" target="_blank"
                                           class="px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 text-emerald-700 font-bold rounded-lg text-xs shadow-sm hover:shadow transition-all inline-flex items-center gap-1.5 cursor-pointer"
                                           title="Share via WhatsApp">
                                            <svg class="w-3.5 h-3.5 text-emerald-600 fill-current shrink-0" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                                            <span>WhatsApp</span>
                                        </a>

                                        @if($link->status !== 'paid')
                                            <!-- Resend Email Button -->
                                            <form method="POST" action="{{ route('admin.businesses.paymentLinks.resend', [$business->id, $link->id]) }}" class="inline">
                                                @csrf
                                                <button type="submit"
                                                        class="px-3 py-1.5 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 font-bold rounded-lg text-xs shadow-sm hover:shadow transition-all inline-flex items-center gap-1.5 cursor-pointer"
                                                        title="Resend email to {{ $business->email }}">
                                                    <svg class="w-3.5 h-3.5 text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                                    <span>Resend</span>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>

                                <!-- Col 5: Verification & Actions -->
                                <td class="py-4 px-5 text-right whitespace-nowrap align-middle">
                                    @if($link->status === 'paid')
                                        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold shadow-xs">
                                            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                            <span>Renewed Listing</span>
                                            @if($link->razorpay_payment_id)
                                                <span class="font-mono text-[11px] text-emerald-700 bg-white px-2 py-0.5 rounded border border-emerald-200" title="Payment ID">
                                                    {{ $link->razorpay_payment_id }}
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <form method="POST" action="{{ route('admin.businesses.paymentLinks.markPaid', [$business->id, $link->id]) }}"
                                              class="inline-flex items-center gap-2 justify-end"
                                              onsubmit="return confirm('Mark this payment link as paid? This will renew the business listing for 1 year.');">
                                            @csrf
                                            <input type="text" name="razorpay_payment_id"
                                                   placeholder="Txn ID (optional)"
                                                   class="h-9 w-44 text-xs font-semibold px-3 bg-slate-50 border border-slate-300 rounded-lg focus:bg-white focus:outline-none focus:border-primary-500 shadow-sm"
                                                   title="Optional Razorpay Payment ID or Transaction Reference">
                                            <button type="submit"
                                                    class="h-9 px-3.5 bg-slate-900 hover:bg-slate-800 active:scale-95 text-white font-black text-xs rounded-lg shadow-sm hover:shadow transition-all inline-flex items-center gap-1.5 cursor-pointer">
                                                <svg class="w-3.5 h-3.5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                <span>Mark Paid</span>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="text-center py-8 px-4 bg-slate-50/60 rounded-2xl border border-dashed border-slate-200 text-slate-500 text-xs font-medium">
            <svg class="w-8 h-8 text-slate-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
            No renewal payment links have been generated yet for this business.
        </div>
    @endif
</div>
@endsection