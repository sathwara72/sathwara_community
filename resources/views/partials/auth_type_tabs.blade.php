{{-- Member / Business switcher for login & register pages.
     Usage: @include('partials.auth_type_tabs', ['active' => 'member'|'business', 'mode' => 'login'|'register']) --}}
@php
    $mode = $mode ?? 'login';
    $tabs = [
        'member' => [
            'url' => $mode === 'register' ? route('register.member') : route('login'),
            'label' => $mode === 'register' ? __('messages.member_registration') : __('messages.member_login'),
            'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
        ],
        'business' => [
            'url' => $mode === 'register' ? route('register.business') : route('business.login'),
            'label' => $mode === 'register' ? __('messages.business_registration') : __('messages.business_login'),
            'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
        ],
    ];
@endphp
<div class="relative z-10 grid grid-cols-2 gap-1 p-1 bg-slate-100 rounded-xl">
    @foreach($tabs as $type => $tab)
        <a href="{{ $tab['url'] }}"
           @if($active === $type) aria-current="page" @endif
           class="inline-flex items-center justify-center gap-1.5 px-2 py-2 rounded-lg text-xs font-extrabold transition-colors {{ $active === $type ? 'bg-white text-primary-600 shadow-xs' : 'text-slate-500 hover:text-slate-800' }}">
            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $tab['icon'] }}"/>
            </svg>
            <span class="truncate">{{ $tab['label'] }}</span>
        </a>
    @endforeach
</div>
