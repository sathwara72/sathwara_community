{{-- Heading shown on the coloured band of the registration pages.
     Usage: @include('partials.registration_hero', ['variant' => 'member'|'business']) --}}
@php
    $variant = $variant ?? 'member';
    $isBusiness = $variant === 'business';
    $iconPath = $isBusiness
        ? 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'
        : 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z';
@endphp
<div class="text-center text-white pt-2 pb-5 space-y-3">
    <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl shadow-lg"
         style="background: rgba(255, 255, 255, 0.14); border: 1px solid rgba(255, 255, 255, 0.25);">
        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: {{ $isBusiness ? '#fbbf24' : '#ffffff' }};">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $iconPath }}"/>
        </svg>
    </div>
    <div class="space-y-1.5">
        <span class="inline-block text-[11px] font-extrabold uppercase tracking-widest px-3 py-1 rounded-full"
              style="background: {{ $isBusiness ? 'rgba(251, 191, 36, 0.18)' : 'rgba(255, 255, 255, 0.18)' }}; color: {{ $isBusiness ? '#fcd34d' : '#ffffff' }};">
            {{ __('messages.home_' . $variant . '_tag') }}
        </span>
        <h1 class="text-2xl sm:text-3xl font-black tracking-tight">{{ __('messages.home_' . $variant . '_title') }}</h1>
        <p class="text-sm font-medium max-w-xl mx-auto" style="color: rgba(255, 255, 255, 0.82);">{{ __('messages.home_' . $variant . '_desc') }}</p>
    </div>
</div>
