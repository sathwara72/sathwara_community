<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    class="{{ app()->getLocale() == 'gu' ? 'font-gujarati' : 'font-sans' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Business Panel') — {{ App\Models\Setting::get('website_name', 'Sathwara Community') }}</title>
    @if(App\Models\Setting::get('website_favicon'))
        <link rel="icon" type="image/x-icon" href="{{ asset('storage/' . App\Models\Setting::get('website_favicon')) }}">
    @endif

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Noto+Sans+Gujarati:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>

    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @php
        $primaryColor = App\Models\Setting::get('primary_color', '#ef4444');
        $biz = Auth::guard('business')->user();
        $isGu = (app()->getLocale() === 'gu');
        $websiteLogo = App\Models\Setting::get('website_logo') ? asset('storage/' . App\Models\Setting::get('website_logo')) : asset('logo.png');
    @endphp
    <style>
        :root {
            --primary-hex: {{ $primaryColor }};
            --primary: {{ $primaryColor }};
            --primary-dark: color-mix(in srgb, var(--primary-hex) 85%, black);
            --primary-light: color-mix(in srgb, var(--primary-hex) 15%, transparent);
            --accent: #f59e0b;
            --sidebar-bg: #ffffff;
            --sidebar-hover: #f8fafc;
            --sidebar-active: {{ $primaryColor }};
            --bg: #f8fafc;
            --card-bg: #ffffff;
            --text: #0f172a;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
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

        .bg-primary-500,
        .bg-primary-600 {
            background-color: var(--primary-hex) !important;
        }

        .hover\:bg-primary-600:hover,
        .hover\:bg-primary-700:hover {
            background-color: var(--primary-hex) !important;
            filter: brightness(88%) !important;
        }

        button[class*="bg-primary"],
        a[class*="bg-primary"] {
            color: #ffffff !important;
        }

        button[class*="bg-primary"]:hover,
        a[class*="bg-primary"]:hover {
            color: #ffffff !important;
            filter: brightness(88%) !important;
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

        /* Card styles for views */
        .card {
            background: #ffffff;
            border-radius: 1rem;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        .card-header {
            padding: 16px 20px;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .card-header h5 {
            font-size: 14px;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
        }
        .card-body {
            padding: 20px;
        }

        .alert {
            padding: 14px 18px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 13px;
            font-weight: 600;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }
        .alert-success { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
        .alert-danger  { background: #fff1f2; color: #9f1239; border: 1px solid #fecdd3; }
        .alert-warning { background: #fffbeb; color: #92400e; border: 1px solid #fde68a; }
    </style>
    @stack('styles')
</head>

<body x-data="{ sidebarOpen: false }"
    class="text-slate-800 antialiased min-h-screen flex flex-col md:flex-row relative">

    <!-- Soft Decorative Background Gradients -->
    <div class="fixed inset-0 bg-slate-50 z-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 right-20 w-96 h-96 rounded-full blur-3xl opacity-20"
            style="background-color: var(--primary-hex);"></div>
        <div class="absolute bottom-[-100px] left-10 w-80 h-80 bg-rose-500 rounded-full blur-3xl opacity-10"></div>
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

        <!-- Sidebar Top Header / Brand & Business Info -->
        <div class="py-3.5 px-4 border-b border-slate-100 shrink-0">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3 min-w-0 flex-1">
                    <div class="w-10 h-10 rounded-xl overflow-hidden bg-slate-100 border border-slate-200 shrink-0 flex items-center justify-center font-black text-sm text-white shadow-xs"
                        style="background: linear-gradient(135deg, var(--primary-hex), #991b1b);">
                        @if($biz && $biz->logo_path)
                            <img src="{{ Storage::url($biz->logo_path) }}" alt="{{ $biz->business_name }}" class="w-full h-full object-cover">
                        @else
                            {{ strtoupper(substr($biz->business_name ?? 'B', 0, 1)) }}
                        @endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <h4 class="text-xs sm:text-[13px] font-extrabold text-slate-900 leading-snug truncate">
                            {{ $biz->business_name ?? 'Business Panel' }}
                        </h4>
                        <div class="mt-0.5">
                            @if($biz && $biz->status === 'approved' && $biz->membership_status === 'active')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Approved
                                </span>
                            @elseif($biz && $biz->status === 'pending')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Pending
                                </span>
                            @elseif($biz && $biz->status === 'rejected')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Rejected
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Inactive
                                </span>
                            @endif
                        </div>
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
        </div>

        <!-- Sidebar Navigation Links -->
        <div class="flex-grow p-3 space-y-1.5 overflow-y-auto">
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest px-3 pt-2 pb-1">
                {{ __('MAIN MENU') }}
            </div>

            <!-- My Listing -->
            <a href="{{ route('business.profile.edit') }}"
                class="flex items-center space-x-3 px-3.5 py-2.5 text-xs font-bold rounded-xl {{ Route::is('business.profile.*') ? 'bg-primary-50 text-primary-500' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }} transition-colors">
                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <span>{{ __('My Listing') }}</span>
            </a>

            <!-- Renewal & Invoices -->
            <a href="{{ route('business.renewal') }}"
                class="flex items-center space-x-3 px-3.5 py-2.5 text-xs font-bold rounded-xl {{ Route::is('business.renewal*') ? 'bg-primary-50 text-primary-500' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }} transition-colors">
                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <span>{{ __('Renewal & Invoices') }}</span>
                @if($biz && $biz->isRenewalDue())
                    <span class="ml-auto px-2 py-0.5 text-[10px] font-extrabold rounded-full bg-rose-100 text-rose-700 animate-pulse">{{ __('Expired') }}</span>
                @endif
            </a>

            <!-- Section: Links -->
            <div class="border-t border-slate-100 my-2"></div>
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest px-3 pt-1 pb-1">
                {{ __('EXPLORE') }}
            </div>

            <!-- Preview -->
            @if($biz)
            <a href="{{ route('business.details', $biz->id) }}" target="_blank"
                class="flex items-center space-x-3 px-3.5 py-2.5 text-xs font-bold rounded-xl text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-colors">
                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <span>{{ __('Preview') }}</span>
                <span class="text-[10px] text-slate-400 ml-auto">↗</span>
            </a>
            @endif

            <!-- Back to Website -->
            <a href="{{ route('home') }}"
                class="flex items-center space-x-3 px-3.5 py-2.5 text-xs font-bold rounded-xl text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-colors">
                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span>{{ __('Back to Website') }}</span>
            </a>

            <!-- Logout -->
            <form method="POST" action="{{ route('business.logout') }}" class="w-full pt-1">
                @csrf
                <button type="submit"
                    class="w-full flex items-center space-x-3 px-3.5 py-2.5 text-xs font-bold rounded-xl text-rose-600 hover:bg-rose-50 hover:text-rose-700 transition-colors text-left cursor-pointer">
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    <span>{{ __('Logout') }}</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-grow flex flex-col min-w-0 relative z-10">
        <!-- Top Navigation Header -->
        <header class="h-16 bg-white border-b border-slate-100 flex items-center justify-between px-4 sm:px-6 lg:px-8 shrink-0">
            <div class="flex items-center gap-3 min-w-0">
                <button @click="sidebarOpen = true"
                    class="md:hidden text-slate-500 hover:text-slate-700 focus:outline-none p-1.5 rounded-lg hover:bg-slate-100 transition-colors">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <h2 class="font-extrabold text-base sm:text-lg lg:text-xl text-slate-950 truncate">
                    @hasSection('page-title')
                        @yield('page-title')
                    @elseif(View::hasSection('page_title'))
                        @yield('page_title')
                    @else
                        {{ __('Business Panel') }}
                    @endif
                </h2>
            </div>

            <div class="flex items-center space-x-4">
                <!-- Owner Name Tag -->
                @if($biz && $biz->owner_name)
                    <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 bg-slate-50 border border-slate-200/80 rounded-lg text-xs font-bold text-slate-700">
                        <span class="w-2 h-2 rounded-full" style="background-color: var(--primary-hex);"></span>
                        <span>{{ $biz->owner_name }}</span>
                    </div>
                @endif

                <!-- Language Toggle -->
                <div class="relative" x-data="{ showLang: false }">
                    <button @click="showLang = !showLang"
                        class="inline-flex items-center px-3 py-1.5 border border-slate-200 text-xs font-bold rounded-lg text-slate-700 bg-slate-50 hover:bg-slate-100 transition-colors cursor-pointer">
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
        <main class="flex-grow px-4 sm:px-6 lg:px-8 pb-6 pt-4 overflow-y-auto">
            <!-- Toast / Flash Alerts -->
            @if (session('success') || session('error') || session('warning') || session('info'))
                <div class="mb-4" x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" x-transition>
                    @if (session('success'))
                        <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between text-xs font-bold">
                            <span>✅ {{ session('success') }}</span>
                            <button @click="show = false" class="text-emerald-500 font-bold ml-2 cursor-pointer">&times;</button>
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 flex items-center justify-between text-xs font-bold">
                            <span>❌ {{ session('error') }}</span>
                            <button @click="show = false" class="text-rose-500 font-bold ml-2 cursor-pointer">&times;</button>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Subscription Expired Banner Alert -->
            @if($biz && $biz->isRenewalDue() && !Route::is('business.renewal*'))
                <div class="mb-5 p-4 sm:p-5 rounded-2xl bg-gradient-to-r from-amber-500/10 via-rose-500/10 to-transparent border border-amber-300 text-amber-950 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-500/20 text-amber-700 flex items-center justify-center shrink-0">
                            <i class="fas fa-exclamation-triangle text-lg"></i>
                        </div>
                        <div>
                            <h4 class="font-extrabold text-sm text-slate-900">{{ __('Subscription Expired') }}</h4>
                            <p class="text-xs text-slate-600 mt-0.5">{{ __('Your business listing subscription has expired. Please renew your membership to keep your listing active.') }}</p>
                        </div>
                    </div>
                    <a href="{{ route('business.renewal') }}" class="inline-flex items-center justify-center px-4 py-2 rounded-xl text-xs font-bold text-white shadow-sm transition hover:opacity-90 shrink-0" style="background-color: var(--primary-hex);">
                        <i class="fas fa-rotate mr-2"></i> {{ __('Renew Now') }} &rarr;
                    </a>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>
