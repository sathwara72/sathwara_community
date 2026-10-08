@extends('layouts.public')

@section('content')
<section class="relative overflow-hidden py-4 md:py-8 bg-slate-50/70 flex-1 flex items-center justify-center">
    @include('partials.auth_background', ['variant' => 'member'])
    <div class="relative z-10 max-w-sm w-full px-4">
        
        <!-- Compact Main Login Card -->
        <div class="bg-white p-5 md:p-6 rounded-2xl border border-slate-200/80 shadow-lg relative overflow-hidden space-y-4">
            
            <!-- Ambient Accent Glow -->
            <div class="absolute -top-10 -right-10 w-32 h-32 bg-primary-500/10 blur-2xl rounded-full pointer-events-none"></div>

            <!-- Member / Business Login Switch -->
            @include('partials.auth_type_tabs', ['active' => 'member', 'mode' => 'login'])

            <!-- Card Header & Branding -->
            <div class="text-center space-y-2">
                <div class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-primary-50 border border-primary-100 text-primary-600 shadow-2xs mx-auto">
                    @if(App\Models\Setting::get('website_logo'))
                        <img src="{{ asset('storage/' . App\Models\Setting::get('website_logo')) }}" alt="Logo" class="w-6 h-6 object-contain">
                    @else
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    @endif
                </div>

                <div class="space-y-0.5">
                    <h2 class="text-lg font-extrabold text-slate-900 tracking-tight">
                        {{ __('messages.member_account_login') }}
                    </h2>
                    <p class="text-[11px] font-medium text-slate-500">
                        {{ __('messages.login_subtitle') }}
                    </p>
                </div>
            </div>

            <!-- Session Status Alert -->
            <x-auth-session-status class="mb-1 text-xs" :status="session('status')" />

            <!-- Login Form -->
            <form method="POST" action="{{ route('login') }}" class="space-y-3 pt-1">
                @csrf

                <!-- Email Field -->
                <div class="space-y-1">
                    <label for="email" class="text-[10px] font-bold text-slate-700 uppercase tracking-wider">
                        {{ __('messages.member_email_id') }} <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                            </svg>
                        </div>
                        <input id="email" type="email" name="email" value="{{ old('email', request()->cookie('remember_email')) }}" required autofocus autocomplete="username"
                            placeholder="{{ __('messages.email_placeholder') }}"
                            class="w-full text-xs font-semibold pl-9 pr-3 py-2 bg-slate-50/70 border border-slate-200 rounded-lg focus:bg-white focus:border-primary-500 focus:ring-2 focus:ring-primary-500/15 text-slate-800 transition-all outline-hidden">
                    </div>
                    <x-input-error :messages="$errors->get('email')" class="mt-0.5 text-[11px]" />
                </div>

                <!-- Password Field -->
                <div class="space-y-1" x-data="{ showPassword: false }">
                    <label for="password" class="text-[10px] font-bold text-slate-700 uppercase tracking-wider">
                        {{ __('messages.password') }} <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <input id="password" :type="showPassword ? 'text' : 'password'" name="password" 
                            value="{{ old('password', request()->cookie('remember_password')) }}"
                            required autocomplete="current-password"
                            placeholder="••••••••••••"
                            class="w-full text-xs font-semibold pl-9 pr-9 py-2 bg-slate-50/70 border border-slate-200 rounded-lg focus:bg-white focus:border-primary-500 focus:ring-2 focus:ring-primary-500/15 text-slate-800 transition-all outline-hidden">
                        <button type="button" @click="showPassword = !showPassword" 
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none cursor-pointer"
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
                    <label for="remember_me" class="inline-flex items-center gap-1.5 cursor-pointer select-none">
                        <input id="remember_me" type="checkbox" name="remember" value="1"
                            {{ old('remember', request()->cookie('remember_me')) ? 'checked' : '' }}
                            class="w-3.5 h-3.5 rounded border-slate-300 text-primary-600 shadow-2xs focus:ring-primary-500 cursor-pointer">
                        <span class="text-[11px] font-semibold text-slate-600">{{ __('messages.remember_me') }}</span>
                    </label>

                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" 
                            class="text-[11px] font-bold text-primary-600 hover:text-primary-700 hover:underline transition-colors">
                            {{ __('messages.forgot_password') }}
                        </a>
                    @endif
                </div>

                <!-- Log In Button -->
                <div class="pt-1">
                    <button type="submit" 
                        class="w-full inline-flex items-center justify-center px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-xs transition-all transform active:scale-98 cursor-pointer gap-2">
                        <span>{{ __('messages.log_in_to_portal') }}</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </button>
                </div>
            </form>

            <!-- Registration Footer Prompt -->
            <div class="pt-3 border-t border-slate-100 text-center">
                <p class="text-[11px] text-slate-500 font-medium">
                    {{ __('messages.not_registered_yet') }}
                    <a href="{{ route('register.member') }}" class="font-bold text-primary-600 hover:text-primary-700 hover:underline ml-0.5">
                        {{ __('messages.register_now_link') }} &rarr;
                    </a>
                </p>
            </div>

        </div>

    </div>
</section>
@endsection



