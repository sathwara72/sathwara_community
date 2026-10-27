// Payment log: records every opening of the Razorpay popup and how it ended (cancelled / failed / paid),
// so the office can trace a payment that was taken but never verified. Logging never blocks a payment.
//
// Use PaymentLog.razorpay(options, { purpose: 'event_pass' }) in place of new Razorpay(options).
(function () {
    const url = document.currentScript && document.currentScript.dataset.url;

    function newKey() {
        if (window.crypto && crypto.randomUUID) {
            return crypto.randomUUID();
        }
        return 'pa-' + Date.now().toString(36) + '-' + Math.random().toString(36).slice(2, 12);
    }

    function send(body) {
        if (!url) {
            return Promise.resolve();
        }
        try {
            return fetch(url, {
                method: 'POST',
                keepalive: true,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(body),
            }).catch(function () {});
        } catch (e) {
            return Promise.resolve();
        }
    }

    window.PaymentLog = {
        razorpay: function (options, meta) {
            const prefill = options.prefill || {};
            // Every call carries the whole attempt, so the server is right whatever order they arrive in
            const base = {
                key: newKey(),
                purpose: (meta && meta.purpose) || null,
                description: options.description || null,
                amount: (Number(options.amount) || 0) / 100,
                name: prefill.name || null,
                contact: prefill.contact || null,
                email: prefill.email || null,
                order_id: options.order_id || null,
            };
            const log = function (status, extra) {
                return send(Object.assign({}, base, extra || {}, { status: status }));
            };

            const handler = options.handler;
            const modal = Object.assign({}, options.modal);
            const ondismiss = modal.ondismiss;
            let paid = false;

            const wrapped = Object.assign({}, options, {
                handler: function (response) {
                    paid = true;
                    // Wait briefly so the payment id is logged before the page moves on
                    const logged = log('paid', {
                        payment_id: response.razorpay_payment_id || null,
                        order_id: response.razorpay_order_id || base.order_id,
                    });
                    const timeout = new Promise(function (resolve) { setTimeout(resolve, 2500); });
                    Promise.race([logged, timeout]).then(function () {
                        if (handler) handler(response);
                    });
                },
                modal: Object.assign(modal, {
                    ondismiss: function () {
                        if (!paid) log('cancelled');
                        if (ondismiss) ondismiss.apply(this, arguments);
                    },
                }),
            });

            const rzp = new Razorpay(wrapped);

            // Log failures, then pass them on to the page's own payment.failed handler
            const on = rzp.on.bind(rzp);
            let pageFailedHandler = null;
            on('payment.failed', function (r) {
                const err = (r && r.error) || {};
                log('failed', {
                    payment_id: (err.metadata && err.metadata.payment_id) || null,
                    order_id: (err.metadata && err.metadata.order_id) || base.order_id,
                    error: [err.description, err.reason].filter(Boolean).join(' | ') || null,
                });
                if (pageFailedHandler) pageFailedHandler(r);
            });
            rzp.on = function (event, fn) {
                if (event === 'payment.failed') {
                    pageFailedHandler = fn;
                    return rzp;
                }
                return on(event, fn);
            };

            const open = rzp.open.bind(rzp);
            rzp.open = function () {
                log('opened');
                return open();
            };

            return rzp;
        },
    };
})();
