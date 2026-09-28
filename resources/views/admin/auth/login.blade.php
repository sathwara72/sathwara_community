<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Admin Portal Sign In - {{ config('app.name', 'Shree Satwara Gnati Mandal, Ahmedabad') }}</title>

    @if(App\Models\Setting::get('website_favicon'))
        <link rel="icon" type="image/x-icon" href="{{ asset('storage/' . App\Models\Setting::get('website_favicon')) }}">
    @endif

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
    </style>
</head>

<body
    class="h-full bg-slate-50 text-slate-900 flex items-center justify-center p-4 selection:bg-primary-500 selection:text-white">
    <div class="w-full max-w-md space-y-6">

        <!-- Header Branding -->
        <div class="text-center space-y-3">
            @if(App\Models\Setting::get('website_logo'))
                <img class="h-16 w-auto mx-auto drop-shadow-xs"
                    src="{{ asset('storage/' . App\Models\Setting::get('website_logo')) }}" alt="Logo">
            @else
                <div
                    class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-primary-600 to-primary-500 flex items-center justify-center text-white font-black text-2xl shadow-md shadow-primary-500/30 mx-auto border border-white">
                    S
                </div>
            @endif

            <div class="space-y-1">
                <div
                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-primary-50 border border-primary-200/80 text-primary-700 text-[10px] font-extrabold uppercase tracking-widest">
                    <span class="w-1.5 h-1.5 rounded-full bg-primary-600 animate-pulse"></span>
                    <span>Admin Portal</span>
                </div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Administrator Sign In</h1>
                <p class="text-xs text-slate-500 font-semibold">Authorized management personnel access</p>
            </div>
        </div>

        <!-- Clean Theme Login Card -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-6 sm:p-8 shadow-xl shadow-slate-200/50 space-y-5">
            @if(session('success'))
                <div
                    class="p-3.5 bg-emerald-50 border border-emerald-200/80 rounded-xl text-emerald-800 text-xs font-bold flex items-center gap-2">
                    <span class="text-emerald-600">✓</span> {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div
                    class="p-3.5 bg-rose-50 border border-rose-200/80 rounded-xl text-rose-800 text-xs font-semibold space-y-1">
                    <p class="font-extrabold uppercase tracking-wider text-[10px] text-rose-600">Authentication Failed</p>
                    <ul class="list-disc pl-4 space-y-0.5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login.submit') }}" class="space-y-4">
                @csrf

                <!-- Admin Email / Phone / ID -->
                <div class="space-y-1.5">
                    <label for="login" class="block text-[11px] font-extrabold uppercase tracking-wider text-slate-600">
                        Admin Email / Phone / ID <span class="text-rose-500">*</span>
                    </label>
                    <input id="login" type="text" name="login" value="{{ old('login') }}" required autofocus
                        placeholder="e.g. admin@satwara.org"
                        class="w-full text-xs font-semibold px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 placeholder-slate-400 focus:bg-white focus:border-primary-500 focus:ring-2 focus:ring-primary-100 outline-none transition-all">
                </div>

                <!-- Password -->
                <div class="space-y-1.5" x-data="{ showPass: false }">
                    <label for="password"
                        class="block text-[11px] font-extrabold uppercase tracking-wider text-slate-600">
                        Password <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <input id="password" :type="showPass ? 'text' : 'password'" name="password" required placeholder="••••••••••••"
                            class="w-full text-xs font-semibold pl-3.5 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 placeholder-slate-400 focus:bg-white focus:border-primary-500 focus:ring-2 focus:ring-primary-100 outline-none transition-all">
                        <button type="button" @click="showPass = !showPass" 
                            class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none cursor-pointer"
                            title="Toggle password visibility">
                            <svg x-show="!showPass" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg x-show="showPass" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858-5.908a10.018 10.018 0 013.682-.863c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21M3 3l18 18"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox" name="remember"
                            class="w-4 h-4 rounded border-slate-300 bg-slate-50 text-primary-600 focus:ring-0 focus:ring-offset-0">
                        <span class="text-xs text-slate-600 font-bold">Keep me signed in</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit"
                    class="w-full py-3.5 bg-primary-600 hover:bg-primary-700 text-white font-extrabold text-xs uppercase tracking-wider rounded-xl shadow-md shadow-primary-500/25 transition-all duration-200 active:scale-[0.99] cursor-pointer block text-center">
                    Sign In To Admin Portal
                </button>
            </form>
        </div>

        <!-- Footer Link -->
        <div class="text-center pt-2">
            <a href="{{ route('home') }}"
                class="text-xs font-bold text-slate-500 hover:text-slate-900 transition-colors inline-flex items-center gap-1.5">
                <span>← Return to Public Website</span>
            </a>
        </div>
    </div>
</body>

</html>