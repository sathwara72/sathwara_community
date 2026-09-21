<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    class="{{ app()->getLocale() == 'gu' ? 'font-gujarati' : 'font-sans' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('messages.account_status') }} - {{ App\Models\Setting::get('website_name', 'Shree Satwara Gnati Mandal, Ahmedabad') }}
    </title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Noto+Sans+Gujarati:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .font-gujarati {
            font-family: 'Noto Sans Gujarati', sans-serif !important;
        }

        .font-sans {
            font-family: 'Plus Jakarta Sans', sans-serif !important;
        }
    </style>
</head>

<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex items-center justify-center p-6">
    <div class="max-w-md w-full bg-white rounded-3xl border border-slate-100 p-8 text-center space-y-6 shadow-sm">
        @if(App\Models\Setting::get('website_logo'))
            <img class="w-16 h-16 rounded-2xl object-contain bg-white p-1.5 shadow-md border border-slate-100 mx-auto"
                src="{{ asset('storage/' . App\Models\Setting::get('website_logo')) }}" alt="Logo">
        @else
            <div
                class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-primary-500 to-secondary-500 flex items-center justify-center text-white font-extrabold text-2xl mx-auto shadow-md">
                S
            </div>
        @endif

        <div class="space-y-2">
            <h2 class="text-xl font-black text-slate-900">{{ __('messages.hello') }}, {{ $user->name }}</h2>
            <p class="text-xs text-slate-400 font-semibold">{{ __('messages.registered_label') }}:
                {{ $user->created_at->format('d M, Y') }}</p>
        </div>

        @if($user->account_status === 'close')
            <div
                class="p-5 rounded-2xl bg-rose-50 border border-rose-100 text-rose-800 text-xs font-semibold leading-relaxed space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center mx-auto">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>
                <h3 class="font-extrabold text-sm">Account Closed</h3>
                <p>તમારું મેમ્બર એકાઉન્ટ હાલમાં બંધ (Closed) કરેલ છે. વધુ માહિતી માટે એડમિનિસ્ટ્રેટરનો સંપર્ક કરો.</p>
            </div>
        @elseif($user->status === 'pending')
            <div
                class="p-5 rounded-2xl bg-amber-50 border border-amber-100 text-amber-800 text-xs font-semibold leading-relaxed space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-600 flex items-center justify-center mx-auto">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h3 class="font-extrabold text-sm">{{ __('messages.app_pending_approval') }}</h3>
                <p>{{ __('messages.app_pending_desc') }}</p>
            </div>
        @elseif($user->status === 'rejected')
            <div
                class="p-5 rounded-2xl bg-rose-50 border border-rose-100 text-rose-800 text-xs font-semibold leading-relaxed space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center mx-auto">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </div>
                <h3 class="font-extrabold text-sm">{{ __('messages.app_rejected') }}</h3>
                <p class="text-left bg-white/50 p-3 rounded-lg border border-rose-200/50 mt-2 font-bold text-rose-700">
                    {{ __('messages.reason') }}: {{ $user->rejection_reason ?? 'Criteria criteria not met.' }}
                </p>
                <p class="text-[10px] text-slate-500 pt-2">{{ __('messages.app_rejected_desc') }}</p>
            </div>
        @endif

        <div class="pt-4 border-t border-slate-100 flex justify-between items-center">
            <a href="{{ route('home') }}" class="text-xs font-bold text-slate-500 hover:text-slate-900">&larr;
                {{ __('messages.back_to_website') }}</a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                    class="text-xs font-bold text-rose-600 hover:text-rose-700">{{ __('messages.sign_out') }}
                    &rarr;</button>
            </form>
        </div>
    </div>
    @include('components.purchase_receipt_modal')
</body>

</html>