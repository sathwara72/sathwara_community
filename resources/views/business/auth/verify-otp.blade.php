@extends('layouts.public')

@section('title', __('Verify Business OTP') . ' — ' . App\Models\Setting::get('website_name', 'Shree Satwara Gnati Mandal, Ahmedabad'))

@section('content')
<section class="py-4 md:py-6 bg-slate-50/70 flex-1 flex items-center justify-center">
    <div class="max-w-sm w-full px-4 mx-auto">
        
        <div class="bg-white p-5 md:p-6 rounded-2xl border border-slate-200/80 shadow-lg relative overflow-hidden space-y-3">
            
            <!-- Ambient Accent Glow -->
            <div class="absolute -top-10 -right-10 w-32 h-32 bg-amber-500/10 blur-2xl rounded-full pointer-events-none"></div>

            <div class="text-center space-y-1">
                <div class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-amber-50 border border-amber-100 text-amber-600 shadow-2xs mx-auto">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>

                <div class="space-y-0.5">
                    <h2 class="text-base sm:text-lg font-extrabold text-slate-900 tracking-tight">
                        {{ __('Verify Business OTP') }}
                    </h2>
                    <p class="text-[11px] font-medium text-slate-500 max-w-xs mx-auto">
                        {{ __('Enter the 6-digit OTP code sent to your business email.') }}
                    </p>
                </div>
            </div>

            <!-- Session Status / Expiry Alerts -->
            @if (session('status'))
                <div class="p-2.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-center text-xs font-semibold">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('business.password.otp.verify.submit') }}" 
                  class="space-y-2.5 pt-0.5"
                  x-data="{ submitting: false }"
                  @submit="submitting = true">
                @csrf

                <!-- OTP Code Field -->
                <div class="space-y-1">
                    <label for="otp" class="text-[10px] font-bold text-slate-700 uppercase tracking-wider">
                        {{ __('messages.six_digit_code') }} <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <input id="otp" type="text" name="otp" required autofocus
                            placeholder="123456" maxlength="6" pattern="[0-9]{6}" inputmode="numeric"
                            class="w-full text-center text-sm font-bold tracking-widest pl-9 pr-3 py-2 bg-slate-50/70 border border-slate-200 rounded-lg focus:bg-white focus:border-primary-500 focus:ring-2 focus:ring-primary-500/15 text-slate-800 transition-all outline-hidden {{ $errors->has('otp') ? 'border-rose-400 ring-2 ring-rose-500/10' : '' }}">
                    </div>
                    <x-input-error :messages="$errors->get('otp')" class="mt-0.5 text-[11px]" />
                </div>

                <div class="pt-0.5">
                    <button type="submit" 
                        :disabled="submitting"
                        class="w-full inline-flex items-center justify-center px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-xs transition-all transform active:scale-98 cursor-pointer disabled:opacity-60 gap-2">
                        <span x-show="!submitting" class="flex items-center gap-1.5">
                            <span>{{ __('Verify Code & Continue') }}</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </span>
                        <span x-show="submitting" class="flex items-center gap-2" x-cloak>
                            <svg class="animate-spin w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>{{ __('Verifying...') }}</span>
                        </span>
                    </button>
                </div>
            </form>

            <div class="pt-2.5 border-t border-slate-100 flex items-center justify-between text-[11px] font-semibold">
                <form method="POST" action="{{ route('business.password.email') }}" class="inline">
                    @csrf
                    <input type="hidden" name="email" value="{{ session('business_reset_email') }}">
                    <button type="submit" class="text-primary-600 hover:text-primary-700 hover:underline cursor-pointer">
                        {{ __('messages.resend_otp') }}
                    </button>
                </form>

                <a href="{{ route('business.password.request') }}" class="text-slate-500 hover:text-slate-700 transition-colors">
                    {{ __('messages.change_email') }}
                </a>
            </div>

        </div>

    </div>
</section>
@endsection
