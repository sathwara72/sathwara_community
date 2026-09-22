@extends('layouts.public')

@section('title', __('Business Portal Login') . ' — ' . App\Models\Setting::get('website_name', 'Shree Satwara Gnati Mandal, Ahmedabad'))

@section('content')
<section class="py-4 md:py-6 bg-slate-50/70 flex-1 flex items-center justify-center">
    <div class="max-w-sm w-full px-4 mx-auto">
        
        <!-- Main Business Login Card -->
        <div class="bg-white p-5 md:p-6 rounded-2xl border border-slate-200/80 shadow-lg relative overflow-hidden space-y-3">
            
            <!-- Ambient Accent Glow -->
            <div class="absolute -top-10 -right-10 w-32 h-32 bg-primary-500/10 blur-2xl rounded-full pointer-events-none"></div>

            <!-- Card Header & Branding -->
            <div class="text-center space-y-1">
                <div class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-primary-50 border border-primary-100 text-primary-600 shadow-2xs mx-auto">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>

                <div class="space-y-0.5">
                    <h2 class="text-base sm:text-lg font-extrabold text-slate-900 tracking-tight">
                        {{ __('Business Portal Login') }}
                    </h2>
                    <p class="text-[11px] font-medium text-slate-500">
                        {{ __('Enter your registered email or phone and password to continue.') }}
                    </p>
                </div>

                <!-- Feature Chips -->
                <div class="flex items-center justify-center gap-1.5 flex-wrap pt-0.5">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-slate-50 border border-slate-200/80 text-[10px] font-bold text-slate-600">
                        🏪 {{ __('Public Business Directory') }}
                    </span>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-slate-50 border border-slate-200/80 text-[10px] font-bold text-slate-600">
                        🔄 {{ __('Instant Renewals') }}
                    </span>
                </div>
            </div>

            <!-- Session Status Alert -->
            @if(session('success'))
                <div class="p-2.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center gap-2 text-xs font-semibold">
                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="p-2.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 flex items-start gap-2 text-xs font-semibold">
                    <svg class="w-4 h-4 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div class="space-y-0.5">
                        @foreach($errors->all() as $err)
                            <div>{{ $err }}</div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Login Form -->
            <form method="POST" action="{{ route('business.login.submit') }}" 
                  class="space-y-2.5 pt-0.5" 
                  x-data="{ submitting: false, showPassword: false }"
                  @submit="submitting = true">
                @csrf

                <!-- Email or Phone Field -->
                <div class="space-y-1">
                    <label for="login" class="text-[10px] font-bold text-slate-700 uppercase tracking-wider">
                        {{ __('Email or Phone Number') }} <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                            </svg>
                        </div>
                        <input id="login" type="text" name="login" value="{{ old('login') }}" 
                               required autofocus autocomplete="username"
                               placeholder="{{ __('Enter your email or phone number') }}"
                               class="w-full text-xs font-semibold pl-9 pr-3 py-2 bg-slate-50/70 border border-slate-200 rounded-lg focus:bg-white focus:border-primary-500 focus:ring-2 focus:ring-primary-500/15 text-slate-800 transition-all outline-hidden {{ $errors->has('login') ? 'border-rose-400 ring-2 ring-rose-500/10' : '' }}">
                    </div>
                    <x-input-error :messages="$errors->get('login')" class="mt-0.5 text-[11px]" />
                </div>

                <!-- Password Field with Visibility Toggle -->
                <div class="space-y-1">
                    <label for="password" class="text-[10px] font-bold text-slate-700 uppercase tracking-wider">
                        {{ __('Password') }} <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <input id="password" :type="showPassword ? 'text' : 'password'" name="password" 
                               required autocomplete="current-password"
                               placeholder="{{ __('Enter your password') }}"
                               class="w-full text-xs font-semibold pl-9 pr-9 py-2 bg-slate-50/70 border border-slate-200 rounded-lg focus:bg-white focus:border-primary-500 focus:ring-2 focus:ring-primary-500/15 text-slate-800 transition-all outline-hidden {{ $errors->has('password') ? 'border-rose-400 ring-2 ring-rose-500/10' : '' }}">
                        <button type="button" @click="showPassword = !showPassword" 
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 focus:outline-hidden cursor-pointer"
                                title="{{ __('messages.toggle_password_visibility') }}">
                            <svg x-show="!showPassword" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg x-show="showPassword" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858-5.908a10.018 10.018 0 013.682-.863c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21M3 3l18 18"/>
                            </svg>
                        </button>
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-0.5 text-[11px]" />
                </div>

                <!-- Remember Me & Forgot Password -->
                <div class="flex items-center justify-between pt-0.5">
                    <label for="remember" class="inline-flex items-center gap-1.5 cursor-pointer select-none">
                        <input id="remember" type="checkbox" name="remember" value="1"
                               {{ old('remember') ? 'checked' : '' }}
                               class="w-3.5 h-3.5 rounded border-slate-300 text-primary-600 shadow-2xs focus:ring-primary-500 cursor-pointer">
                        <span class="text-[11px] font-semibold text-slate-600">{{ __('Remember me') }}</span>
                    </label>

                    @if (Route::has('business.password.request'))
                        <a href="{{ route('business.password.request') }}" 
                            class="text-[11px] font-bold text-primary-600 hover:text-primary-700 hover:underline transition-colors">
                            {{ __('Forgot password?') }}
                        </a>
                    @endif
                </div>

                <!-- Submit Button -->
                <div class="pt-0.5">
                    <button type="submit" 
                            :disabled="submitting"
                            class="w-full inline-flex items-center justify-center px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-xs transition-all transform active:scale-98 cursor-pointer disabled:opacity-60 gap-2">
                        <span x-show="!submitting" class="flex items-center gap-2">
                            <span>{{ __('Sign In to Business Panel') }}</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </span>
                        <span x-show="submitting" class="flex items-center gap-2" x-cloak>
                            <svg class="animate-spin w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>{{ __('Signing In...') }}</span>
                        </span>
                    </button>
                </div>
            </form>

            <!-- Registration & Directory Footer Links -->
            <div class="pt-2.5 border-t border-slate-100 text-center space-y-1">
                <p class="text-[11px] text-slate-500 font-medium">
                    {{ __("Don't have a business listing yet?") }}
                    <a href="{{ route('register.business') }}" class="font-bold text-primary-600 hover:text-primary-700 hover:underline ml-0.5">
                        {{ __('Register your Business') }} &rarr;
                    </a>
                </p>
                <div>
                    <a href="{{ route('business.directory') }}" class="inline-flex items-center gap-1 text-[11px] font-semibold text-slate-400 hover:text-slate-600 transition-colors">
                        &larr; {{ __('Back to Business Directory') }}
                    </a>
                </div>
            </div>

        </div>

    </div>
</section>
@endsection
