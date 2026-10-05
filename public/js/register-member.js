// Member registration: client checks, server pre-validation, then Razorpay Checkout.
// Page values come from window.MemberRegisterConfig (set in register_member.blade.php).
document.addEventListener('DOMContentLoaded', function () {
    const cfg = window.MemberRegisterConfig;
    const form = document.getElementById('memberRegisterForm') || document.querySelector('form[action="' + cfg.urls.submit + '"]');
    if (!form) return;

    form.id = 'memberRegisterForm';

    // ── Custom Modal Helper ──────────────────────────────────────
    const otpAlertModal = document.getElementById('otpAlertModal');
    const otpModalPanel = document.getElementById('otpModalPanel');
    const otpModalAccent = document.getElementById('otpModalAccent');
    const otpModalIcon = document.getElementById('otpModalIcon');
    const otpAlertTitle = document.getElementById('otpAlertTitle');
    const otpAlertMessage = document.getElementById('otpAlertMessage');
    const otpModalCloseBtn = document.getElementById('otpModalCloseBtn');
    const otpModalBackdrop = document.getElementById('otpModalBackdrop');

    // Move modals to body so they sit above header regardless of container hierarchy
    if (otpAlertModal && otpAlertModal.parentElement !== document.body) {
        document.body.appendChild(otpAlertModal);
    }

    const modalConfig = {
        warning: { accent: 'bg-amber-500', icon: '⚠️', iconBg: 'bg-amber-100 text-amber-600', btnBg: 'bg-amber-500 hover:bg-amber-600', title: 'Warning' },
        error:   { accent: 'bg-rose-500',  icon: '❌', iconBg: 'bg-rose-100 text-rose-600',   btnBg: 'bg-rose-600 hover:bg-rose-700',   title: 'Error' },
        success: { accent: 'bg-emerald-500', icon: '✅', iconBg: 'bg-emerald-100 text-emerald-600', btnBg: 'bg-emerald-600 hover:bg-emerald-700', title: 'Success' },
        info:    { accent: 'bg-primary-500', icon: 'ℹ️', iconBg: 'bg-primary-100 text-primary-600', btnBg: 'bg-primary-600 hover:bg-primary-700', title: 'Info' },
    };

    function showModal(message, type = 'warning', title = null) {
        const cfg = modalConfig[type] || modalConfig.warning;
        // Set accent color
        otpModalAccent.className = 'h-1.5 w-full ' + cfg.accent;
        // Set icon
        otpModalIcon.className = 'shrink-0 w-10 h-10 rounded-xl flex items-center justify-center text-xl ' + cfg.iconBg;
        otpModalIcon.textContent = cfg.icon;
        // Set btn color
        otpModalCloseBtn.className = 'px-5 py-2 font-extrabold text-xs text-white rounded-xl transition-all active:scale-95 shadow cursor-pointer ' + cfg.btnBg;
        // Set content
        otpAlertTitle.textContent = title || cfg.title;
        otpAlertMessage.textContent = message;
        // Show
        otpAlertModal.style.setProperty('display', 'flex', 'important');
        otpAlertModal.classList.remove('hidden');
        setTimeout(() => {
            otpModalPanel.classList.remove('scale-95', 'opacity-0');
            otpModalPanel.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function closeModal() {
        otpModalPanel.classList.remove('scale-100', 'opacity-100');
        otpModalPanel.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            otpAlertModal.classList.add('hidden');
            otpAlertModal.style.setProperty('display', 'none', 'important');
        }, 200);
    }

    if (otpModalCloseBtn) otpModalCloseBtn.addEventListener('click', closeModal);
    if (otpModalBackdrop) otpModalBackdrop.addEventListener('click', closeModal);
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });
    // ─────────────────────────────────────────────────────────────

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || cfg.csrf;

    // ── Prevent Razorpay re-opening on page refresh ──────────
    sessionStorage.removeItem('mem_rzp_inprogress');

    // Form submit listener
    form.addEventListener('submit', async function (e) {
        // Block if this is a browser-replayed POST (F5 after Razorpay opened)
        if (sessionStorage.getItem('mem_rzp_inprogress') === '1') {
            e.preventDefault();
            sessionStorage.removeItem('mem_rzp_inprogress');
            return false;
        }
        // 1. HTML5 validation check
        if (!form.checkValidity()) {
            e.preventDefault();
            form.reportValidity();
            return false;
        }

        // 2. Explicit client validation checks
        const phone = form.querySelector('[name="phone"]')?.value.trim() || '';
        if (phone.length !== 10 || !/^\d{10}$/.test(phone)) {
            e.preventDefault();
            showModal(cfg.i18n.mobile10, 'warning', cfg.i18n.invalidMobile);
            form.querySelector('[name="phone"]')?.focus();
            return false;
        }

        const password = form.querySelector('[name="password"]')?.value || '';
        const passwordConfirmation = form.querySelector('[name="password_confirmation"]')?.value || '';
        if (password.length < 8) {
            e.preventDefault();
            showModal(cfg.i18n.passwordMin8, 'warning', cfg.i18n.invalidPassword);
            form.querySelector('[name="password"]')?.focus();
            return false;
        }
        if (password !== passwordConfirmation) {
            e.preventDefault();
            showModal(cfg.i18n.passwordConfirmMismatch, 'warning', cfg.i18n.passwordMismatch);
            form.querySelector('[name="password_confirmation"]')?.focus();
            return false;
        }

        const paymentIdInput = document.getElementById('razorpay_payment_id');
        if (paymentIdInput && paymentIdInput.value) {
            return true; // Already paid, allow normal submission
        }

        if (!cfg.feeRequired) {

            return true; // no fee: submit normally

        }
            e.preventDefault();

            const submitBtn = document.getElementById('submitMemberBtn');
            const originalBtnHtml = submitBtn ? submitBtn.innerHTML : '';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="inline-block animate-spin mr-2">⏳</span> ' + cfg.i18n.validating;
            }

            // 3. Server-Side Pre-Validation before opening Razorpay
            try {
                const formData = new FormData(form);
                const preValRes = await fetch(cfg.urls.preValidate, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                const preValData = await preValRes.json();

                if (!preValRes.ok || !preValData.success) {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnHtml;
                    }
                    const errorsList = preValData.errors ? preValData.errors.join('\n• ') : (preValData.message || 'વિગતો ચકાસવામાં ભૂલ આવી છે.');
                    showModal('• ' + errorsList, 'error', cfg.i18n.correctErrors);
                    return false;
                }
            } catch (err) {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnHtml;
                }
                showModal(cfg.i18n.networkRetry, 'error', cfg.i18n.networkError);
                return false;
            }

            // 4. Open Razorpay Gateway
            const razorpayKey = cfg.razorpayKey;
            const feeAmountPaise = cfg.feePaise;
            const firstName = form.querySelector('[name="first_name"]')?.value || '';
            const lastName = form.querySelector('[name="last_name"]')?.value || '';
            const email = form.querySelector('[name="email"]')?.value || '';

            const options = {
                "key": razorpayKey || "rzp_test_key",
                "amount": feeAmountPaise,
                "currency": "INR",
                "name": cfg.appName,
                "description": "Membership Registration Fee",
                "handler": function (response) {
                    sessionStorage.removeItem('mem_rzp_inprogress');
                    paymentIdInput.value = response.razorpay_payment_id;
                    window.dispatchEvent(new CustomEvent('show-loader'));
                    form.submit();
                },
                "modal": {
                    "ondismiss": function() {
                        sessionStorage.removeItem('mem_rzp_inprogress');
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalBtnHtml;
                        }
                    }
                },
                "prefill": {
                    "name": firstName + " " + lastName,
                    "email": email,
                    "contact": phone
                },
                "theme": {
                    "color": "#2563EB"
                }
            };

            if (window.Razorpay) {
                sessionStorage.setItem('mem_rzp_inprogress', '1');
                const rzp = new Razorpay(options);
                rzp.on('payment.failed', function (response) {
                    sessionStorage.removeItem('mem_rzp_inprogress');
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnHtml;
                    }
                    showModal(response.error.description || 'પેમેન્ટ અસફળ રહ્યું. કૃપા કરીને ફરી પ્રયાસ કરો.', 'error', 'પેમેન્ટ નિષ્ફળ');
                });
                rzp.open();
            } else {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnHtml;
                }
                showModal('Razorpay Payment Gateway failed to load. Please check your internet connection and try again.', 'error', 'Payment Gateway Error');
            }
    });
});
