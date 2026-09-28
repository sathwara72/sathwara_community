@extends('layouts.public')

@section('title', __('Create New Business Password') . ' — ' . App\Models\Setting::get('website_name', 'Shree Satwara Gnati Mandal, Ahmedabad'))

@section('content')
<section class="py-4 md:py-6 bg-slate-50/70 flex-1 flex items-center justify-center">
    <div class="max-w-sm w-full px-4 mx-auto">
        
        <div class="bg-white p-5 md:p-6 rounded-2xl border border-slate-200/80 shadow-lg relative overflow-hidden space-y-3">
            
            <!-- Ambient Accent Glow -->
            <div class="absolute -top-10 -right-10 w-32 h-32 bg-amber-500/10 blur-2xl rounded-full pointer-events-none"></div>

            <div class="text-center space-y-1">
                <div class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-amber-50 border border-amber-100 text-amber-600 shadow-2xs mx-auto">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>

                <div class="space-y-0.5">
                    <h2 class="text-base sm:text-lg font-extrabold text-slate-900 tracking-tight">
                        {{ __('Create New Business Password') }}
                    </h2>
                    <p class="text-[11px] font-medium text-slate-500 max-w-xs mx-auto">
                        {{ __('Enter your new password and confirm it below.') }}
                    </p>
                </div>
            </div>

            <!-- Session Status -->
            @if (session('status'))
                <div class="p-2.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-center text-xs font-semibold">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('business.password.store') }}" 
                  class="space-y-2.5 pt-0.5"
                  x-data="{ submitting: false, showPass: false, showConfirm: false }"
                  @submit="submitting = true">
                @csrf

                <!-- Password Field -->
                <div class="space-y-1">
                    <label for="password" class="text-[10px] font-bold text-slate-700 uppercase tracking-wider">
                        {{ __('New Password') }} <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <input id="password" :type="showPass ? 'text' : 'password'" name="password" required autocomplete="new-password"
                            placeholder="••••••••"
                            class="w-full text-xs font-semibold pl-9 pr-9 py-2 bg-slate-50/70 border border-slate-200 rounded-lg focus:bg-white focus:border-primary-500 focus:ring-2 focus:ring-primary-500/15 text-slate-800 transition-all outline-hidden {{ $errors->has('password') ? 'border-rose-400 ring-2 ring-rose-500/10' : '' }}">
                        <button type="button" @click="showPass = !showPass" 
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 focus:outline-hidden cursor-pointer"
                            title="{{ __('messages.toggle_password_visibility') }}">
                            <svg x-show="!showPass" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg x-show="showPass" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858-5.908a10.018 10.018 0 013.682-.863c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21M3 3l18 18"/>
                            </svg>
                        </button>
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-0.5 text-[11px]" />
                </div>

                <!-- Confirm Password Field -->
                <div class="space-y-1">
                    <label for="password_confirmation" class="text-[10px] font-bold text-slate-700 uppercase tracking-wider">
                        {{ __('Confirm New Password') }} <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <input id="password_confirmation" :type="showConfirm ? 'text' : 'password'" name="password_confirmation" required autocomplete="new-password"
                            placeholder="••••••••"
                            class="w-full text-xs font-semibold pl-9 pr-9 py-2 bg-slate-50/70 border border-slate-200 rounded-lg focus:bg-white focus:border-primary-500 focus:ring-2 focus:ring-primary-500/15 text-slate-800 transition-all outline-hidden">
                        <button type="button" @click="showConfirm = !showConfirm" 
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 focus:outline-hidden cursor-pointer"
                            title="{{ __('messages.toggle_password_visibility') }}">
                            <svg x-show="!showConfirm" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg x-show="showConfirm" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858-5.908a10.018 10.018 0 013.682-.863c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21M3 3l18 18"/>
                            </svg>
                        </button>
                    </div>
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-0.5 text-[11px]" />
                </div>

                <div class="pt-0.5">
                    <button type="submit" 
                        :disabled="submitting"
                        class="w-full inline-flex items-center justify-center px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-xs transition-all transform active:scale-98 cursor-pointer disabled:opacity-60 gap-2">
                        <span x-show="!submitting" class="flex items-center gap-1.5">
                            <span>{{ __('Reset Password & Log In') }}</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </span>
                        <span x-show="submitting" class="flex items-center gap-2" x-cloak>
                            <svg class="animate-spin w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>{{ __('Resetting Password...') }}</span>
                        </span>
                    </button>
                </div>
            </form>

            <div class="pt-2.5 border-t border-slate-100 text-center">
                <a href="{{ route('business.login') }}" class="text-[11px] font-bold text-slate-600 hover:text-primary-600 transition-colors">
                    &larr; {{ __('Back to Business Login') }}
                </a>
            </div>

        </div>

    </div>
</section>
@endsection
