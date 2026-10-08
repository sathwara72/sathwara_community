{{-- Decorative background for login / register pages.
     Usage: @include('partials.auth_background', ['variant' => 'member'|'business'])
     Parent must be `relative overflow-hidden`; content needs `relative z-10`. --}}
@php
    $variant = $variant ?? 'member';
    $theme = $variant === 'business'
        ? ['band' => 'linear-gradient(135deg, #0f172a 0%, #1e293b 55%, #3b1d0f 100%)', 'glowA' => 'rgba(245, 158, 11, 0.35)', 'glowB' => 'rgba(220, 38, 38, 0.30)', 'icon' => 'rgba(251, 191, 36, 0.16)']
        : ['band' => 'linear-gradient(135deg, #7f1d1d 0%, #b91c1c 45%, #ea580c 100%)', 'glowA' => 'rgba(254, 202, 202, 0.30)', 'glowB' => 'rgba(253, 186, 116, 0.40)', 'icon' => 'rgba(255, 255, 255, 0.12)'];
    $iconPath = $variant === 'business'
        ? 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'
        : 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z';
@endphp
<div aria-hidden="true" class="pointer-events-none absolute inset-0 overflow-hidden" style="z-index: 0; background: #f8fafc;">

    <!-- Coloured top band -->
    <div class="absolute left-0 right-0 top-0" style="height: 360px; background: {{ $theme['band'] }};">
        <!-- Dot pattern -->
        <div class="absolute inset-0" style="background-image: radial-gradient(rgba(255, 255, 255, 0.16) 1.3px, transparent 1.3px); background-size: 20px 20px;"></div>
        <!-- Glows -->
        <div class="absolute rounded-full" style="width: 460px; height: 460px; top: -220px; right: -120px; background: radial-gradient(circle, {{ $theme['glowA'] }}, transparent 65%);"></div>
        <div class="absolute rounded-full" style="width: 380px; height: 380px; top: 40px; left: -160px; background: radial-gradient(circle, {{ $theme['glowB'] }}, transparent 65%);"></div>
        <!-- Rings -->
        <div class="absolute rounded-full" style="width: 220px; height: 220px; top: -60px; left: 38%; border: 1.5px solid rgba(255, 255, 255, 0.12);"></div>
        <div class="absolute rounded-full" style="width: 140px; height: 140px; top: 150px; right: 14%; border: 1.5px dashed rgba(255, 255, 255, 0.18);"></div>
        <!-- Faint motif icons -->
        <svg class="absolute hidden md:block" style="width: 130px; height: 130px; top: 60px; left: 6%; color: {{ $theme['icon'] }}; transform: rotate(-12deg);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.1" d="{{ $iconPath }}"/></svg>
        <svg class="absolute hidden md:block" style="width: 96px; height: 96px; top: 70px; right: 7%; color: {{ $theme['icon'] }}; transform: rotate(14deg);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.1" d="{{ $iconPath }}"/></svg>
        <!-- Curved bottom edge -->
        <svg class="absolute left-0 right-0" style="bottom: -1px; width: 100%; height: 70px;" viewBox="0 0 1440 70" preserveAspectRatio="none">
            <path d="M0,40 C240,80 480,0 720,24 C960,48 1200,70 1440,30 L1440,70 L0,70 Z" fill="#f8fafc"/>
        </svg>
    </div>

    <!-- Soft accents on the light area -->
    <div class="absolute rounded-full" style="width: 420px; height: 420px; bottom: -200px; left: -140px; background: radial-gradient(circle, {{ $variant === 'business' ? 'rgba(245, 158, 11, 0.14)' : 'rgba(239, 68, 68, 0.12)' }}, transparent 68%);"></div>
    <div class="absolute rounded-full" style="width: 360px; height: 360px; bottom: -160px; right: -120px; background: radial-gradient(circle, {{ $variant === 'business' ? 'rgba(15, 23, 42, 0.08)' : 'rgba(249, 115, 22, 0.12)' }}, transparent 68%);"></div>
</div>
