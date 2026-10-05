@extends('layouts.member')
@section('page_title', __('messages.account_settings'))

@section('content')
<div class="max-w-4xl space-y-6">

    @if($errors->any())
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 6000)" x-show="show" x-transition
             class="p-4 bg-rose-50 border border-rose-100 text-rose-800 rounded-xl shadow-sm flex items-start justify-between">
            <div>
                <p class="text-xs font-bold mb-2">{{ __('messages.please_correct_errors') }}</p>
                <ul class="list-disc pl-4 text-[11px] font-medium space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            <button @click="show = false" class="text-rose-500 font-bold ml-2">&times;</button>
        </div>
    @endif

    <!-- CARD 1: EMAIL ADDRESS (changed by the office only) -->
    <div class="bg-white border border-slate-100 rounded-2xl p-5 sm:p-6 shadow-sm space-y-4">
        <div class="border-b border-slate-100 pb-3">
            <h3 class="text-sm sm:text-base font-black text-slate-900 flex items-center gap-2">
                <svg class="w-4 h-4 text-primary-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg> {{ __('messages.email_address') }}
            </h3>
            <p class="text-xs text-slate-500 font-medium mt-0.5">{{ __('messages.email_change_by_office') }}</p>
        </div>

        <!-- Current Email Display -->
        <div class="p-4 bg-slate-50 border border-slate-100 rounded-xl flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <span class="text-[10px] font-extrabold uppercase text-slate-400 tracking-wider">{{ __('messages.current_email_address') }}</span>
                <p class="text-xs font-bold text-slate-800 mt-0.5">{{ $user->email }}</p>
            </div>
            <div>
                <span class="text-[10px] font-black text-slate-600 bg-white border border-slate-200 px-2 py-0.5 rounded-md uppercase tracking-wider shadow-sm">{{ __('messages.login_email') }}</span>
            </div>
        </div>

    </div>

    <!-- CARD 2: PASSWORD UPDATE -->
    <div class="bg-white border border-slate-100 rounded-2xl p-5 sm:p-6 shadow-sm space-y-4">
        <div class="border-b border-slate-100 pb-3">
            <h3 class="text-sm sm:text-base font-black text-slate-900 flex items-center gap-2">
                <svg class="w-4 h-4 text-primary-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg> {{ __('messages.update_account_password') }}
            </h3>
            <p class="text-xs text-slate-500 font-medium mt-0.5">{{ __('messages.update_password_desc') }}</p>
        </div>

        <form method="POST" action="{{ route('member.account.settings.update_password') }}" class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="space-y-1.5" x-data="{ showCurrentPass: false }">
                    <label class="text-xs font-bold text-slate-700 block">{{ __('messages.current_password') }} <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <input :type="showCurrentPass ? 'text' : 'password'" name="current_password" required placeholder="••••••••"
                               class="w-full text-xs font-semibold pl-3 pr-9 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-primary-500 focus:outline-none h-10">
                        <button type="button" @click="showCurrentPass = !showCurrentPass" 
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none cursor-pointer"
                                title="{{ __('messages.toggle_password_visibility') }}">
                            <svg x-show="!showCurrentPass" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg x-show="showCurrentPass" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858-5.908a10.018 10.018 0 013.682-.863c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21M3 3l18 18"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="space-y-1.5" x-data="{ showNewPass: false }">
                    <label class="text-xs font-bold text-slate-700 block">{{ __('messages.new_password') }} <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <input :type="showNewPass ? 'text' : 'password'" name="password" required placeholder="••••••••"
                               class="w-full text-xs font-semibold pl-3 pr-9 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-primary-500 focus:outline-none h-10">
                        <button type="button" @click="showNewPass = !showNewPass" 
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none cursor-pointer"
                                title="{{ __('messages.toggle_password_visibility') }}">
                            <svg x-show="!showNewPass" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg x-show="showNewPass" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858-5.908a10.018 10.018 0 013.682-.863c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21M3 3l18 18"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="space-y-1.5" x-data="{ showConfirmPass: false }">
                    <label class="text-xs font-bold text-slate-700 block">{{ __('messages.confirm_new_password') }} <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <input :type="showConfirmPass ? 'text' : 'password'" name="password_confirmation" required placeholder="••••••••"
                               class="w-full text-xs font-semibold pl-3 pr-9 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-primary-500 focus:outline-none h-10">
                        <button type="button" @click="showConfirmPass = !showConfirmPass" 
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none cursor-pointer"
                                title="{{ __('messages.toggle_password_visibility') }}">
                            <svg x-show="!showConfirmPass" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg x-show="showConfirmPass" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858-5.908a10.018 10.018 0 013.682-.863c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21M3 3l18 18"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 flex justify-end">
                <button type="submit" 
                        class="px-6 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl shadow-sm transition-colors uppercase tracking-wider cursor-pointer">
                    {{ __('messages.update_password_btn') }}
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
