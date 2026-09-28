# 🛡️ Website Security Audit & Hardening Report

**Target Application:** Shree Satwara Gnati Mandal Community Portal (`satwarasamajahd.org`)  
**Audit Date:** September 2026  
**Framework:** Laravel 12.x / PHP 8.2+ / MySQL  
**Scope:** Authentication, Member & Business Registration, OTP Verification, Razorpay Payments, File Uploads, API Endpoints, HTTP Headers, and Session Management.

---

## 📊 1. Security Overview & Findings Matrix

| # | Security Category | Status in Project | Risk Level | Description |
|---|-------------------|-------------------|------------|-------------|
| 1 | **Payment Signature Verification** | ❌ **Missing** | 🔴 **Critical** | Server accepts any client `razorpay_payment_id` without verifying signature or transaction status. |
| 2 | **Payment ID Replay Protection** | ❌ **Missing** | 🔴 **Critical** | `payment_id` is not enforced as unique; same payment ID can be submitted multiple times. |
| 3 | **OTP Brute-Force Rate Limiting** | ❌ **Missing** | 🟠 **High** | No limit on incorrect 6-digit OTP verification attempts within the 10-minute expiry window. |
| 4 | **OTP Request / Flooding Throttle** | ❌ **Missing** | 🟠 **High** | Endpoints `/register/business/send-otp` and `/forgot-password` have no rate limiter on sends. |
| 5 | **Plaintext OTP Logging** | ⚠️ **Vulnerable** | 🟠 **High** | Full 6-digit OTP codes are logged in plaintext to `storage/logs/laravel.log`. |
| 6 | **HTTP Security Headers** | ❌ **Missing** | 🟠 **High** | No `X-Frame-Options`, `CSP`, `X-Content-Type-Options`, or `Referrer-Policy` middleware. |
| 7 | **Business Login Rate Limiting** | ❌ **Missing** | 🟠 **High** | `BusinessAuthController::login` has no login attempt throttling, enabling password brute-forcing. |
| 8 | **Business Login Account Enumeration**| ⚠️ **Vulnerable** | 🟡 **Medium** | Distinct error message reveals if email or phone is registered in the database. |
| 9 | **Public API Data Enumeration / Scraping** | ❌ **Missing** | 🟡 **Medium** | `/api/check-member-id` and `/api/lookup-father-member` are unthrottled and expose full names. |
| 10 | **Contact Form Spam Protection** | ❌ **Missing** | 🟡 **Medium** | `/contact-us` lacks CAPTCHA or IP submission rate limits, allowing automated mail flooding. |
| 11 | **Search Engine Exclusion (`robots.txt`)** | ❌ **Missing** | 🟡 **Medium** | `public/robots.txt` has empty `Disallow:`, allowing search engine crawlers to index admin & panel URLs. |
| 12 | **Session Cookie `Secure` Flag** | ⚠️ **Config-dependent** | 🟡 **Medium** | `config/session.php` only enables `secure` if `SESSION_SECURE_COOKIE=true` is set in `.env`. |
| 13 | **SQL Injection Protection** | ✅ **Available** | 🟢 **Good** | Protected via Eloquent ORM and PDO prepared statements. |
| 14 | **Cross-Site Request Forgery (CSRF)** | ✅ **Available** | 🟢 **Good** | Protected across all POST routes via `@csrf` token verification. |
| 15 | **Password Storage Hashing** | ✅ **Available** | 🟢 **Good** | Protected via standard Bcrypt one-way hashing (`Hash::make`). |
| 16 | **File Upload Name Obfuscation** | ✅ **Available** | 🟢 **Good** | Uploaded photos are stored using randomized SHA-1 hash filenames, preventing path traversal. |
| 17 | **Member Login Rate Limiting** | ✅ **Available** | 🟢 **Good** | Protected via `LoginRequest` (5 attempts max with lockout). |

---

## 🔍 2. Detailed Findings & Developer Action Plan

### 🔴 Finding 1: Server-Side Razorpay Payment Verification
* **Affected Files:**
  * `app/Http/Controllers/RegistrationController.php` (lines ~248-250 and ~545-547)
* **Vulnerability:**
  The server accepts whatever `razorpay_payment_id` is sent from the frontend:
  ```php
  $paymentId = $request->input('razorpay_payment_id');
  $paymentStatus = (!empty($paymentId) || $signupFee <= 0) ? 'paid' : 'unpaid';
  ```
  A malicious user can intercept the request or submit a dummy string (e.g. `pay_fake123456`) and get registered as `payment_status = 'paid'` without paying.
* **Developer Fix:**
  Verify the payment via Razorpay API before marking `paid`, or verify `razorpay_signature` if using Razorpay Order API:
  ```php
  if (!empty($paymentId)) {
      $api = new \Razorpay\Api\Api(
          \App\Models\Setting::get('razorpay_key_id'),
          \App\Models\Setting::get('razorpay_key_secret')
      );
      $payment = $api->payment->fetch($paymentId);
      if ($payment->status !== 'captured' && $payment->status !== 'authorized') {
          return back()->withErrors(['payment' => 'Payment could not be verified with Razorpay.']);
      }
      if ($payment->amount < ($signupFee * 100)) {
          return back()->withErrors(['payment' => 'Payment amount does not match the required fee.']);
      }
  }
  ```

---

### 🔴 Finding 2: Missing Payment ID Replay Prevention
* **Affected Files:**
  * `app/Http/Controllers/RegistrationController.php`
  * `database/migrations/`
* **Vulnerability:**
  The `payment_id` column is not enforced unique across registrations. A single valid payment receipt can be submitted by multiple fraudulent accounts.
* **Developer Fix:**
  Add a validation rule checking that the payment ID has not already been recorded in `users`, `businesses`, or `business_payment_links`:
  ```php
  if (!empty($paymentId)) {
      $alreadyUsed = \App\Models\User::where('payment_id', $paymentId)->exists()
          || \App\Models\Business::where('payment_id', $paymentId)->exists();
      if ($alreadyUsed) {
          return back()->withErrors(['payment' => 'This payment transaction ID has already been utilized.']);
      }
  }
  ```

---

### 🟠 Finding 3: OTP Brute-Force & Attempt Limiting
* **Affected Files:**
  * `app/Http/Controllers/RegistrationController.php` (`verifyBusinessRegistrationOtp`)
  * `app/Http/Controllers/Auth/OtpPasswordResetController.php`
* **Vulnerability:**
  The 6-digit code has 1,000,000 possibilities and lives for 10 minutes. Without an attempt counter, an automated script can send thousands of guesses and bypass email verification.
* **Developer Fix:**
  Track failed attempts in session and invalidate the OTP after 3-5 failed attempts:
  ```php
  $attempts = (int) session('biz_reg_otp_attempts', 0) + 1;
  session(['biz_reg_otp_attempts' => $attempts]);

  if ($attempts > 5) {
      session()->forget(['biz_reg_otp_code', 'biz_reg_otp_email', 'biz_reg_otp_expires', 'biz_reg_otp_attempts']);
      return response()->json([
          'success' => false,
          'message' => 'Too many failed OTP attempts. Please request a new code.',
      ], 429);
  }
  ```

---

### 🟠 Finding 4: OTP Request Flooding & Sensitive Logging
* **Affected Files:**
  * `routes/web.php` (`/register/business/send-otp`, `/forgot-password`)
  * `app/Http/Controllers/RegistrationController.php` (line 392)
* **Vulnerabilities:**
  1. No rate limiting on sending OTP emails.
  2. `Log::info("Business Registration OTP for {$email}: {$otp}...");` writes secrets to disk.
* **Developer Fix:**
  1. Add `throttle:3,10` (max 3 OTP requests per 10 minutes) on routes.
  2. Remove `$otp` from all `Log::info` statements.

---

### 🟠 Finding 5: Missing HTTP Security Headers
* **Affected Files:**
  * `bootstrap/app.php`
* **Vulnerability:**
  The site does not emit defensive HTTP headers. This allows the application to be embedded in malicious `<iframe>` tags (Clickjacking) and exposes it to MIME-type sniffing and cross-origin leakage.
* **Developer Fix:**
  Create and attach a `SecurityHeaders` middleware in `bootstrap/app.php`:
  ```php
  $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
  $response->headers->set('X-Content-Type-Options', 'nosniff');
  $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
  $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
  $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
  ```

---

### 🟠 Finding 6: Business Login Rate Limiting & Account Enumeration
* **Affected Files:**
  * `app/Http/Controllers/Business/BusinessAuthController.php`
* **Vulnerabilities:**
  1. An attacker can attempt unlimited password guesses on `/business/login`.
  2. Message `"No business account found with this email or phone number."` confirms to an attacker whether an email is registered.
* **Developer Fix:**
  1. Use Laravel's `RateLimiter`:
     ```php
     $throttleKey = Str::transliterate(Str::lower($request->input('login')).'|'.$request->ip());
     if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
         throw ValidationException::withMessages(['login' => 'Too many login attempts. Please try again in ' . RateLimiter::availableIn($throttleKey) . ' seconds.']);
     }
     ```
  2. Use generic failure messages: `"These credentials do not match our records."`

---

### 🟡 Finding 7: Unrestricted Public API Data Scraping
* **Affected Files:**
  * `routes/web.php` (`/api/check-member-id`, `/api/lookup-father-member`)
* **Vulnerability:**
  Anyone can loop through IDs `SSAM0001` to `SSAM9999` and harvest the full community member roster.
* **Developer Fix:**
  Attach strict rate limiting middleware:
  ```php
  Route::get('/api/check-member-id', ...)->middleware('throttle:30,1');
  Route::get('/api/lookup-father-member', ...)->middleware('throttle:30,1');
  ```

---

### 🟡 Finding 8: Search Engine Indexing (`public/robots.txt`)
* **Affected File:**
  * `public/robots.txt`
* **Vulnerability:**
  `Disallow:` is empty, allowing crawlers to discover and index administrative portals and registration pages.
* **Developer Fix:**
  Update `public/robots.txt`:
  ```txt
  User-agent: *
  Disallow: /admin/
  Disallow: /business/
  Disallow: /member/
  Disallow: /api/
  Allow: /
  ```

---

## 🚀 3. Production Deployment Hardening Checklist

When deploying to `https://satwarasamajahd.org/`:

- [ ] **Ensure `APP_DEBUG=false`** in `.env` (prevents stack traces and credentials from leaking on errors).
- [ ] **Set `SESSION_SECURE_COOKIE=true`** in `.env` so cookies are only transmitted via HTTPS.
- [ ] **Configure Razorpay Webhook Secret** in Admin Settings and in Razorpay Dashboard to ensure webhooks cannot be forged.
- [ ] **Nginx PHP execution block in storage**:
  ```nginx
  location ~* /storage/.*\.php$ {
      deny all;
      return 404;
  }
  ```
- [ ] **Enable Cloudflare / WAF** with Rate Limiting and DDoS mitigation.

---
*Generated for Shree Satwara Gnati Mandal (`satwarasamajahd.org`)*
