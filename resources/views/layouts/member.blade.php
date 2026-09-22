<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    class="{{ app()->getLocale() == 'gu' ? 'font-gujarati' : 'font-sans' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? App\Models\Setting::get('website_name', 'Member Dashboard') }}</title>
    @if(App\Models\Setting::get('website_favicon'))
        <link rel="icon" type="image/x-icon" href="{{ asset('storage/' . App\Models\Setting::get('website_favicon')) }}">
    @endif

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Noto+Sans+Gujarati:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @php
        $primaryColor = App\Models\Setting::get('primary_color', '#ef4444');
    @endphp
    <style>
        :root {
            --primary-hex:
                {{ $primaryColor }}
            ;
        }

        .text-primary-500,
        .group-hover\:text-primary-500:hover,
        .hover\:text-primary-500:hover {
            color: var(--primary-hex) !important;
        }

        .text-primary-600 {
            color: var(--primary-hex) !important;
            filter: brightness(90%);
        }

        .bg-primary-50 {
            background-color: color-mix(in srgb, var(--primary-hex) 10%, transparent) !important;
        }

        .bg-primary-500 {
            background-color: var(--primary-hex) !important;
        }

        .hover\:bg-primary-600:hover {
            background-color: var(--primary-hex) !important;
            filter: brightness(90%);
        }

        .border-primary-500 {
            border-color: var(--primary-hex) !important;
        }

        .from-primary-500 {
            --tw-gradient-from: var(--primary-hex) var(--tw-gradient-from-position, ) !important;
            --tw-gradient-to: rgb(255 255 255 / 0) var(--tw-gradient-to-position, ) !important;
            --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to) !important;
        }

        .to-primary-500 {
            --tw-gradient-to: var(--primary-hex) var(--tw-gradient-to-position, ) !important;
        }
    </style>

    <style>
        .font-gujarati {
            font-family: 'Noto Sans Gujarati', sans-serif !important;
            letter-spacing: 0.01em;
        }

        .font-sans {
            font-family: 'Plus Jakarta Sans', sans-serif !important;
        }

        body {
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* Global Button Hover Highlight & Elevation */
        button:not([disabled]),
        input[type="submit"]:not([disabled]),
        input[type="button"]:not([disabled]),
        a[class*="bg-primary"],
        a[class*="bg-slate-900"],
        a[class*="bg-emerald"],
        a[class*="bg-rose"],
        a[class*="bg-indigo"],
        button[class*="bg-primary"],
        button[class*="bg-slate-900"],
        button[class*="bg-emerald"],
        button[class*="bg-rose"],
        button[class*="bg-indigo"] {
            transition: all 0.22s cubic-bezier(0.4, 0, 0.2, 1) !important;
            cursor: pointer;
        }

        button:not([disabled]):hover,
        input[type="submit"]:not([disabled]):hover,
        input[type="button"]:not([disabled]):hover,
        a[class*="bg-primary"]:hover,
        a[class*="bg-slate-900"]:hover,
        a[class*="bg-emerald"]:hover,
        a[class*="bg-rose"]:hover,
        a[class*="bg-indigo"]:hover,
        button[class*="bg-primary"]:hover,
        button[class*="bg-slate-900"]:hover,
        button[class*="bg-emerald"]:hover,
        button[class*="bg-rose"]:hover,
        button[class*="bg-indigo"]:hover {
            filter: brightness(1.05);
        }

        button:not([disabled]):active,
        input[type="submit"]:not([disabled]):active,
        input[type="button"]:not([disabled]):active,
        a[class*="bg-primary"]:active,
        a[class*="bg-slate-900"]:active,
        a[class*="bg-emerald"]:active,
        a[class*="bg-rose"]:active,
        button[class*="bg-primary"]:active,
        button[class*="bg-slate-900"]:active,
        button[class*="bg-emerald"]:active,
        button[class*="bg-rose"]:active {
            transform: scale(0.98);
        }

        [x-cloak] {
            display: none !important;
        }

        /* Member Panel Typography Scaling */
        html {
            font-size: 16px;
        }

        /* Enhanced Member Table Typography */
        table thead th,
        table thead tr th {
            font-size: 12.5px !important;
            font-weight: 800 !important;
            color: #334155 !important;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            padding: 0.65rem 0.75rem !important;
        }

        table tbody td,
        table tbody tr td {
            font-size: 13.5px !important;
            color: #1e293b !important;
            padding: 0.65rem 0.75rem !important;
        }

        table tbody td h4,
        table tbody td .font-extrabold,
        table tbody td .font-black {
            font-size: 13.5px !important;
            font-weight: 800 !important;
        }

        .font-gujarati table thead th,
        .font-gujarati table thead tr th {
            font-size: 13px !important;
            font-weight: 800 !important;
            color: #1e293b !important;
        }

        .font-gujarati table tbody td {
            font-size: 13.5px !important;
        }

        /* Sidebar Navigation text sizing */
        aside a,
        aside button {
            font-size: 13px !important;
        }

        /* Form Inputs & Controls */
        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="number"],
        input[type="tel"],
        input[type="date"],
        input[type="time"],
        select,
        textarea {
            font-size: 13px !important;
        }

        /* Badges & Micro Text */
        span[class*="text-[9px]"],
        span[class*="text-[10px]"] {
            font-size: 11px !important;
            font-weight: 700 !important;
        }

        span[class*="text-xs"],
        p[class*="text-xs"] {
            font-size: 12.5px !important;
        }

        /* Hide scrollbars utilities */
        .no-scrollbar::-webkit-scrollbar {
            display: none !important;
            width: 0 !important;
            height: 0 !important;
        }

        .no-scrollbar {
            -ms-overflow-style: none !important;
            scrollbar-width: none !important;
        }
    </style>
</head>

<body x-data="{ sidebarOpen: false }"
    class="text-slate-800 antialiased min-h-screen flex flex-col md:flex-row relative">
    <!-- Soft Decorative Background Gradients -->
    <div class="fixed inset-0 bg-slate-50 z-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 right-20 w-96 h-96 rounded-full blur-3xl opacity-20"
            style="background-color: var(--primary-hex);"></div>
        <div class="absolute bottom-[-100px] left-10 w-80 h-80 bg-indigo-500 rounded-full blur-3xl opacity-20"></div>
    </div>

    <!-- Mobile Sidebar Backdrop -->
    <div x-show="sidebarOpen" @click="sidebarOpen = false" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0" class="fixed inset-0 z-40 bg-slate-950/40 backdrop-blur-sm md:hidden"
        x-cloak>
    </div>

    <!-- Sidebar -->
    <aside
        class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-slate-100 flex flex-col shrink-0 transition-transform duration-300 transform md:translate-x-0 md:static md:h-screen md:sticky md:top-0 overflow-hidden"
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
        <!-- Sidebar User Card (Top Header) -->
        <div class="min-h-16 py-3 px-4 border-b border-slate-100 flex items-center justify-between shrink-0">
            <div class="flex items-center space-x-3 min-w-0 flex-1">
                @php
                    $sidebarProfile = auth()->user()->memberProfile;
                    $sidebarHasPhoto = ($sidebarProfile && !empty($sidebarProfile->photo_path) && !str_contains($sidebarProfile->photo_path, 'unsplash.com') && $sidebarProfile->photo_path !== 'NOT_SPECIFIED' && $sidebarProfile->photo_path !== 'N/A');
                    $sidebarPhoto = $sidebarHasPhoto ? (str_starts_with($sidebarProfile->photo_path, 'http') ? $sidebarProfile->photo_path : asset('storage/' . $sidebarProfile->photo_path)) : asset('logo.png');
                @endphp
                <img class="w-10 h-10 rounded-full object-cover shadow-xs bg-slate-100 p-0.5 border border-slate-200 shrink-0"
                    src="{{ $sidebarPhoto }}" alt="User avatar">
                <div class="min-w-0 flex-1">
                    <h4 class="text-xs sm:text-[13px] font-bold text-slate-800 leading-snug break-words">
                        {{ auth()->user()->display_name }}
                    </h4>
                </div>
            </div>
            <!-- Mobile Close Button -->
            <button @click="sidebarOpen = false"
                class="md:hidden text-slate-400 hover:text-slate-600 focus:outline-none shrink-0 ml-2">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Sidebar Navigation -->
        <div class="flex-grow p-3 space-y-2 overflow-y-auto">
            <a href="{{ route('member.dashboard') }}"
                class="flex items-center space-x-3 px-4 py-2.5 text-xs font-bold rounded-lg {{ Route::is('member.dashboard') ? 'bg-primary-50 text-primary-500' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }} transition-colors">
                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2" />
                </svg>
                <span>{{ __('messages.dashboard') }}</span>
            </a>
            <a href="{{ route('member.profile.edit') }}"
                class="flex items-center space-x-3 px-4 py-2.5 text-xs font-bold rounded-lg {{ Route::is('member.profile.edit') ? 'bg-primary-50 text-primary-500' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }} transition-colors">
                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span>{{ __('messages.profile') }}</span>
            </a>
            <a href="{{ route('member.family.index') }}"
                class="flex items-center space-x-3 px-4 py-2.5 text-xs font-bold rounded-lg {{ Route::is('member.family.*') ? 'bg-primary-50 text-primary-500' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }} transition-colors">
                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <span>{{ __('messages.family_members') }}</span>
            </a>
            <a href="{{ route('member.directory') }}"
                class="flex items-center space-x-3 px-4 py-2.5 text-xs font-bold rounded-lg {{ Route::is('member.directory') ? 'bg-primary-50 text-primary-500' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }} transition-colors">
                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <span>{{ __('messages.member_directory') }}</span>
            </a>
            <a href="{{ route('member.events.index') }}"
                class="flex items-center space-x-3 px-4 py-2.5 text-xs font-bold rounded-lg {{ Route::is('member.events.*') ? 'bg-primary-50 text-primary-500' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }} transition-colors">
                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span>{{ __('messages.register_event') }}</span>
            </a>
            <a href="{{ route('member.businesses.my') }}"
                class="flex items-center space-x-3 px-4 py-2.5 text-xs font-bold rounded-lg {{ Route::is('member.businesses.*') ? 'bg-primary-50 text-primary-500' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }} transition-colors">
                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                <span>{{ __('messages.my_businesses') ?? 'My Businesses' }}</span>
            </a>
            <a href="{{ route('member.card') }}"
                class="flex items-center space-x-3 px-4 py-2.5 text-xs font-bold rounded-lg {{ Route::is('member.card') ? 'bg-primary-50 text-primary-500' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }} transition-colors">
                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8h2m-2 2h4" />
                </svg>
                <span>{{ __('messages.membership_card') }}</span>
            </a>
            <a href="{{ route('member.account.settings') }}"
                class="flex items-center space-x-3 px-4 py-2.5 text-xs font-bold rounded-lg {{ Route::is('member.account.settings') ? 'bg-primary-50 text-primary-500' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }} transition-colors">
                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span>{{ __('messages.account_settings') }}</span>
            </a>
            <div class="border-t border-slate-100 my-2"></div>
            <a href="{{ route('home') }}"
                class="flex items-center space-x-3 px-4 py-2.5 text-xs font-bold rounded-lg text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-colors">
                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span>{{ __('messages.back_to_website') }}</span>
            </a>

            <!-- Logout -->
            <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf
                <button type="submit"
                    class="w-full flex items-center space-x-3 px-4 py-2.5 text-xs font-bold rounded-lg text-rose-600 hover:bg-rose-50 hover:text-rose-700 transition-colors text-left">
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    <span>{{ __('messages.logout') }}</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Member Content Area -->
    <div class="flex-grow flex flex-col min-w-0 relative z-10">
        <!-- Top Navigation -->
        <header
            class="h-16 bg-white border-b border-slate-100 flex items-center justify-between px-4 sm:px-6 lg:px-8 shrink-0">
            <div class="flex items-center gap-3 min-w-0">
                <button @click="sidebarOpen = true"
                    class="md:hidden text-slate-500 hover:text-slate-700 focus:outline-none p-1.5 rounded-lg hover:bg-slate-100 transition-colors">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <h2 class="font-extrabold text-base sm:text-lg lg:text-xl text-slate-950 truncate">
                    @yield('page_title', __('messages.dashboard'))
                </h2>
            </div>

            <div class="flex items-center space-x-4">
                <!-- Language Toggle -->
                <div class="relative" x-data="{ showLang: false }">
                    <button @click="showLang = !showLang"
                        class="inline-flex items-center px-3 py-1.5 border border-slate-200 text-xs font-bold rounded-lg text-slate-700 bg-slate-50 hover:bg-slate-100 transition-colors">
                        🌐 {{ app()->getLocale() == 'en' ? 'English' : 'ગુજરાતી' }}
                    </button>
                    <div x-show="showLang" @click.away="showLang = false"
                        class="absolute right-0 mt-2 w-32 bg-white border border-slate-100 rounded-lg shadow-lg py-1 z-50"
                        x-cloak>
                        <a href="{{ route('locale.set', 'en') }}"
                            class="block px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">English</a>
                        <a href="{{ route('locale.set', 'gu') }}"
                            class="block px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 font-gujarati">ગુજરાતી</a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Dynamic Content Body -->
        <main class="flex-grow px-4 pb-4 pt-3 overflow-y-auto">
            <!-- Toast Alerts -->
            @if (session('success') || session('error') || session('warning') || session('info'))
                <div class="mb-6" x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show"
                    x-transition>
                    @if (session('success'))
                        <div
                            class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between">
                            <span class="text-sm font-semibold">✅ {{ session('success') }}</span>
                            <button @click="show = false" class="text-emerald-500 font-bold">&times;</button>
                        </div>
                    @endif
                    @if (session('error'))
                        <div
                            class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 flex items-center justify-between">
                            <span class="text-sm font-semibold">❌ {{ session('error') }}</span>
                            <button @click="show = false" class="text-rose-500 font-bold">&times;</button>
                        </div>
                    @endif
                </div>
            @endif

            @yield('content')
        </main>
    </div>
    @include('partials.global_loader')
    @include('partials.delete_confirm_modal')
    @include('components.purchase_receipt_modal')
    @stack('scripts')
</body>

</html>