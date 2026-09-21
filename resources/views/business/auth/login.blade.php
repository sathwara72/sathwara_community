<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Business Login — Sathwara Community</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Noto+Sans+Gujarati:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    @php
        $primaryColor = App\Models\Setting::get('primary_color', '#ef4444');
    @endphp
    <style>
        :root {
            --primary: {{ $primaryColor }};
            --primary-dark: color-mix(in srgb, {{ $primaryColor }} 80%, black);
            --bg: #0f172a;
            --card-bg: #1e293b;
            --border: #334155;
            --text: #f1f5f9;
            --text-muted: #94a3b8;
            --success: #10b981;
            --danger: #ef4444;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg);
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            padding: 20px;
            background-image:
                radial-gradient(ellipse at 20% 50%, color-mix(in srgb, var(--primary) 15%, transparent) 0%, transparent 60%),
                radial-gradient(ellipse at 80% 20%, color-mix(in srgb, var(--primary) 10%, transparent) 0%, transparent 60%);
        }
        .login-wrapper {
            display: flex;
            width: 100%;
            max-width: 900px;
            min-height: 560px;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 25px 60px rgba(0,0,0,.5);
        }

        /* ── Left Panel ── */
        .login-left {
            flex: 1;
            background: linear-gradient(135deg, color-mix(in srgb, var(--primary) 85%, black) 0%, var(--primary) 50%, #991b1b 100%);
            padding: 48px 40px;
            display: flex; flex-direction: column; justify-content: space-between;
            position: relative; overflow: hidden;
        }
        .login-left::before {
            content: '';
            position: absolute; top: -60px; right: -60px;
            width: 200px; height: 200px; border-radius: 50%;
            background: rgba(255,255,255,.07);
        }
        .login-left::after {
            content: '';
            position: absolute; bottom: -40px; left: -40px;
            width: 160px; height: 160px; border-radius: 50%;
            background: rgba(255,255,255,.05);
        }
        .left-brand { position: relative; z-index: 1; }
        .left-brand .logo-icon {
            width: 52px; height: 52px; border-radius: 14px;
            background: rgba(255,255,255,.2); backdrop-filter: blur(8px);
            display: flex; align-items: center; justify-content: center;
            font-size: 22px; color: #fff; margin-bottom: 20px;
        }
        .left-brand h1 { color: #fff; font-size: 26px; font-weight: 800; line-height: 1.2; }
        .left-brand p  { color: rgba(255,255,255,.75); font-size: 14px; margin-top: 10px; }
        .left-features { position: relative; z-index: 1; }
        .left-features .feature-item {
            display: flex; align-items: center; gap: 12px;
            color: rgba(255,255,255,.85); font-size: 13.5px; margin-bottom: 16px;
        }
        .left-features .feature-item i {
            width: 32px; height: 32px; border-radius: 8px;
            background: rgba(255,255,255,.15);
            display: flex; align-items: center; justify-content: center;
            font-size: 13px; color: #fff; flex-shrink: 0;
        }

        /* ── Right Panel ── */
        .login-right {
            flex: 1.1;
            background: var(--card-bg);
            padding: 48px 44px;
            display: flex; flex-direction: column; justify-content: center;
        }
        .login-right h2 { color: var(--text); font-size: 24px; font-weight: 700; margin-bottom: 6px; }
        .login-right .subtitle { color: var(--text-muted); font-size: 14px; margin-bottom: 32px; }

        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; color: #cbd5e1; font-size: 13px; font-weight: 500; margin-bottom: 7px; }
        .input-wrap { position: relative; }
        .input-wrap i.icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 14px; }
        .input-wrap input {
            width: 100%; padding: 12px 14px 12px 40px;
            background: #0f172a; border: 1px solid var(--border);
            border-radius: 10px; color: var(--text); font-size: 14px;
            transition: border-color .2s, box-shadow .2s;
            outline: none;
        }
        .input-wrap input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px color-mix(in srgb, var(--primary) 20%, transparent);
        }
        .input-wrap input.is-invalid { border-color: var(--danger); }
        .input-wrap .toggle-password {
            position: absolute; right: 14px; top: 50%; transform: translateY(-50%);
            cursor: pointer; color: var(--text-muted); font-size: 14px;
            background: none; border: none; padding: 0;
        }
        .input-wrap .toggle-password:hover { color: var(--text); }
        .invalid-feedback { color: var(--danger); font-size: 12px; margin-top: 5px; }

        .form-options { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; }
        .remember-check { display: flex; align-items: center; gap: 8px; cursor: pointer; }
        .remember-check input[type="checkbox"] { accent-color: var(--primary); width: 15px; height: 15px; }
        .remember-check span { color: var(--text-muted); font-size: 13px; }

        .btn-login {
            width: 100%; padding: 13px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: #fff; border: none; border-radius: 10px;
            font-size: 15px; font-weight: 700; cursor: pointer;
            transition: all .2s; box-shadow: 0 4px 15px color-mix(in srgb, var(--primary) 30%, transparent);
        }
        .btn-login:hover { transform: translateY(-1px); box-shadow: 0 6px 20px color-mix(in srgb, var(--primary) 40%, transparent); }
        .btn-login:active { transform: translateY(0); }
        .btn-login:disabled { opacity: .7; cursor: not-allowed; }

        .login-footer { margin-top: 24px; text-align: center; color: var(--text-muted); font-size: 13px; }
        .login-footer a { color: var(--primary); text-decoration: none; font-weight: 500; }
        .login-footer a:hover { text-decoration: underline; }

        .alert-error {
            background: rgba(239,68,68,.1); border: 1px solid rgba(239,68,68,.3);
            color: #fca5a5; padding: 12px 16px; border-radius: 10px;
            margin-bottom: 20px; font-size: 13px; display: flex; align-items: center; gap: 8px;
        }
        .alert-success-msg {
            background: rgba(16,185,129,.1); border: 1px solid rgba(16,185,129,.3);
            color: #6ee7b7; padding: 12px 16px; border-radius: 10px;
            margin-bottom: 20px; font-size: 13px; display: flex; align-items: center; gap: 8px;
        }

        @media(max-width: 640px) {
            .login-left { display: none; }
            .login-right { padding: 36px 28px; }
            .login-wrapper { max-width: 440px; }
        }
    </style>
</head>
<body>

<div class="login-wrapper">
    {{-- Left Branding Panel --}}
    <div class="login-left">
        <div class="left-brand">
            <div class="logo-icon"><i class="fas fa-store"></i></div>
            <h1>Business Panel</h1>
            <p>Manage your business listing, track renewals, and update your profile.</p>
        </div>
        <div class="left-features">
            <div class="feature-item">
                <i class="fas fa-store-alt"></i>
                <span>Manage your public business listing</span>
            </div>
            <div class="feature-item">
                <i class="fas fa-sync-alt"></i>
                <span>Track renewal dates & payment status</span>
            </div>
            <div class="feature-item">
                <i class="fas fa-user-edit"></i>
                <span>Update your business information easily</span>
            </div>
            <div class="feature-item">
                <i class="fas fa-images"></i>
                <span>Upload logo and gallery photos</span>
            </div>
        </div>
    </div>

    {{-- Right Login Form --}}
    <div class="login-right">
        <h2>Welcome Back</h2>
        <p class="subtitle">Sign in to your Business Panel account</p>

        @if(session('success'))
            <div class="alert-success-msg"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="alert-error">
                <i class="fas fa-exclamation-circle"></i>
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('business.login.submit') }}" id="loginForm">
            @csrf

            <div class="form-group">
                <label for="login">Email or Phone Number</label>
                <div class="input-wrap">
                    <i class="fas fa-user icon"></i>
                    <input type="text" id="login" name="login"
                           value="{{ old('login') }}"
                           placeholder="Enter your email or phone"
                           class="{{ $errors->has('login') ? 'is-invalid' : '' }}"
                           autocomplete="username" required>
                </div>
                @error('login')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrap">
                    <i class="fas fa-lock icon"></i>
                    <input type="password" id="password" name="password"
                           placeholder="Enter your password"
                           class="{{ $errors->has('password') ? 'is-invalid' : '' }}"
                           autocomplete="current-password" required>
                    <button type="button" class="toggle-password" onclick="togglePwd()" id="togglePwdBtn">
                        <i class="fas fa-eye" id="pwdEyeIcon"></i>
                    </button>
                </div>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-options">
                <label class="remember-check">
                    <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                    <span>Remember me</span>
                </label>
            </div>

            <button type="submit" class="btn-login" id="loginBtn">
                <i class="fas fa-sign-in-alt"></i> Sign In to Business Panel
            </button>
        </form>

        <div class="login-footer">
            <p>Don't have an account? <a href="{{ route('register.business') }}">Register your Business</a></p>
            <p style="margin-top:8px;"><a href="{{ route('business.directory') }}">← Back to Business Directory</a></p>
        </div>
    </div>
</div>

<script>
function togglePwd() {
    const pwd = document.getElementById('password');
    const icon = document.getElementById('pwdEyeIcon');
    if (pwd.type === 'password') {
        pwd.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        pwd.type = 'password';
        icon.className = 'fas fa-eye';
    }
}

document.getElementById('loginForm').addEventListener('submit', function() {
    const btn = document.getElementById('loginBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Signing In...';
});
</script>
</body>
</html>
