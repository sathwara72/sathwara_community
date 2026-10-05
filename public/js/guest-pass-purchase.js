// Pass purchase without login (Alpine component used by public/partials/guest_pass_purchase).
window.guestPassPurchase = function (cfg) {
    return {
        fee: cfg.fee,
        maxPersons: cfg.maxPersons,
        processing: cfg.processing,
        open: false,
        step: 'details',
        email: '',
        mobile: '',
        areaId: '',
        name: '',
        count: 1,
        loading: false,
        error: '',
        info: '',

        reset() {
            this.step = 'details';
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

        saveDetails() {
            return this.run(async () => {
                const data = await this.post(cfg.urls.details, { email: this.email, mobile: this.mobile, area_id: this.areaId });
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
    };
};
