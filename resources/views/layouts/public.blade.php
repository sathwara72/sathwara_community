<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    class="overflow-x-hidden {{ app()->getLocale() == 'gu' ? 'font-gujarati' : 'font-sans' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>
        {{ $title ?? App\Models\Setting::get('seo_title', App\Models\Setting::get('website_name', 'Shree Satwara Gnati Mandal, Ahmedabad')) }}
    </title>
    @if(App\Models\Setting::get('seo_description'))
        <meta name="description" content="{{ App\Models\Setting::get('seo_description') }}">
    @endif
    @if(App\Models\Setting::get('website_favicon'))
        <link rel="icon" type="image/x-icon" href="{{ asset('storage/' . App\Models\Setting::get('website_favicon')) }}">
    @endif

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,500&family=Noto+Sans+Gujarati:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* ============================================================
           TYPOGRAPHY SYSTEM — Shree Satwara Gnati Mandal, Ahmedabad Website
           English  : Plus Jakarta Sans
           Gujarati : Noto Sans Gujarati
           ============================================================ */

        html {
            font-size: 16px;
        }

        html,
        body {
            max-width: 100vw;
            overflow-x: clip;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* --- Font Families --- */
        .font-sans,
        html:not(.font-gujarati) body,
        html:not(.font-gujarati) p,
        html:not(.font-gujarati) span,
        html:not(.font-gujarati) div,
        html:not(.font-gujarati) a,
        html:not(.font-gujarati) button,
        html:not(.font-gujarati) input,
        html:not(.font-gujarati) select,
        html:not(.font-gujarati) textarea,
        html:not(.font-gujarati) label,
        html:not(.font-gujarati) h1,
        html:not(.font-gujarati) h2,
        html:not(.font-gujarati) h3,
        html:not(.font-gujarati) h4,
        html:not(.font-gujarati) h5,
        html:not(.font-gujarati) h6 {
            font-family: 'Plus Jakarta Sans', sans-serif !important;
        }

        .font-gujarati,
        .font-gujarati body,
        .font-gujarati p,
        .font-gujarati span,
        .font-gujarati div,
        .font-gujarati a,
        .font-gujarati button,
        .font-gujarati input,
        .font-gujarati select,
        .font-gujarati textarea,
        .font-gujarati label,
        .font-gujarati h1,
        .font-gujarati h2,
        .font-gujarati h3,
        .font-gujarati h4,
        .font-gujarati h5,
        .font-gujarati h6 {
            font-family: 'Noto Sans Gujarati', sans-serif !important;
            letter-spacing: 0.01em;
        }

        /* --- Base Body Text : weight 500 --- */
        body {
            font-weight: 500;
            color: #1e293b;
            line-height: 1.65;
        }

        p, li, td, dd, dt, blockquote {
            font-weight: 500 !important;
        }

        /* --- Suppress thin weights globally (300 / 400 → 500) --- */
        [class*="font-thin"],
        [class*="font-extralight"],
        [class*="font-light"] {
            font-weight: 500 !important;
        }

        [class*="font-normal"]:not(h1):not(h2):not(h3):not(h4):not(h5):not(h6):not(button):not(a):not(nav *) {
            font-weight: 500 !important;
        }

        /* --- Navigation : weight 600 --- */
        nav a,
        header nav a,
        header a,
        .navbar a {
            font-weight: 600 !important;
            font-size: 15px !important;
        }

        /* --- Global Description / Body Paragraph Color --- */
        p,
        .text-slate-600,
        .text-slate-500 {
            color: #475569;
        }

        /* Enforce bright white for all white text elements */
        .text-white,
        [class*="text-white"],
        section .text-white,
        p.text-white,
        span.text-white,
        h1.text-white,
        h2.text-white,
        h3.text-white,
        h4.text-white {
            color: #ffffff !important;
        }

        /* --- Buttons : weight 600 & proper colors --- */
        button,
        a[class*="bg-primary"],
        a[class*="bg-slate-900"],
        a[class*="bg-emerald"],
        a[class*="bg-rose"],
        a[class*="bg-indigo"],
        input[type="submit"],
        input[type="button"],
        [class*="btn"] {
            font-weight: 700 !important;
        }

        /* --- Labels & UI Controls : weight 600 --- */
        label,
        [class*="badge"],
        [class*="tag"],
        th {
            font-weight: 600 !important;
        }

        /* --- Card Titles / h4 h5 h6 : weight 600–700 --- */
        h4, h5, h6,
        [class*="card"] h4,
        [class*="card"] h5 {
            font-weight: 700 !important;
        }

        /* --- Section Headings h3 : weight 700 --- */
        h3 {
            font-weight: 700 !important;
        }

        /* --- Page Headings h2 : weight 700 --- */
        h2 {
            font-weight: 700 !important;
        }

        /* --- Hero / Primary Heading h1 : weight 800 --- */
        h1 {
            font-weight: 800 !important;
            line-height: 1.15;
        }

        /* --- Table Typography --- */
        table thead th,
        table thead tr th {
            font-size: 12.5px !important;
            font-weight: 700 !important;
            color: #334155 !important;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            padding: 0.65rem 0.75rem !important;
        }

        table tbody td,
        table tbody tr td {
            font-size: 13.5px !important;
            font-weight: 500 !important;
            color: #1e293b !important;
            padding: 0.65rem 0.75rem !important;
        }

        /* --- Form Inputs & Controls --- */
        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="number"],
        input[type="tel"],
        input[type="date"],
        input[type="time"],
        select,
        textarea {
            font-size: 13.5px !important;
            font-weight: 500 !important;
        }

        /* --- Badges & Micro Text --- */
        span[class*="text-[9px]"],
        span[class*="text-[10px]"] {
            font-size: 11px !important;
            font-weight: 700 !important;
        }

        span[class*="text-xs"],
        p[class*="text-xs"],
        a[class*="text-xs"] {
            font-size: 12.5px !important;
            font-weight: 600 !important;
        }

        /* --- Global Button Hover & Elevation --- */
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
    </style>
</head>

<body class="text-slate-800 antialiased min-h-screen flex flex-col overflow-x-clip max-w-full relative">
    <!-- Soft Decorative Background Gradients -->
    <div class="fixed inset-0 bg-slate-50 z-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-96 h-96 rounded-full blur-3xl opacity-30"
            style="background-color: var(--primary-hex);"></div>
        <div class="absolute top-1/2 left-[-150px] w-[350px] h-[350px] bg-indigo-500 rounded-full blur-3xl opacity-20">
        </div>
        <div class="absolute bottom-[-100px] right-20 w-80 h-80 bg-rose-500 rounded-full blur-3xl opacity-20"></div>
    </div>

    <!-- Header/Navbar (Fixed Top) -->
    <header
        class="fixed top-0 left-0 right-0 z-50 w-full bg-white/95 backdrop-blur-md border-b border-slate-100/80 shadow-sm transition-all duration-300"
        x-data="{ open: false }">
        <div class="max-w-7xl mx-auto px-2 sm:px-4 lg:px-6 w-full">
            <div class="flex justify-between items-center h-20 gap-1.5 xl:gap-3">

                <!-- Logo / Brand Name -->
                <div class="flex items-center shrink-0">
                    <a href="{{ route('home') }}" class="flex items-center group py-1">
                        @if(App\Models\Setting::get('website_logo'))
                            <img class="h-12 w-12 sm:h-14 sm:w-14 object-contain rounded-full shadow-md shadow-primary-500/10 border border-slate-100 group-hover:scale-105 transition-transform duration-300"
                                src="{{ asset('storage/' . App\Models\Setting::get('website_logo')) }}"
                                alt="{{ App\Models\Setting::get('website_name', 'Satwara') }}">
                        @else
                            <div
                                class="w-12 h-12 sm:w-14 sm:h-14 rounded-full bg-gradient-to-tr from-primary-600 via-primary-500 to-rose-500 flex items-center justify-center text-white font-black text-xl shadow-md shadow-primary-500/20 group-hover:scale-105 group-hover:rotate-3 transition-all duration-300">
                                S
                            </div>
                        @endif
                    </a>
                </div>

                <!-- Desktop Navigation Links -->
                <nav class="hidden lg:flex items-center space-x-1 xl:space-x-1.5 font-sans shrink-0">
                    <a href="{{ route('home') }}"
                        class="px-2.5 xl:px-3 py-1.5 rounded-xl text-sm xl:text-[15px] font-bold whitespace-nowrap transition-all duration-200 {{ Route::is('home') ? 'bg-primary-50 text-primary-500 shadow-xs ring-1 ring-primary-500/20' : 'text-slate-600 hover:text-primary-500 hover:bg-slate-50/80' }}">
                        {{ __('messages.home') }}
                    </a>
                    <a href="{{ route('events') }}"
                        class="px-2.5 xl:px-3 py-1.5 rounded-xl text-sm xl:text-[15px] font-bold whitespace-nowrap transition-all duration-200 {{ Route::is('events') || Route::is('event.details') ? 'bg-primary-50 text-primary-500 shadow-xs ring-1 ring-primary-500/20' : 'text-slate-600 hover:text-primary-500 hover:bg-slate-50/80' }}">
                        {{ __('messages.events') }}
                    </a>
                    <a href="{{ route('updates') }}"
                        class="px-2.5 xl:px-3 py-1.5 rounded-xl text-sm xl:text-[15px] font-bold whitespace-nowrap transition-all duration-200 {{ Route::is('updates') || Route::is('update.details') ? 'bg-primary-50 text-primary-500 shadow-xs ring-1 ring-primary-500/20' : 'text-slate-600 hover:text-primary-500 hover:bg-slate-50/80' }}">
                        {{ __('messages.updates') }}
                    </a>
                    <a href="{{ route('gallery') }}"
                        class="px-2.5 xl:px-3 py-1.5 rounded-xl text-sm xl:text-[15px] font-bold whitespace-nowrap transition-all duration-200 {{ Route::is('gallery') ? 'bg-primary-50 text-primary-500 shadow-xs ring-1 ring-primary-500/20' : 'text-slate-600 hover:text-primary-500 hover:bg-slate-50/80' }}">
                        {{ __('messages.gallery') }}
                    </a>
                    <a href="{{ route('business.directory') }}"
                        class="px-2.5 xl:px-3 py-1.5 rounded-xl text-sm xl:text-[15px] font-bold whitespace-nowrap transition-all duration-200 {{ Route::is('business.directory') || Route::is('business.details') ? 'bg-primary-50 text-primary-500 shadow-xs ring-1 ring-primary-500/20' : 'text-slate-600 hover:text-primary-500 hover:bg-slate-50/80' }}">
                        {{ __('messages.business_directory') }}
                    </a>

                    <!-- About & Contact Combined Dropdown -->
                    <div class="relative" x-data="{ openAbout: false }" @mouseenter="openAbout = true"
                        @mouseleave="openAbout = false">
                        <button @click="openAbout = !openAbout"
                            class="inline-flex items-center gap-1 px-2.5 xl:px-3 py-1.5 rounded-xl text-sm xl:text-[15px] font-bold whitespace-nowrap transition-all duration-200 {{ Route::is('about') || Route::is('management_desk') || Route::is('contact') ? 'bg-primary-50 text-primary-500 shadow-xs ring-1 ring-primary-500/20' : 'text-slate-600 hover:text-primary-500 hover:bg-slate-50/80' }}">
                            <span>
                                @if(Route::is('management_desk'))
                                    {{ __('messages.management_desk') }}
                                @elseif(Route::is('contact'))
                                    {{ __('messages.contact_us') }}
                                @else
                                    {{ __('messages.about_us') }}
                                @endif
                            </span>
                            <svg class="w-3.5 h-3.5 transition-transform duration-200"
                                :class="openAbout ? 'rotate-180 text-primary-500' : 'text-slate-400'" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div x-show="openAbout" x-transition:enter="transition ease-out duration-200 transform"
                            x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-150 transform"
                            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                            x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                            class="absolute right-0 mt-1 w-52 bg-white rounded-2xl shadow-xl border border-slate-100 p-2 z-50 space-y-1"
                            x-cloak>

                            <a href="{{ route('about') }}"
                                class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm xl:text-[15px] font-bold transition-all duration-150 {{ Route::is('about') ? 'bg-primary-50 text-primary-600' : 'text-slate-700 hover:bg-slate-50 hover:text-primary-500' }}">
                                <svg class="w-4 h-4 text-primary-500 shrink-0" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>{{ __('messages.about_us') }}</span>
                            </a>

                            <a href="{{ route('management_desk') }}"
                                class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm xl:text-[15px] font-bold transition-all duration-150 {{ Route::is('management_desk') ? 'bg-primary-50 text-primary-600' : 'text-slate-700 hover:bg-slate-50 hover:text-primary-500' }}">
                                <svg class="w-4 h-4 text-primary-500 shrink-0" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <span>{{ __('messages.management_desk') }}</span>
                            </a>

                            <a href="{{ route('contact') }}"
                                class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm xl:text-[15px] font-bold transition-all duration-150 {{ Route::is('contact') ? 'bg-primary-50 text-primary-600' : 'text-slate-700 hover:bg-slate-50 hover:text-primary-500' }}">
                                <svg class="w-4 h-4 text-primary-500 shrink-0" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                <span>{{ __('messages.contact_us') }}</span>
                            </a>

                        </div>
                    </div>
                </nav>

                <!-- Actions (Language toggle + Auth buttons) -->
                <div class="hidden lg:flex items-center space-x-1.5 xl:space-x-2 shrink-0">
                    <!-- Language Selector Dropdown -->
                    <div class="relative" x-data="{ showLang: false }">
                        <button @click="showLang = !showLang"
                            class="inline-flex items-center gap-1 px-2.5 py-1.5 border border-slate-200/90 text-xs font-bold rounded-xl text-slate-700 bg-slate-50/80 hover:bg-white hover:border-slate-300 hover:shadow-xs transition-all duration-200 focus:outline-none">
                            <svg class="w-3.5 h-3.5 text-primary-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3.6 9h16.8M3.6 15h16.8" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11.5 3a17 17 0 000 18M12.5 3a17 17 0 010 18" />
                            </svg>
                            <span>{{ app()->getLocale() == 'en' ? 'English' : 'ગુજરાતી' }}</span>
                            <svg class="w-3 h-3 text-slate-400 transition-transform duration-200"
                                :class="{ 'rotate-180': showLang }" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="showLang" @click.away="showLang = false"
                            x-transition:enter="transition ease-out duration-150 transform"
                            x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-100 transform"
                            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                            x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                            class="absolute right-0 mt-2 w-36 bg-white border border-slate-100 rounded-xl shadow-xl py-1 z-50 overflow-hidden"
                            x-cloak>
                            <a href="{{ route('locale.set', 'en') }}"
                                class="flex items-center justify-between px-4 py-2.5 text-xs font-bold transition-colors {{ app()->getLocale() == 'en' ? 'text-primary-500 bg-primary-50/60' : 'text-slate-700 hover:bg-slate-50' }}">
                                <span>English</span>
                                @if(app()->getLocale() == 'en')
                                    <span class="w-1.5 h-1.5 rounded-full bg-primary-500"></span>
                                @endif
                            </a>
                            <a href="{{ route('locale.set', 'gu') }}"
                                class="flex items-center justify-between px-4 py-2.5 text-xs font-bold font-gujarati transition-colors {{ app()->getLocale() == 'gu' ? 'text-primary-500 bg-primary-50/60' : 'text-slate-700 hover:bg-slate-50' }}">
                                <span>ગુજરાતી</span>
                                @if(app()->getLocale() == 'gu')
                                    <span class="w-1.5 h-1.5 rounded-full bg-primary-500"></span>
                                @endif
                            </a>
                        </div>
                    </div>

                    <!-- Auth / Dashboard Action Button -->
                    @auth
                        @if(auth()->user()->hasRole('Administrator'))
                            <a href="{{ route('admin.dashboard') }}"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-900 border border-transparent rounded-xl font-extrabold text-xs text-white uppercase tracking-wider hover:bg-slate-800 transition-all duration-200 hover:-translate-y-0.5 active:translate-y-0 shadow-md shadow-slate-900/10 whitespace-nowrap">
                                <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                                <span>{{ __('messages.admin_portal') }}</span>
                            </a>
                        @else
                            <a href="{{ route('member.dashboard') }}"
                                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-gradient-to-r from-primary-600 to-primary-500 border border-transparent rounded-xl font-extrabold text-xs text-white uppercase tracking-wider hover:from-primary-700 hover:to-primary-600 transition-all duration-200 hover:-translate-y-0.5 active:translate-y-0 shadow-md shadow-primary-500/25 whitespace-nowrap">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                </svg>
                                <span>{{ __('messages.view_dashboard') }}</span>
                            </a>
                        @endif
                    @else
                        <a href="{{ route('login') }}"
                            class="text-xs font-extrabold text-slate-700 hover:text-primary-500 px-2 py-1.5 rounded-xl hover:bg-slate-50 transition-colors uppercase tracking-wider whitespace-nowrap">
                            {{ __('messages.login') }}
                        </a>
                        <a href="{{ route('register.member') }}"
                            class="inline-flex items-center gap-1 px-3.5 py-1.5 bg-gradient-to-r from-primary-600 to-primary-500 border border-transparent rounded-xl font-extrabold text-xs text-white uppercase tracking-wider hover:from-primary-700 hover:to-primary-600 transition-all duration-200 hover:-translate-y-0.5 active:translate-y-0 shadow-md shadow-primary-500/25 whitespace-nowrap">
                            <span>{{ __('messages.register') }}</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </a>
                    @endauth
                </div>

                <!-- Hamburger menu button -->
                <div class="flex items-center lg:hidden">
                    <button @click="open = !open"
                        class="inline-flex items-center justify-center p-2 rounded-xl text-slate-600 hover:text-primary-500 hover:bg-slate-100 focus:outline-none transition-colors">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path :class="{'hidden': open, 'inline-flex': !open }" class="inline-flex"
                                stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                            <path :class="{'hidden': !open, 'inline-flex': open }" class="hidden" stroke-linecap="round"
                                stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile / Tablet Navigation Menu (XL hidden) -->
        <div x-show="open" x-transition:enter="transition ease-out duration-200 transform"
            x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150 transform"
            x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2"
            class="xl:hidden bg-white/98 backdrop-blur-lg border-t border-slate-100 px-4 pt-3 pb-6 shadow-xl" x-cloak>
            <div class="space-y-1 sm:px-2">
                <a href="{{ route('home') }}"
                    class="block px-4 py-2.5 rounded-xl text-sm font-bold transition-all {{ Route::is('home') ? 'bg-primary-50 text-primary-500' : 'text-slate-700 hover:bg-slate-50' }}">
                    {{ __('messages.home') }}
                </a>
                <a href="{{ route('events') }}"
                    class="block px-4 py-2.5 rounded-xl text-sm font-bold transition-all {{ Route::is('events') || Route::is('event.details') ? 'bg-primary-50 text-primary-500' : 'text-slate-700 hover:bg-slate-50' }}">
                    {{ __('messages.events') }}
                </a>
                <a href="{{ route('updates') }}"
                    class="block px-4 py-2.5 rounded-xl text-sm font-bold transition-all {{ Route::is('updates') || Route::is('update.details') ? 'bg-primary-50 text-primary-500' : 'text-slate-700 hover:bg-slate-50' }}">
                    {{ __('messages.updates') }}
                </a>
                <a href="{{ route('gallery') }}"
                    class="block px-4 py-2.5 rounded-xl text-sm font-bold transition-all {{ Route::is('gallery') ? 'bg-primary-50 text-primary-500' : 'text-slate-700 hover:bg-slate-50' }}">
                    {{ __('messages.gallery') }}
                </a>
                <a href="{{ route('business.directory') }}"
                    class="block px-4 py-2.5 rounded-xl text-sm font-bold transition-all {{ Route::is('business.directory') || Route::is('business.details') ? 'bg-primary-50 text-primary-500' : 'text-slate-700 hover:bg-slate-50' }}">
                    {{ __('messages.business_directory') }}
                </a>

                <!-- Mobile Accordion for About Us, Management Desk & Contact Us -->
                <div x-data="{ openSubMenu: {{ Route::is('about') || Route::is('management_desk') || Route::is('contact') ? 'true' : 'false' }} }"
                    class="space-y-1">
                    <button @click="openSubMenu = !openSubMenu"
                        class="flex items-center justify-between w-full px-4 py-2.5 rounded-xl text-sm font-bold transition-all {{ Route::is('about') || Route::is('management_desk') || Route::is('contact') ? 'bg-primary-50 text-primary-500' : 'text-slate-700 hover:bg-slate-50' }}">
                        <span>
                            @if(Route::is('management_desk'))
                                {{ __('messages.management_desk') }}
                            @elseif(Route::is('contact'))
                                {{ __('messages.contact_us') }}
                            @else
                                {{ __('messages.about_us') }}
                            @endif
                        </span>
                        <svg class="w-4 h-4 transition-transform duration-200"
                            :class="openSubMenu ? 'rotate-180 text-primary-500' : 'text-slate-400'" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="openSubMenu" class="pl-4 space-y-1 border-l-2 border-primary-100 ml-4 my-1" x-cloak>
                        <a href="{{ route('about') }}"
                            class="block px-3 py-2 rounded-lg text-xs font-bold transition-all {{ Route::is('about') ? 'bg-primary-50 text-primary-600 font-extrabold' : 'text-slate-600 hover:text-primary-500 hover:bg-slate-50' }}">
                            {{ __('messages.about_us') }}
                        </a>
                        <a href="{{ route('management_desk') }}"
                            class="block px-3 py-2 rounded-lg text-xs font-bold transition-all {{ Route::is('management_desk') ? 'bg-primary-50 text-primary-600 font-extrabold' : 'text-slate-600 hover:text-primary-500 hover:bg-slate-50' }}">
                            {{ __('messages.management_desk') }}
                        </a>
                        <a href="{{ route('contact') }}"
                            class="block px-3 py-2 rounded-lg text-xs font-bold transition-all {{ Route::is('contact') ? 'bg-primary-50 text-primary-600 font-extrabold' : 'text-slate-600 hover:text-primary-500 hover:bg-slate-50' }}">
                            {{ __('messages.contact_us') }}
                        </a>
                    </div>
                </div>

                <div class="border-t border-slate-100 my-3 pt-3 px-2 flex justify-between items-center">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('messages.language') }}:</span>
                    <div class="flex space-x-2">
                        <a href="{{ route('locale.set', 'en') }}"
                            class="px-3 py-1.5 text-xs font-extrabold rounded-lg transition-colors {{ app()->getLocale() == 'en' ? 'bg-primary-500 text-white shadow-xs' : 'bg-slate-100 text-slate-700' }}">EN</a>
                        <a href="{{ route('locale.set', 'gu') }}"
                            class="px-3 py-1.5 text-xs font-extrabold rounded-lg font-gujarati transition-colors {{ app()->getLocale() == 'gu' ? 'bg-primary-500 text-white shadow-xs' : 'bg-slate-100 text-slate-700' }}">GU</a>
                    </div>
                </div>

                <div class="border-t border-slate-100 pt-3">
                    @auth
                        <div class="pt-1">
                            @if(auth()->user()->hasRole('Administrator'))
                                <a href="{{ route('admin.dashboard') }}"
                                    class="block w-full text-center px-4 py-3 bg-slate-900 text-white font-extrabold rounded-xl text-sm uppercase tracking-wider shadow-md">
                                    {{ __('messages.admin_portal') }}
                                </a>
                            @else
                                <a href="{{ route('member.dashboard') }}"
                                    class="block w-full text-center px-4 py-3 bg-gradient-to-r from-primary-600 to-primary-500 text-white font-extrabold rounded-xl text-sm uppercase tracking-wider shadow-md shadow-primary-500/25">
                                    {{ __('messages.view_dashboard') }}
                                </a>
                            @endif
                        </div>
                    @else
                        <div class="space-y-2 pt-1">
                            <a href="{{ route('login') }}"
                                class="block w-full text-center px-4 py-2.5 border border-slate-200 text-slate-700 font-extrabold rounded-xl text-xs uppercase tracking-wider hover:bg-slate-50 transition-colors">
                                {{ __('messages.login') }}
                            </a>
                            <a href="{{ route('register.member') }}"
                                class="block w-full text-center px-4 py-3 bg-gradient-to-r from-primary-600 to-primary-500 text-white font-extrabold rounded-xl text-sm uppercase tracking-wider shadow-md shadow-primary-500/25">
                                {{ __('messages.register') }}
                            </a>
                        </div>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow pt-20 relative">
        <!-- Toast Alerts -->
        @if (session('success') || session('error') || session('warning') || session('info'))
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-2" x-data="{ show: true }"
                x-init="setTimeout(() => show = false, 5000)" x-show="show" x-transition>
                @if (session('success'))
                    <div
                        class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span class="text-sm font-semibold">{{ session('success') }}</span>
                        </div>
                        <button @click="show = false"
                            class="text-emerald-500 hover:text-emerald-700 font-bold text-lg">&times;</button>
                    </div>
                @endif
                @if (session('error'))
                    <div
                        class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span class="text-sm font-semibold">{{ session('error') }}</span>
                        </div>
                        <button @click="show = false"
                            class="text-rose-500 hover:text-rose-700 font-bold text-lg">&times;</button>
                    </div>
                @endif
                @if (session('warning'))
                    <div
                        class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <svg class="w-5 h-5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <span class="text-sm font-semibold">{{ session('warning') }}</span>
                        </div>
                        <button @click="show = false"
                            class="text-amber-500 hover:text-amber-700 font-bold text-lg">&times;</button>
                    </div>
                @endif
                @if (session('info'))
                    <div
                        class="p-4 rounded-xl bg-blue-50 border border-blue-200 text-blue-800 flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <span>ℹ️</span>
                            <span class="text-sm font-semibold">{{ session('info') }}</span>
                        </div>
                        <button @click="show = false"
                            class="text-blue-500 hover:text-blue-700 font-bold text-lg">&times;</button>
                    </div>
                @endif
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Modern Sober Global Footer -->
    <footer class="bg-slate-50 border-t border-slate-200/80 relative z-10 overflow-hidden py-10 sm:py-12">
        <!-- Top Gradient Accent Bar -->
        <!-- <div class="h-1 w-full bg-gradient-to-r from-primary-500 via-rose-400 to-primary-500 absolute top-0 left-0"></div> -->

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 lg:gap-10">
                <!-- Brand Info -->
                <div class="space-y-3 md:col-span-1">
                    <div class="flex items-center gap-2.5">
                        @if(App\Models\Setting::get('website_logo'))
                            <img class="h-8 w-auto object-contain rounded-lg"
                                src="{{ asset('storage/' . App\Models\Setting::get('website_logo')) }}"
                                alt="{{ App\Models\Setting::get('website_name', 'Satwara') }}">
                        @else
                            <div
                                class="w-8 h-8 rounded-xl bg-gradient-to-tr from-primary-600 via-primary-500 to-rose-500 flex items-center justify-center text-white font-black text-sm shadow-md shadow-primary-500/20">
                                S
                            </div>
                        @endif
                        <span class="font-extrabold text-lg text-slate-900 tracking-tight">
                            {{ App\Models\Setting::get('website_name', 'Shree Satwara Gnati Mandal, Ahmedabad') }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        {{ App\Models\Setting::get('seo_description', 'Connecting families and empowering local community-owned businesses.') }}
                    </p>
                </div>

                <!-- Quick Navigation (Fixed Double About Us) -->
                <div class="space-y-3">
                    <h3 class="text-xs font-black uppercase tracking-wider text-slate-400">
                        {{ __('messages.quick_navigation') }}
                    </h3>
                    <ul class="space-y-2 text-xs font-medium">
                        <li>
                            <a href="{{ route('about') }}"
                                class="group flex items-center gap-2 text-slate-600 hover:text-primary-600 transition-colors">
                                <span
                                    class="w-1.5 h-1.5 rounded-full bg-slate-300 group-hover:bg-primary-500 group-hover:scale-125 transition-all duration-200"></span>
                                <span
                                    class="group-hover:translate-x-1 transition-transform duration-200">{{ __('messages.about_us') }}</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('management_desk') }}"
                                class="group flex items-center gap-2 text-slate-600 hover:text-primary-600 transition-colors">
                                <span
                                    class="w-1.5 h-1.5 rounded-full bg-slate-300 group-hover:bg-primary-500 group-hover:scale-125 transition-all duration-200"></span>
                                <span
                                    class="group-hover:translate-x-1 transition-transform duration-200">{{ __('messages.management_desk') }}</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('events') }}"
                                class="group flex items-center gap-2 text-slate-600 hover:text-primary-600 transition-colors">
                                <span
                                    class="w-1.5 h-1.5 rounded-full bg-slate-300 group-hover:bg-primary-500 group-hover:scale-125 transition-all duration-200"></span>
                                <span
                                    class="group-hover:translate-x-1 transition-transform duration-200">{{ __('messages.events') }}</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('gallery') }}"
                                class="group flex items-center gap-2 text-slate-600 hover:text-primary-600 transition-colors">
                                <span
                                    class="w-1.5 h-1.5 rounded-full bg-slate-300 group-hover:bg-primary-500 group-hover:scale-125 transition-all duration-200"></span>
                                <span
                                    class="group-hover:translate-x-1 transition-transform duration-200">{{ __('messages.gallery') }}</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Directories -->
                <div class="space-y-3">
                    <h3 class="text-xs font-black uppercase tracking-wider text-slate-400">
                        {{ __('messages.directories_links') }}
                    </h3>
                    <ul class="space-y-2 text-xs font-medium">
                        <li>
                            <a href="{{ route('business.directory') }}"
                                class="group flex items-center gap-2 text-slate-600 hover:text-primary-600 transition-colors">
                                <span
                                    class="w-1.5 h-1.5 rounded-full bg-slate-300 group-hover:bg-primary-500 group-hover:scale-125 transition-all duration-200"></span>
                                <span
                                    class="group-hover:translate-x-1 transition-transform duration-200">{{ __('messages.business_directory') }}</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('register.business') }}"
                                class="group flex items-center gap-2 text-slate-600 hover:text-primary-600 transition-colors">
                                <span
                                    class="w-1.5 h-1.5 rounded-full bg-slate-300 group-hover:bg-primary-500 group-hover:scale-125 transition-all duration-200"></span>
                                <span
                                    class="group-hover:translate-x-1 transition-transform duration-200">{{ __('messages.business_registration') }}</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('register.member') }}"
                                class="group flex items-center gap-2 text-slate-600 hover:text-primary-600 transition-colors">
                                <span
                                    class="w-1.5 h-1.5 rounded-full bg-slate-300 group-hover:bg-primary-500 group-hover:scale-125 transition-all duration-200"></span>
                                <span
                                    class="group-hover:translate-x-1 transition-transform duration-200">{{ __('messages.member_registration') }}</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Modernized Contact Us Section -->
                <div class="space-y-3">
                    <h3 class="text-xs font-black uppercase tracking-wider text-slate-400">
                        {{ __('messages.contact_us') }}
                    </h3>
                    <div class="space-y-2.5">
                        <!-- Address -->
                        @if(App\Models\Setting::get('contact_address'))
                            <div
                                class="group flex items-start gap-2.5 p-2 rounded-xl bg-slate-50 border border-transparent hover:border-slate-200/60 transition-all duration-200">
                                <div
                                    class="w-7 h-7 rounded-lg bg-primary-600 text-primary-600 flex items-center justify-center text-xs shrink-0 group-hover:bg-primary-600 group-hover:text-white transition-colors duration-200 shadow-2xs">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </div>
                                <span
                                    class="text-xs text-slate-600 leading-snug whitespace-pre-line group-hover:text-slate-900 transition-colors">
                                    {{ App\Models\Setting::get('contact_address') }}
                                </span>
                            </div>
                        @endif

                        <!-- Phone -->
                        @if(App\Models\Setting::get('contact_phone'))
                            <a href="tel:{{ App\Models\Setting::get('contact_phone') }}"
                                class="group flex items-center gap-2.5 p-2 rounded-xl hover:bg-slate-50 border border-transparent hover:border-slate-200/60 transition-all duration-200">
                                <div
                                    class="w-7 h-7 rounded-lg bg-emerald-600 text-emerald-600 flex items-center justify-center text-xs shrink-0 group-hover:bg-emerald-600 group-hover:text-white transition-colors duration-200 shadow-2xs">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                    </svg>
                                </div>
                                <span
                                    class="text-xs font-bold text-slate-700 group-hover:text-emerald-600 transition-colors">
                                    {{ App\Models\Setting::get('contact_phone') }}
                                </span>
                            </a>
                        @endif

                        <!-- Email -->
                        @if(App\Models\Setting::get('contact_email'))
                            <a href="mailto:{{ App\Models\Setting::get('contact_email') }}"
                                class="group flex items-center gap-2.5 p-2 rounded-xl hover:bg-slate-50 border border-transparent hover:border-slate-200/60 transition-all duration-200">
                                <div
                                    class="w-7 h-7 rounded-lg bg-rose-600 text-rose-600 flex items-center justify-center text-xs shrink-0 group-hover:bg-rose-600 group-hover:text-white transition-colors duration-200 shadow-2xs">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 002-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <span
                                    class="text-xs font-bold text-slate-700 group-hover:text-rose-600 transition-colors truncate">
                                    {{ App\Models\Setting::get('contact_email') }}
                                </span>
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Footer Bottom Bar -->
            <div
                class="border-t border-slate-100 mt-8 pt-6 flex flex-col sm:flex-row justify-between items-center text-xs text-slate-500 gap-4">
                <span>
                    © {{ date('Y') }} {{ App\Models\Setting::get('website_name', 'Shree Satwara Gnati Mandal, Ahmedabad') }}.
                    {{ __('messages.all_rights_reserved') }}
                </span>

                <div class="flex items-center gap-2 flex-wrap">
                    @php
                        $fbUrl = trim(App\Models\Setting::get('social_facebook', ''));
                        $twUrl = trim(App\Models\Setting::get('social_twitter', ''));
                        $igUrl = trim(App\Models\Setting::get('social_instagram', ''));
                        $ytUrl = trim(App\Models\Setting::get('social_youtube', ''));
                    @endphp

                    @if($fbUrl)
                        <a href="{{ $fbUrl }}" target="_blank"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200/80 text-slate-700 text-xs font-bold shadow-2xs">
                            <svg class="w-3.5 h-3.5 fill-current text-blue-600" viewBox="0 0 24 24">
                                <path
                                    d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                            </svg>
                            <span>{{ __('messages.facebook') }}</span>
                        </a>
                    @endif

                    @if($twUrl)
                        <a href="{{ $twUrl }}" target="_blank"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200/80 text-slate-700 text-xs font-bold shadow-2xs">
                            <svg class="w-3.5 h-3.5 fill-current text-slate-900" viewBox="0 0 24 24">
                                <path
                                    d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" />
                            </svg>
                            <span>{{ __('messages.twitter') }}</span>
                        </a>
                    @endif

                    @if($igUrl)
                        <a href="{{ $igUrl }}" target="_blank"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200/80 text-slate-700 text-xs font-bold shadow-2xs">
                            <svg class="w-3.5 h-3.5 fill-current text-rose-500" viewBox="0 0 24 24">
                                <path
                                    d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z" />
                            </svg>
                            <span>{{ __('messages.instagram') }}</span>
                        </a>
                    @endif

                    @if($ytUrl)
                        <a href="{{ $ytUrl }}" target="_blank"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200/80 text-slate-700 text-xs font-bold shadow-2xs">
                            <svg class="w-3.5 h-3.5 fill-current text-red-600" viewBox="0 0 24 24">
                                <path
                                    d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z" />
                            </svg>
                            <span>{{ __('messages.youtube') }}</span>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </footer>
    @include('partials.global_loader')
    @include('partials.delete_confirm_modal')
    @include('components.purchase_receipt_modal')
</body>

</html>