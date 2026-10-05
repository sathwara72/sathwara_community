<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    class="{{ app()->getLocale() == 'gu' ? 'font-gujarati' : 'font-sans' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? App\Models\Setting::get('website_name', 'Admin Dashboard') }}</title>
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

    <!-- Quill Rich Text Editor Assets -->
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>

    {{--
    @php
    $primaryColor = App\Models\Setting::get('primary_color', '#ef4444');
    @endphp
    <style>
        :root {
            --primary-hex: {
                    {
                    $primaryColor
                }
            }

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
            background-color: var(--primary-hex) !important;
            opacity: 0.1;
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

        /* Hide scrollbar for Chrome, Safari, Edge and Firefox */
        *,
        .no-scrollbar {
            -ms-overflow-style: none !important;
            /* IE and Edge */
            scrollbar-width: none !important;
            /* Firefox */
        }

        .no-scrollbar::-webkit-scrollbar,
        ::-webkit-scrollbar {
            display: none !important;
            width: 0 !important;
            height: 0 !important;
            background: transparent !important;
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
    --}}

    <style>
        .font-gujarati {
            font-family: 'Noto Sans Gujarati', sans-serif !important;
        }

        .font-sans,
        body {
            font-family: 'Plus Jakarta Sans', sans-serif !important;
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

        /* Sidebar Scrollbar hidden for sleek dark menu */
        aside,
        aside .overflow-y-auto {
            -ms-overflow-style: none !important;
            scrollbar-width: none !important;
        }

        aside::-webkit-scrollbar,
        aside .overflow-y-auto::-webkit-scrollbar {
            display: none !important;
            width: 0 !important;
            height: 0 !important;
        }

        /* Custom Scrollbar Styles for standard page overflow */
        html {
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 #f1f5f9;
        }

        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Crisp & Readable Admin Panel Typography */
        html {
            font-size: 16px;
        }

        /* Enhanced Admin Table Typography */
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
        aside a, aside button {
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
    </style>
</head>

<body x-data="{ sidebarOpen: false }"
    class="bg-slate-50 text-slate-800 antialiased min-h-screen flex flex-col md:flex-row">
    <!-- Mobile Sidebar Backdrop -->
    <div x-show="sidebarOpen" @click="sidebarOpen = false" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0" class="fixed inset-0 z-40 bg-slate-950/40 backdrop-blur-sm md:hidden"
        x-cloak>
    </div>

    <!-- Sidebar -->
    <aside
        class="fixed inset-y-0 left-0 z-50 w-64 bg-black text-slate-300 flex flex-col shrink-0 transition-transform duration-300 transform md:translate-x-0 md:static md:h-screen md:sticky md:top-0 overflow-hidden"
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" x-cloak>
        <!-- Sidebar Header -->
        <div class="h-16 flex items-center justify-between px-6 border-b border-zinc-900">
            <a href="{{ route('home') }}" class="flex items-center space-x-3">
                @if(App\Models\Setting::get('website_logo'))
                    <img src="{{ asset('storage/' . App\Models\Setting::get('website_logo')) }}" alt="Logo"
                        class="w-8 h-8 rounded-lg object-contain bg-white p-0.5 shadow-sm">
                @else
                    <img src="{{ asset('logo.png') }}" alt="Logo"
                        class="w-8 h-8 rounded-lg object-contain bg-white p-0.5 shadow-sm">
                @endif
                <span class="font-extrabold text-base text-white tracking-wide">{{ __('messages.admin_portal') }}</span>
            </a>
            <!-- Mobile Close Button -->
            <button @click="sidebarOpen = false" class="md:hidden text-slate-400 hover:text-white focus:outline-none">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Sidebar Navigation -->
        <div class="flex-grow p-4 space-y-1 overflow-y-auto">
            <a href="{{ route('admin.dashboard') }}"
                class="flex items-center space-x-3 px-4 py-2.5 text-xs font-bold rounded-lg {{ Route::is('admin.dashboard') ? 'bg-primary-500 text-white' : 'text-slate-400 hover:bg-zinc-900 hover:text-white' }} transition-colors">
                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2" />
                </svg>
                <span>{{ __('messages.dashboard_overview') }}</span>
            </a>

            <div class="pt-4 pb-1 text-[10px] font-extrabold uppercase text-slate-500 tracking-widest px-4">
                {{ __('messages.core_management') }}
            </div>

            @php
                $user = auth()->user();
                $userPerms = $user->permissions->pluck('name');

                $hasMembers = $user->hasRole('Administrator') || $userPerms->contains('members_manage') || $userPerms->contains(fn($p) => str_starts_with($p, 'members_'));
                $hasAreas = $user->hasRole('Administrator') || $userPerms->contains('areas_manage') || $userPerms->contains(fn($p) => str_starts_with($p, 'areas_'));
                $hasBusinesses = $user->hasRole('Administrator') || $userPerms->contains('businesses_manage') || $userPerms->contains(fn($p) => str_starts_with($p, 'businesses_'));
                $hasEvents = $user->hasRole('Administrator') || $userPerms->contains('events_manage') || $userPerms->contains(fn($p) => str_starts_with($p, 'event_') || str_starts_with($p, 'events_'));
                $hasGallery = $user->hasRole('Administrator') || $userPerms->contains('gallery_manage') || $userPerms->contains(fn($p) => str_starts_with($p, 'gallery_'));
                $hasSliders = $user->hasRole('Administrator') || $userPerms->contains('sliders_manage') || $userPerms->contains(fn($p) => str_starts_with($p, 'sliders_'));
                $hasAgendas = $user->hasRole('Administrator') || $userPerms->contains('agendas_manage') || $userPerms->contains(fn($p) => str_starts_with($p, 'agendas_'));
                $hasDesk = $user->hasRole('Administrator') || $userPerms->contains('desk_manage') || $userPerms->contains(fn($p) => str_starts_with($p, 'desk_'));
                $hasCommittee = $user->hasRole('Administrator') || $userPerms->contains('committee_manage') || $userPerms->contains(fn($p) => str_starts_with($p, 'committee_'));
                $hasTimelines = $user->hasRole('Administrator') || $userPerms->contains('timelines_manage') || $userPerms->contains(fn($p) => str_starts_with($p, 'timelines_'));
                $hasAbout = $user->hasRole('Administrator') || $userPerms->contains('about_manage') || $userPerms->contains(fn($p) => str_starts_with($p, 'about_'));
                $hasAnnouncements = $user->hasRole('Administrator') || $userPerms->contains('announcements_manage') || $userPerms->contains(fn($p) => str_starts_with($p, 'announcements_'));
                $hasSettings = $user->hasRole('Administrator') || $userPerms->contains('settings_manage') || $userPerms->contains(fn($p) => str_starts_with($p, 'settings_'));
                $hasTransactions = $user->hasRole('Administrator') || $userPerms->contains('transactions_view');
            @endphp

            @role('Administrator')
            <a href="{{ route('admin.sub_admins.index') }}"
                class="flex items-center space-x-3 px-4 py-2.5 text-xs font-bold rounded-lg {{ Route::is('admin.sub_admins.*') ? 'bg-primary-500 text-white' : 'text-slate-400 hover:bg-zinc-900 hover:text-white' }} transition-colors">
                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                <span>{{ __('messages.sub_admins_access') }}</span>
            </a>
            @endrole

            @if($hasTransactions)
                <a href="{{ route('admin.transactions.index') }}"
                    class="flex items-center space-x-3 px-4 py-2.5 text-xs font-bold rounded-lg {{ Route::is('admin.transactions.*') ? 'bg-primary-500 text-white' : 'text-slate-400 hover:bg-zinc-900 hover:text-white' }} transition-colors">
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" />
                    </svg>
                    <span>{{ __('messages.transactions') }}</span>
                </a>
            @endif

            @if($hasMembers)
                <a href="{{ route('admin.members.index') }}"
                    class="flex items-center space-x-3 px-4 py-2.5 text-xs font-bold rounded-lg {{ Route::is('admin.members.*') ? 'bg-primary-500 text-white' : 'text-slate-400 hover:bg-zinc-900 hover:text-white' }} transition-colors">
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <span>{{ __('messages.members_approvals') }}</span>
                </a>
            @endif

            @if($hasAreas)
                <a href="{{ route('admin.areas.index') }}"
                    class="flex items-center space-x-3 px-4 py-2.5 text-xs font-bold rounded-lg {{ Route::is('admin.areas.*') ? 'bg-primary-500 text-white' : 'text-slate-400 hover:bg-zinc-900 hover:text-white' }} transition-colors">
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span>{{ __('messages.area_management') }}</span>
                </a>
            @endif

            @if($hasBusinesses)
                <a href="{{ route('admin.businesses.index') }}"
                    class="flex items-center space-x-3 px-4 py-2.5 text-xs font-bold rounded-lg {{ Route::is('admin.businesses.index') ? 'bg-primary-500 text-white' : 'text-slate-400 hover:bg-zinc-900 hover:text-white' }} transition-colors">
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <span>{{ __('messages.business_listings') }}</span>
                </a>
            @endif

            @if($hasEvents)
                <a href="{{ route('admin.events.index') }}"
                    class="flex items-center space-x-3 px-4 py-2.5 text-xs font-bold rounded-lg {{ Route::is('admin.events.*') ? 'bg-primary-500 text-white' : 'text-slate-400 hover:bg-zinc-900 hover:text-white' }} transition-colors">
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span>{{ __('messages.events_manager') }}</span>
                </a>
            @endif

            @if($hasGallery)
                <a href="{{ route('admin.gallery.index') }}"
                    class="flex items-center space-x-3 px-4 py-2.5 text-xs font-bold rounded-lg {{ Route::is('admin.gallery.*') ? 'bg-primary-500 text-white' : 'text-slate-400 hover:bg-zinc-900 hover:text-white' }} transition-colors">
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span>{{ __('messages.general_gallery') }}</span>
                </a>
            @endif

            <!-- CONTENT CONTROLS (PAGE-BASED DROPDOWNS) -->
            <div class="pt-4 pb-1 text-[10px] font-extrabold uppercase text-slate-500 tracking-widest px-4">
                {{ __('messages.content_controls') }}
            </div>

            <!-- Home Page Dropdown -->
            @if($hasSliders || $hasAgendas)
                <div
                    x-data="{ open: {{ (Route::is('admin.content.sliders') || Route::is('admin.content.agendas')) ? 'true' : 'false' }} }">
                    <button @click="open = !open"
                        class="w-full flex items-center justify-between px-4 py-2.5 text-xs font-bold rounded-lg {{ (Route::is('admin.content.sliders') || Route::is('admin.content.agendas')) ? 'text-white bg-zinc-900/80' : 'text-slate-400 hover:bg-zinc-900 hover:text-white' }} transition-colors">
                        <div class="flex items-center space-x-3">
                            <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 001 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            <span>{{ __('messages.home_page') }}</span>
                        </div>
                        <svg class="w-3.5 h-3.5 transition-transform duration-200"
                            :class="open ? 'rotate-180 text-white' : 'text-slate-500'" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div x-show="open" class="pl-7 pr-2 py-1 space-y-1" x-cloak>
                        @if($hasSliders)
                            <a href="{{ route('admin.content.sliders') }}"
                                class="flex items-center space-x-2.5 px-3 py-2 text-[11px] font-bold rounded-lg {{ Route::is('admin.content.sliders') ? 'bg-primary-500 text-white' : 'text-slate-400 hover:bg-zinc-900 hover:text-white' }} transition-colors">
                                <span
                                    class="w-1.5 h-1.5 rounded-full {{ Route::is('admin.content.sliders') ? 'bg-white' : 'bg-slate-600' }}"></span>
                                <span>{{ __('messages.hero_sliders') }}</span>
                            </a>
                        @endif
                        @if($hasAgendas)
                            <a href="{{ route('admin.content.agendas') }}"
                                class="flex items-center space-x-2.5 px-3 py-2 text-[11px] font-bold rounded-lg {{ Route::is('admin.content.agendas') ? 'bg-primary-500 text-white' : 'text-slate-400 hover:bg-zinc-900 hover:text-white' }} transition-colors">
                                <span
                                    class="w-1.5 h-1.5 rounded-full {{ Route::is('admin.content.agendas') ? 'bg-white' : 'bg-slate-600' }}"></span>
                                <span>{{ __('messages.core_agendas') }}</span>
                            </a>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Management Desk (Standalone Main Tab) -->
            @if($hasDesk)
                <a href="{{ route('admin.content.desk') }}"
                    class="flex items-center space-x-3 px-4 py-2.5 text-xs font-bold rounded-lg {{ Route::is('admin.content.desk') ? 'bg-primary-500 text-white' : 'text-slate-400 hover:bg-zinc-900 hover:text-white' }} transition-colors">
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m0 0h4m-4 0V11m0 0a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 01-2-2v-4a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2" />
                    </svg>
                    <span>{{ __('messages.management_desk') }}</span>
                </a>
            @endif

            <!-- About Us Page (Standalone Tab) -->
            @if($hasSettings || $hasTimelines || $hasAbout)
                <a href="{{ route('admin.settings.about') }}"
                    class="flex items-center space-x-3 px-4 py-2.5 text-xs font-bold rounded-lg {{ (Route::is('admin.settings.about*') || Route::is('admin.content.timelines')) ? 'bg-primary-500 text-white' : 'text-slate-400 hover:bg-zinc-900 hover:text-white' }} transition-colors">
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ __('messages.about_us_page') }}</span>
                </a>
            @endif

            @if($hasAnnouncements)
                <a href="{{ route('admin.content.updates') }}"
                    class="flex items-center space-x-3 px-4 py-2.5 text-xs font-bold rounded-lg {{ Route::is('admin.content.updates') ? 'bg-primary-500 text-white' : 'text-slate-400 hover:bg-zinc-900 hover:text-white' }} transition-colors">
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                    </svg>
                    <span>{{ __('messages.announcements') }}</span>
                </a>
            @endif

            @if($hasSettings)
                <div class="pt-4 pb-1 text-[10px] font-extrabold uppercase text-slate-500 tracking-widest px-4">
                    {{ __('messages.configuration') }}
                </div>
                <a href="{{ route('admin.settings.index') }}"
                    class="flex items-center space-x-3 px-4 py-2.5 text-xs font-bold rounded-lg {{ ((Route::is('admin.settings.*') && !Route::is('admin.settings.about*')) || Route::is('admin.email_settings.*')) ? 'bg-primary-500 text-white' : 'text-slate-400 hover:bg-zinc-900 hover:text-white' }} transition-colors">
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span>{{ __('messages.system_settings') }}</span>
                </a>
            @endif

            <div class="border-t border-zinc-900 my-4"></div>
            <a href="{{ route('home') }}"
                class="flex items-center space-x-3 px-4 py-2.5 text-xs font-bold rounded-lg text-slate-400 hover:bg-zinc-900 hover:text-white transition-colors">
                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span>{{ __('messages.back_to_website') }}</span>
            </a>

            <!-- Logout -->
            <form method="POST" action="{{ route('admin.logout') }}" class="w-full">
                @csrf
                <button type="submit"
                    class="w-full flex items-center space-x-3 px-4 py-2.5 text-xs font-bold rounded-lg text-rose-400 hover:bg-rose-950 hover:text-rose-300 transition-colors text-left">
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    <span>{{ __('messages.sign_out') }}</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-grow flex flex-col min-w-0">
        <!-- Top Navigation -->
        <header class="h-16 bg-white border-b border-slate-100 flex items-center justify-between px-3 shrink-0">
            <div class="flex items-center space-x-3">
                <!-- Mobile Open Menu Toggle -->
                <button @click="sidebarOpen = true"
                    class="md:hidden text-slate-500 hover:text-slate-900 focus:outline-none">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <h2 class="font-extrabold text-xl text-slate-950">@yield('page_title', 'Admin Dashboard')</h2>
            </div>

            <div class="flex items-center space-x-2">
                <!-- Notifications -->
                <div class="relative" x-data="{ showNotif: false }">
                    <button @click="showNotif = !showNotif"
                        class="relative inline-flex items-center justify-center w-10 h-10 rounded-xl text-slate-500 hover:bg-slate-100 hover:text-slate-900 transition-colors focus:outline-none">
                        <svg class="w-5 h-5 text-slate-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                        </svg>
                        @if($unreadNotificationsCount > 0)
                            <span class="absolute top-0.5 right-0.5 flex items-center justify-center min-w-[17px] h-[17px] px-1 bg-rose-500 text-white text-[9.5px] font-black rounded-full ring-2 ring-white shadow-2xs leading-none">
                                {{ $unreadNotificationsCount > 9 ? '9+' : $unreadNotificationsCount }}
                            </span>
                        @endif
                    </button>
                    <div x-show="showNotif" @click.away="showNotif = false"
                        class="absolute right-0 mt-2 w-80 sm:w-96 bg-white border border-slate-100 rounded-lg shadow-lg z-30 overflow-hidden"
                        x-cloak>
                        <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100">
                            <span class="text-xs font-black text-slate-900 uppercase tracking-wider">{{ __('messages.notifications') }}</span>
                            @if($unreadNotificationsCount > 0)
                                <form method="POST" action="{{ route('admin.notifications.markAllRead') }}">
                                    @csrf
                                    <button type="submit" class="text-[11px] font-bold text-primary-600 hover:text-primary-700">{{ __('messages.mark_all_read') }}</button>
                                </form>
                            @endif
                        </div>

                        @php
                            // Written out literally (not interpolated) so Tailwind's class scanner keeps these in the build.
                            $notifColorClasses = [
                                'primary' => 'bg-primary-50 text-primary-600',
                                'emerald' => 'bg-emerald-50 text-emerald-600',
                                'sky' => 'bg-sky-50 text-sky-600',
                                'amber' => 'bg-amber-50 text-amber-600',
                                'rose' => 'bg-rose-50 text-rose-600',
                            ];
                        @endphp
                        <div class="max-h-96 overflow-y-auto divide-y divide-slate-50">
                            @forelse($recentNotifications as $n)
                                <a href="{{ route('admin.notifications.read', $n->id) }}"
                                   class="flex items-start gap-3 px-4 py-3 hover:bg-slate-50 transition-colors {{ is_null($n->read_at) ? 'bg-primary-50/40' : '' }}">
                                    <span class="w-8 h-8 rounded-lg {{ $notifColorClasses[$n->data['color'] ?? 'primary'] ?? $notifColorClasses['primary'] }} flex items-center justify-center shrink-0 mt-0.5">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                        </svg>
                                    </span>
                                    <span class="min-w-0 flex-1">
                                        <span class="flex items-center gap-1.5">
                                            <span class="text-xs font-extrabold text-slate-900 truncate">{{ $n->data['title'] ?? '' }}</span>
                                            @if(is_null($n->read_at))
                                                <span class="w-1.5 h-1.5 rounded-full bg-primary-500 shrink-0"></span>
                                            @endif
                                        </span>
                                        <span class="block text-[11px] text-slate-500 leading-snug line-clamp-2 mt-0.5">{{ $n->data['message'] ?? '' }}</span>
                                        <span class="block text-[10px] text-slate-400 font-semibold mt-1">{{ $n->created_at->diffForHumans() }}</span>
                                    </span>
                                </a>
                            @empty
                                <div class="px-4 py-8 text-center text-xs text-slate-400 font-medium">
                                    {{ __('messages.no_notifications_yet') }}
                                </div>
                            @endforelse
                        </div>

                        <a href="{{ route('admin.notifications.index') }}" class="block text-center px-4 py-2.5 text-[11px] font-bold text-primary-600 hover:bg-slate-50 border-t border-slate-100">
                            {{ __('messages.view_all_notifications') }}
                        </a>
                    </div>
                </div>

                <!-- Language Toggle -->
                <div class="relative" x-data="{ showLang: false }">
                    <button @click="showLang = !showLang"
                        class="inline-flex items-center px-3 py-1.5 border border-slate-200 text-xs font-bold rounded-lg text-slate-700 bg-slate-50 hover:bg-slate-100 transition-colors">
                        🌐 {{ app()->getLocale() == 'en' ? 'English' : 'ગુજરાતી' }}
                    </button>
                    <div x-show="showLang" @click.away="showLang = false"
                        class="absolute right-0 mt-2 w-32 bg-white border border-slate-100 rounded-lg shadow-lg py-1 z-30"
                        x-cloak>
                        <a href="{{ route('locale.set', 'en') }}"
                            class="block px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">English</a>
                        <a href="{{ route('locale.set', 'gu') }}"
                            class="block px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 font-gujarati">ગુજરાતી</a>
                    </div>
                </div>

                <span class="text-xs font-bold bg-primary-50 text-primary-600 px-3 py-1.5 rounded-lg">
                    {{ __('messages.administrator') }}:
                    <!-- {{ auth()->user()->display_name }} -->
                    {{ auth()->user()->hasRole('Administrator') ? 'Admin' : auth()->user()->display_name }}
                </span>
            </div>
        </header>

        <!-- Dynamic Content Body -->
        <main class="flex-grow px-4 py-3">
            <!-- Toast Alerts -->
            @if (session('success') || session('error') || session('warning') || session('info'))
                <div class="mb-6" x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show"
                    x-transition class="relative">
                    @if (session('success'))
                        <div
                            class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between shadow-sm">
                            <span class="text-xs font-bold">✅ {{ session('success') }}</span>
                            <button type="button" @click="show = false"
                                class="text-emerald-500 hover:text-emerald-700 font-extrabold text-base focus:outline-none">&times;</button>
                        </div>
                    @endif
                    @if (session('error'))
                        <div
                            class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 flex items-center justify-between shadow-sm">
                            <span class="text-xs font-bold">❌ {{ session('error') }}</span>
                            <button type="button" @click="show = false"
                                class="text-rose-500 hover:text-rose-700 font-extrabold text-base focus:outline-none">&times;</button>
                        </div>
                    @endif
                    @if (session('warning'))
                        <div
                            class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 flex items-center justify-between shadow-sm">
                            <span class="text-xs font-bold">⚠️ {{ session('warning') }}</span>
                            <button type="button" @click="show = false"
                                class="text-amber-500 hover:text-amber-700 font-extrabold text-base focus:outline-none">&times;</button>
                        </div>
                    @endif
                    @if (session('info'))
                        <div
                            class="p-4 rounded-xl bg-blue-50 border border-blue-200 text-blue-800 flex items-center justify-between shadow-sm">
                            <span class="text-xs font-bold">ℹ️ {{ session('info') }}</span>
                            <button type="button" @click="show = false"
                                class="text-blue-500 hover:text-blue-700 font-extrabold text-base focus:outline-none">&times;</button>
                        </div>
                    @endif
                </div>
            @endif

            @yield('content')
        </main>
    </div>
    @include('partials.global_loader')
    @include('partials.delete_confirm_modal')
    @stack('modals')
    @stack('scripts')
</body>

</html>