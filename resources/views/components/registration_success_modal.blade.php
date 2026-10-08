@php
    $registrationSuccess = session()->pull('registration_success');
    $registrationPopupKey = 'registration_popup_shown_' . ($registrationSuccess['id'] ?? md5(json_encode($registrationSuccess)));
@endphp

@if ($registrationSuccess)
<!-- ================= FREE REGISTRATION SUCCESS MODAL ================= -->
<div x-data="{
        showRegSuccess: true,
        init() {
            try {
                if (sessionStorage.getItem(@js($registrationPopupKey))) { this.showRegSuccess = false; }
                else { sessionStorage.setItem(@js($registrationPopupKey), '1'); }
            } catch (e) {}
        }
     }"
     x-cloak
     x-show="showRegSuccess"
     class="fixed inset-0 flex items-center justify-center p-4"
     style="position: fixed !important; inset: 0 !important; z-index: 999999 !important; background-color: rgba(15, 23, 42, 0.75) !important; backdrop-filter: blur(8px) !important; -webkit-backdrop-filter: blur(8px) !important; padding: 16px !important;"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">

    <style>
        @keyframes regCheckScale {
            0% { transform: scale(0.5); opacity: 0; }
            60% { transform: scale(1.12); opacity: 1; }
            100% { transform: scale(1); opacity: 1; }
        }
        @keyframes regCheckDraw {
            0% { stroke-dashoffset: 48; }
            100% { stroke-dashoffset: 0; }
        }
        @keyframes regPulseRing {
            0% { transform: scale(0.92); opacity: 0.7; }
            50% { transform: scale(1.22); opacity: 0; }
            100% { transform: scale(1.22); opacity: 0; }
        }
        .reg-check-circle { animation: regCheckScale 0.45s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards; }
        .reg-check-stroke { stroke-dasharray: 48; stroke-dashoffset: 48; animation: regCheckDraw 0.4s 0.25s cubic-bezier(0.65, 0, 0.45, 1) forwards; }
        .reg-check-ring { animation: regPulseRing 2.2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
    </style>

    <div @click.away="showRegSuccess = false"
         class="rounded-3xl border border-slate-100 shadow-2xl relative flex flex-col text-center"
         style="max-width: 420px !important; width: 100% !important; background-color: #ffffff !important; margin: auto !important; padding: 26px 20px 20px 20px !important;">

        <button type="button"
                @click="showRegSuccess = false"
                class="absolute top-3.5 right-3.5 w-7 h-7 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-400 hover:text-slate-700 flex items-center justify-center transition-colors cursor-pointer text-xs font-bold"
                title="{{ __('messages.popup_close') }}">
            ✕
        </button>

        <div class="flex items-center justify-center pt-1 pb-1">
            <div class="relative flex items-center justify-center">
                <div class="reg-check-ring absolute w-20 h-20 rounded-full bg-emerald-400/25 pointer-events-none"></div>
                <div class="reg-check-circle relative w-16 h-16 rounded-full bg-gradient-to-tr from-emerald-600 to-emerald-400 flex items-center justify-center shadow-lg shadow-emerald-500/30">
                    <svg class="w-8 h-8 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round">
                        <path class="reg-check-stroke" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="mt-4 space-y-1">
            <h3 class="text-lg sm:text-xl font-black text-slate-900 tracking-tight leading-snug">
                {{ __('messages.biz_registration_success_title') }}
            </h3>
            @if (!empty($registrationSuccess['business_name']))
                <p class="text-sm font-bold text-primary-600">{{ $registrationSuccess['business_name'] }}</p>
            @endif
            <p class="text-xs font-medium text-slate-500 leading-relaxed">
                {{ __('messages.biz_registration_pending_approval') }}
            </p>
            <p class="text-xs font-medium text-slate-500 leading-relaxed">
                {{ __('messages.biz_registration_check_status') }}
            </p>
        </div>

        <div class="mt-5 w-full pt-1 flex gap-2">
            <a href="{{ route('business.login') }}"
               class="flex-1 bg-slate-900 hover:bg-slate-800 active:scale-95 text-white font-bold text-xs py-2.5 rounded-xl transition-all text-center">
                {{ __('messages.business_login') }}
            </a>
            <button type="button"
                    @click="showRegSuccess = false"
                    class="flex-1 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-bold text-xs py-2.5 rounded-xl shadow-xs shadow-emerald-600/25 transition-all cursor-pointer">
                {{ __('messages.popup_ok') }}
            </button>
        </div>
    </div>
</div>
@endif
