// Business registration: client checks, server pre-validation, then Razorpay Checkout.
// Page values come from window.BusinessRegisterConfig (set in register_business.blade.php).
document.addEventListener('DOMContentLoaded', function () {
    const cfg = window.BusinessRegisterConfig;
    const form = document.getElementById('businessRegisterForm');
    if (!form) return;

    // ── Prevent Razorpay re-opening on page refresh ──────────────
    // If sessionStorage shows a payment was already initiated, do NOT
    // re-trigger Razorpay when the page is refreshed (F5 / browser back).
    sessionStorage.removeItem('biz_rzp_inprogress');

    const submitBtn = document.getElementById('submitBusinessBtn');
    const originalBtnHtml = submitBtn ? submitBtn.innerHTML : '';
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || cfg.csrf;

    // ── Alert modal (messages are set as text, never HTML) ──────
    const alertModal = document.getElementById('bizAlertModal');
    if (alertModal && alertModal.parentElement !== document.body) document.body.appendChild(alertModal);
    const alertColors = { warning: '#f59e0b', error: '#e11d48' };
    function showAlert(message, type = 'warning', title = 'Notice') {
        document.getElementById('bizAlertAccent').style.background = alertColors[type] || alertColors.warning;
        document.getElementById('bizAlertTitle').textContent = title;
        document.getElementById('bizAlertMessage').textContent = message;
        alertModal.style.display = 'flex';
    }
    function closeAlert() { alertModal.style.display = 'none'; }
    document.getElementById('bizAlertClose')?.addEventListener('click', closeAlert);
    document.getElementById('bizAlertBackdrop')?.addEventListener('click', closeAlert);
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeAlert(); });

    form.addEventListener('submit', async function (e) {
        const paymentIdInput = document.getElementById('razorpay_payment_id');

        // Already captured payment_id — allow normal form submit to server
        if (paymentIdInput && paymentIdInput.value) {
            return true;
        }

        // If browser is replaying a cached POST (refresh), just abort silently
        if (sessionStorage.getItem('biz_rzp_inprogress') === '1') {
            e.preventDefault();
            sessionStorage.removeItem('biz_rzp_inprogress');
            return false;
        }

        e.preventDefault();

        // HTML5 validation
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const password = form.querySelector('[name="password"]')?.value || '';
        const passwordConfirm = form.querySelector('[name="password_confirmation"]')?.value || '';

        if (password.length < 6) {
            showAlert('Password must be at least 6 characters long.', 'warning', 'Password Too Short');
            form.querySelector('[name="password"]')?.focus();
            return;
        }

        if (password !== passwordConfirm) {
            showAlert('The password and confirmation do not match. (પાસવર્ડ અને કન્ફર્મ પાસવર્ડ સરખા નથી.)', 'warning', 'Password Mismatch');
            form.querySelector('[name="password_confirmation"]')?.focus();
            return;
        }

        const phone = (form.querySelector('[name="phone"]')?.value || '').trim();
        if (phone.length !== 10 || !/^\d{10}$/.test(phone)) {
            showAlert(cfg.i18n.mobile10, 'warning', 'Invalid Mobile Number');
            form.querySelector('[name="phone"]')?.focus();
            return;
        }

        const logoInput = form.querySelector('[name="logo"]');
        if (logoInput && (!logoInput.files || logoInput.files.length === 0)) {
            showAlert('Please upload your Business Logo or Visiting Card.', 'warning', 'Logo Required');
            logoInput.focus();
            return;
        }

        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span style="display:inline-flex;align-items:center;gap:6px;"><svg style="width:14px;height:14px;animation:spin 1s linear infinite;" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Validating details...</span>';
        }

        // ── Server-Side Pre-Validation before opening Razorpay ──
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
                const errorsList = preValData.errors ? preValData.errors.join('\n• ') : (preValData.message || 'Please correct errors before payment.');
                showAlert('• ' + errorsList, 'error', 'Please correct these details');
                return false;
            }
        } catch (err) {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnHtml;
            }
            showAlert('Unable to validate details with server. Please try again.', 'error', 'Network Error');
            return false;
        }

        if (submitBtn) {
            submitBtn.innerHTML = '<span style="display:inline-flex;align-items:center;gap:6px;"><svg style="width:14px;height:14px;animation:spin 1s linear infinite;" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Opening payment...</span>';
        }

        const razorpayKey    = cfg.razorpayKey;
        const feeAmountPaise = cfg.feePaise;
        const businessName   = form.querySelector('[name="business_name"]')?.value || '';
        const ownerName      = form.querySelector('[name="owner_name"]')?.value || '';
        const email          = form.querySelector('[name="email"]')?.value || '';

        const resetSubmitBtn = function () {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnHtml;
            }
        };

        const options = {
            "key":         razorpayKey || "rzp_test_key",
            "amount":      feeAmountPaise,
            "currency":    "INR",
            "name":        cfg.appName,
            "description": "Business Registration Fee - " + businessName,
            "handler": function (response) {
                // Mark payment done — clear session flag
                sessionStorage.removeItem('biz_rzp_inprogress');
                paymentIdInput.value = response.razorpay_payment_id;
                form.submit();
            },
            "modal": {
                "ondismiss": function () {
                    // User closed Razorpay without paying — reset button
                    sessionStorage.removeItem('biz_rzp_inprogress');
                    resetSubmitBtn();
                }
            },
            "prefill": {
                "name":    ownerName,
                "email":   email,
                "contact": phone
            },
            "theme": { "color": "#2563EB" }
        };

        function launchRazorpay() {
            sessionStorage.setItem('biz_rzp_inprogress', '1');
            const rzp = new Razorpay(options);
            rzp.on('payment.failed', function () {
                sessionStorage.removeItem('biz_rzp_inprogress');
                resetSubmitBtn();
            });
            rzp.open();
        }

        if (window.Razorpay) {
            launchRazorpay();
        } else {
            const script = document.createElement('script');
            script.src = 'https://checkout.razorpay.com/v1/checkout.js';
            script.onload = launchRazorpay;
            script.onerror = function () {
                sessionStorage.removeItem('biz_rzp_inprogress');
                resetSubmitBtn();
                showAlert('Razorpay Payment Gateway failed to load. Please check your internet connection.', 'error', 'Payment Error');
            };
            document.head.appendChild(script);
        }
    });
});
