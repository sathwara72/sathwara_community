@extends('layouts.public')

@section('title', __('Reset Business Password') . ' — ' . App\Models\Setting::get('website_name', 'Shree Satwara Gnati Mandal, Ahmedabad'))

@section('content')
<section class="py-4 md:py-6 bg-slate-50/70 flex-1 flex items-center justify-center">
    <div class="max-w-sm w-full px-4 mx-auto">
        
        <div class="bg-white p-5 md:p-6 rounded-2xl border border-slate-200/80 shadow-lg relative overflow-hidden space-y-3">
            
            <!-- Ambient Accent Glow -->
            <div class="absolute -top-10 -right-10 w-32 h-32 bg-amber-500/10 blur-2xl rounded-full pointer-events-none"></div>

            <div class="text-center space-y-1">
                <div class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-amber-50 border border-amber-100 text-amber-600 shadow-2xs mx-auto">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                </div>

                <div class="space-y-0.5">
                    <h2 class="text-base sm:text-lg font-extrabold text-slate-900 tracking-tight">
                        {{ __('Reset Business Password') }}
                    </h2>
                    <p class="text-[11px] font-medium text-slate-500 max-w-xs mx-auto">
                        {{ __('Enter your registered business email. We\'ll send an OTP code to reset your password.') }}
                    </p>
                </div>
            </div>

            <!-- Session Status Alert -->
            @if (session('status'))
                <div class="p-2.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-center text-xs font-semibold">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('business.password.email') }}" 
                  class="space-y-2.5 pt-0.5"
                  x-data="{ submitting: false }"
                  @submit="submitting = true">
                @csrf

                <!-- Email Field -->
                <div class="space-y-1">
                    <label for="email" class="text-[10px] font-bold text-slate-700 uppercase tracking-wider">
                        {{ __('messages.email_address_label') }} <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                            </svg>
                        </div>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                            placeholder="{{ __('messages.email_placeholder') }}"
                            class="w-full text-xs font-semibold pl-9 pr-3 py-2 bg-slate-50/70 border border-slate-200 rounded-lg focus:bg-white focus:border-primary-500 focus:ring-2 focus:ring-primary-500/15 text-slate-800 transition-all outline-hidden {{ $errors->has('email') ? 'border-rose-400 ring-2 ring-rose-500/10' : '' }}">
                    </div>
                    <x-input-error :messages="$errors->get('email')" class="mt-0.5 text-[11px]" />
                </div>

                <div class="pt-0.5">
                    <button type="submit" 
                        :disabled="submitting"
                        class="w-full inline-flex items-center justify-center px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-xs transition-all transform active:scale-98 cursor-pointer disabled:opacity-60 gap-2">
                        <span x-show="!submitting" class="flex items-center gap-1.5">
                            <span>{{ __('Send Verification OTP') }}</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </span>
                        <span x-show="submitting" class="flex items-center gap-2" x-cloak>
                            <svg class="animate-spin w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>{{ __('Sending OTP...') }}</span>
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
