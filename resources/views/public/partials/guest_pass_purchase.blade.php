@php
    $guestFee = (float) ($event->pass_fee ?? 0);
    $guestPassesClosed = !empty($event->registration_end_date) && now()->toDateString() > $event->registration_end_date->toDateString();
    $guestRemaining = \App\Services\GuestPassService::remainingPasses($event);
    $guestMaxPersons = $guestRemaining === null
        ? (int) config('guest_pass.max_persons_per_purchase')
        : min((int) config('guest_pass.max_persons_per_purchase'), $guestRemaining);
@endphp

<div class="space-y-3.5"
     x-data="guestPassPurchase({
        fee: {{ $guestFee }},
        maxPersons: {{ max(1, $guestMaxPersons) }},
        urls: {
            sendOtp: @js(route('events.guest_pass.send_otp', $event->id)),
            verifyOtp: @js(route('events.guest_pass.verify_otp', $event->id)),
            order: @js(route('events.guest_pass.order', $event->id)),
            complete: @js(route('events.guest_pass.complete', $event->id)),
        },
        csrf: @js(csrf_token()),
        appName: @js(config('app.name')),
        cancelledMessage: @js(__('messages.guest_payment_cancelled')),
        processing: @js(__('messages.guest_processing')),
     })">

    @if($guestFee > 0)
        <div class="bg-primary-50 border border-primary-200 rounded-2xl p-3.5 flex items-center justify-between">
            <span class="text-sm font-bold text-primary-900">{{ __('messages.pass_fee_per_person') }}:</span>
            <span class="text-base font-black text-primary-700">₹{{ number_format($guestFee) }}</span>
        </div>
    @else
        <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-3.5 flex items-center justify-between">
            <span class="text-sm font-bold text-emerald-900">{{ __('messages.registration_fee') }}:</span>
            <span class="text-xs font-black text-emerald-700 uppercase bg-emerald-100 px-3 py-1 rounded-lg">{{ __('messages.free_entry') }}</span>
        </div>
    @endif

    @if($guestPassesClosed)
        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 text-slate-600 text-sm font-bold text-center">
            Pass purchase for this event closed on {{ $event->registration_end_date->format('d-M-Y') }}.
        </div>
    @elseif($guestRemaining === 0)
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-600 text-sm font-bold text-center">
            All passes for this event have been sold out.
        </div>
    @else
        <button type="button" @click="open = true"
                class="w-full flex items-center justify-center px-5 py-3.5 bg-primary-600 hover:bg-primary-500 active:scale-95 text-white text-sm font-extrabold rounded-xl shadow-md shadow-primary-500/20 transition-all text-center cursor-pointer">
            {{ __('messages.guest_buy_pass') }}
        </button>
    @endif

    <a href="{{ route('login') }}" class="block text-center text-xs font-bold text-slate-500 hover:text-primary-700 transition-colors">
        {{ __('messages.guest_or_login') }}
    </a>

    <template x-teleport="body">
        <div x-show="open" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
             x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div class="bg-white rounded-3xl border border-slate-100 shadow-2xl max-w-md w-full max-h-[92vh] overflow-y-auto relative">

                <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between sticky top-0 z-10">
                    <div>
                        <h3 class="text-sm font-extrabold">{{ __('messages.purchase_event_pass') }}</h3>
                        <p class="text-[11px] text-slate-400 font-medium mt-0.5 truncate max-w-[280px]">{{ $event->title }}</p>
                    </div>
                    <button type="button" @click="open = false" :disabled="loading"
                            class="w-7 h-7 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-colors cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="p-6 space-y-4">
                    <div x-show="error" x-cloak x-text="error"
                         class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-bold"></div>
                    <div x-show="info && !error" x-cloak x-text="info"
                         class="p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-bold"></div>

                    {{-- Step 1: email + mobile --}}
                    <form x-show="step === 'details'" @submit.prevent="sendOtp()" class="space-y-4">
                        <div class="space-y-1">
                            <label class="text-xs font-extrabold text-slate-800">{{ __('messages.guest_email') }} <span class="text-rose-500">*</span></label>
                            <input type="email" x-model="email" required autocomplete="email" inputmode="email"
                                   class="w-full text-sm font-semibold px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-primary-500 focus:outline-none">
                            <p class="text-[11px] text-slate-500 font-medium">{{ __('messages.guest_email_hint') }}</p>
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-extrabold text-slate-800">{{ __('messages.guest_mobile') }} <span class="text-rose-500">*</span></label>
                            <input type="tel" x-model="mobile" required autocomplete="tel" inputmode="numeric" maxlength="15" placeholder="9876543210"
                                   class="w-full text-sm font-semibold px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-primary-500 focus:outline-none">
                        </div>
                        <button type="submit" :disabled="loading"
                                class="w-full py-3.5 px-4 bg-primary-600 hover:bg-primary-700 disabled:opacity-60 active:scale-95 text-white font-black text-xs rounded-2xl shadow-md transition-all cursor-pointer uppercase tracking-wider">
                            <span x-text="loading ? processing : @js(__('messages.guest_send_otp'))"></span>
                        </button>
                    </form>

                    {{-- Step 2: OTP --}}
                    <form x-show="step === 'otp'" x-cloak @submit.prevent="verifyOtp()" class="space-y-4">
                        <div class="text-xs text-slate-600 font-medium">
                            <span x-text="email" class="font-black text-slate-900"></span>
                            <button type="button" @click="reset()" class="ml-2 text-primary-700 font-bold hover:underline cursor-pointer">{{ __('messages.guest_change_email') }}</button>
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-extrabold text-slate-800">{{ __('messages.guest_enter_otp') }}</label>
                            <input type="text" x-model="otp" required maxlength="6" inputmode="numeric" autocomplete="one-time-code" pattern="\d{6}"
                                   class="w-full text-center text-2xl tracking-[0.5em] font-black px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-primary-500 focus:outline-none">
                        </div>
                        <button type="submit" :disabled="loading || otp.length !== 6"
                                class="w-full py-3.5 px-4 bg-primary-600 hover:bg-primary-700 disabled:opacity-60 active:scale-95 text-white font-black text-xs rounded-2xl shadow-md transition-all cursor-pointer uppercase tracking-wider">
                            <span x-text="loading ? processing : @js(__('messages.guest_verify_otp'))"></span>
                        </button>
                        <button type="button" @click="sendOtp()" :disabled="loading || resendIn > 0"
                                class="w-full text-xs font-bold text-slate-500 hover:text-primary-700 disabled:opacity-60 cursor-pointer">
                            <span x-text="resendIn > 0 ? @js(__('messages.guest_resend_otp')) + ' (' + resendIn + 's)' : @js(__('messages.guest_resend_otp'))"></span>
                        </button>
                    </form>

                    {{-- Step 3: choose number of passes and pay --}}
                    <form x-show="step === 'purchase'" x-cloak @submit.prevent="pay()" class="space-y-4">
                        <div class="flex items-center justify-between p-3 bg-emerald-50 border border-emerald-200 rounded-2xl text-xs">
                            <span class="font-bold text-emerald-800">✓ {{ __('messages.guest_email_verified') }}: <span x-text="email" class="font-black"></span></span>
                        </div>

                        <div class="space-y-1">
                            <label class="text-xs font-extrabold text-slate-800">{{ __('messages.guest_name_optional') }}</label>
                            <input type="text" x-model="name" maxlength="100" autocomplete="name"
                                   class="w-full text-sm font-semibold px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-primary-500 focus:outline-none">
                        </div>

                        <div class="space-y-2">
                            <label class="text-xs font-extrabold text-slate-800">{{ __('messages.ketla_person_attending') }}</label>
                            <div class="flex items-center justify-between bg-slate-50 p-2 rounded-2xl border border-slate-200">
                                <button type="button" @click="if (count > 1) count--" :disabled="count <= 1"
                                        class="w-12 h-12 rounded-xl bg-white hover:bg-slate-100 active:scale-95 disabled:opacity-40 border border-slate-200 text-slate-800 font-black text-2xl flex items-center justify-center cursor-pointer">&minus;</button>
                                <div class="flex items-center gap-2 px-3">
                                    <span class="text-2xl font-black text-primary-600" x-text="count"></span>
                                    <span class="text-xs font-extrabold text-slate-700 uppercase tracking-wider" x-text="count > 1 ? @js(__('messages.persons')) : @js(__('messages.person'))"></span>
                                </div>
                                <button type="button" @click="if (count < maxPersons) count++" :disabled="count >= maxPersons"
                                        class="w-12 h-12 rounded-xl bg-white hover:bg-slate-100 active:scale-95 disabled:opacity-40 border border-slate-200 text-slate-800 font-black text-2xl flex items-center justify-center cursor-pointer">&plus;</button>
                            </div>
                        </div>

                        <div x-show="fee > 0" class="p-4 bg-primary-50/80 border border-primary-200/80 rounded-2xl space-y-2">
                            <div class="flex items-center justify-between text-xs font-bold text-slate-600">
                                <span>{{ __('messages.pass_fee') }}:</span>
                                <span>₹<span x-text="fee"></span> &times; <span x-text="count"></span></span>
                            </div>
                            <div class="border-t border-primary-200/60 pt-2 flex items-center justify-between">
                                <span class="text-xs font-extrabold text-primary-950">{{ __('messages.total_amount') }}:</span>
                                <span class="text-lg font-black text-primary-700">₹<span x-text="(count * fee).toLocaleString()"></span></span>
                            </div>
                        </div>

                        <button type="submit" :disabled="loading"
                                class="w-full py-3.5 px-4 bg-primary-600 hover:bg-primary-700 disabled:opacity-60 active:scale-95 text-white font-black text-xs rounded-2xl shadow-md transition-all cursor-pointer uppercase tracking-wider">
                            <span x-show="loading" x-text="processing"></span>
                            <span x-show="!loading && fee > 0">{{ __('messages.guest_pay_and_get_pass') }} (₹<span x-text="(count * fee).toLocaleString()"></span>)</span>
                            <span x-show="!loading && fee <= 0">{{ __('messages.guest_get_free_pass') }}</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </template>
</div>

<script>
window.guestPassPurchase = function (cfg) {
    return {
        fee: cfg.fee,
        maxPersons: cfg.maxPersons,
        processing: cfg.processing,
        open: false,
        step: 'details',
        email: '',
        mobile: '',
        name: '',
        otp: '',
        count: 1,
        loading: false,
        error: '',
        info: '',
        resendIn: 0,
        timer: null,

        reset() {
            this.step = 'details';
            this.otp = '';
            this.error = '';
            this.info = '';
        },

        async post(url, body) {
            let res, data = {};
            try {
                res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': cfg.csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify(body || {}),
                });
                data = await res.json();
            } catch (e) {
                if (res && res.status === 419) {
                    throw new Error('Your session expired. Please refresh the page and try again.');
                }
                if (res && res.status === 429) {
                    throw new Error('Too many attempts. Please wait a few minutes and try again.');
                }
                throw new Error('Something went wrong. Please check your connection and try again.');
            }
            if (!res.ok || data.success === false) {
                throw new Error(data.message || 'Something went wrong. Please try again.');
            }
            return data;
        },

        async run(fn) {
            this.error = '';
            this.info = '';
            this.loading = true;
            try {
                await fn();
            } catch (e) {
                this.error = e.message;
                this.loading = false;
            }
        },

        sendOtp() {
            return this.run(async () => {
                const data = await this.post(cfg.urls.sendOtp, { email: this.email, mobile: this.mobile });
                this.step = 'otp';
                this.otp = '';
                this.info = data.message;
                this.startResendTimer();
                this.loading = false;
            });
        },

        verifyOtp() {
            return this.run(async () => {
                const data = await this.post(cfg.urls.verifyOtp, { otp: this.otp });
                this.email = data.email || this.email;
                this.step = 'purchase';
                this.loading = false;
            });
        },

        pay() {
            return this.run(async () => {
                const body = { person_count: this.count, name: this.name };
                const order = await this.post(cfg.urls.order, body);

                if (order.free) {
                    await this.complete(body);
                    return;
                }

                if (!window.Razorpay) {
                    throw new Error('Payment gateway failed to load. Please refresh and try again.');
                }

                const rzp = new Razorpay({
                    key: order.key,
                    order_id: order.order_id,
                    amount: order.amount,
                    currency: order.currency,
                    name: cfg.appName,
                    description: 'Event Pass Booking (' + this.count + ' Person/s)',
                    prefill: { email: order.email, contact: order.mobile, name: this.name },
                    theme: { color: '#2563EB' },
                    handler: (response) => {
                        this.run(() => this.complete({
                            razorpay_order_id: response.razorpay_order_id,
                            razorpay_payment_id: response.razorpay_payment_id,
                            razorpay_signature: response.razorpay_signature,
                        }));
                    },
                    modal: {
                        ondismiss: () => {
                            this.loading = false;
                            this.error = cfg.cancelledMessage;
                        },
                    },
                });
                rzp.on('payment.failed', (r) => {
                    this.loading = false;
                    this.error = (r.error && r.error.description) || cfg.cancelledMessage;
                });
                rzp.open();
            });
        },

        async complete(body) {
            const data = await this.post(cfg.urls.complete, body);
            window.dispatchEvent(new CustomEvent('show-loader'));
            window.location.href = data.redirect;
        },

        startResendTimer() {
            clearInterval(this.timer);
            this.resendIn = 60;
            this.timer = setInterval(() => {
                this.resendIn = Math.max(0, this.resendIn - 1);
                if (this.resendIn === 0) clearInterval(this.timer);
            }, 1000);
        },
    };
};
</script>
