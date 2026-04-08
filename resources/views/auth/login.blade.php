<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>تسجيل الدخول - MAGZANI</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;900&family=Tajawal:wght@300;400;500;700&display=swap" rel="stylesheet">

    <style>
        * { font-family: 'Cairo', 'Tajawal', sans-serif; box-sizing: border-box; }

        /* ── Background ── */
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background:
                radial-gradient(ellipse at 20% 20%, rgba(85,114,255,0.18) 0%, transparent 55%),
                radial-gradient(ellipse at 80% 80%, rgba(99,102,241,0.14) 0%, transparent 55%),
                radial-gradient(ellipse at 50% 0%,  rgba(59,130,246,0.1)  0%, transparent 50%),
                linear-gradient(160deg, #0d1232 0%, #111827 40%, #0a0f22 100%);
        }

        /* Animated constellation dots */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image:
                radial-gradient(circle, rgba(255,255,255,0.08) 1px, transparent 1px),
                radial-gradient(circle, rgba(85,114,255,0.12) 1px, transparent 1px);
            background-size: 60px 60px, 40px 40px;
            background-position: 0 0, 20px 20px;
            pointer-events: none;
            animation: starDrift 20s linear infinite;
        }
        @keyframes starDrift {
            from { background-position: 0 0, 20px 20px; }
            to   { background-position: 60px 60px, 80px 80px; }
        }

        /* ── Card ── */
        .login-card {
            background: rgba(255,255,255,0.96);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 40px 40px 36px;
            width: 100%;
            max-width: 420px;
            box-shadow:
                0 30px 80px rgba(0,0,0,0.5),
                0 0 0 1px rgba(85,114,255,0.15),
                inset 0 1px 0 rgba(255,255,255,0.8);
            position: relative;
            overflow: hidden;
        }
        .login-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            background: linear-gradient(90deg, #5572ff, #6366f1, #3b82f6, #8b5cf6, #5572ff);
            background-size: 200% 100%;
            animation: shimmerBar 3s linear infinite;
        }
        @keyframes shimmerBar {
            from { background-position: 0% 0%; }
            to   { background-position: 200% 0%; }
        }

        /* ── Logo animations ── */
        .logo-wrapper {
            display: flex;
            justify-content: center;
            margin-bottom: 24px;
            position: relative;
        }
        .logo-wrapper::before {
            content: '';
            position: absolute;
            inset: -20px;
            background: radial-gradient(circle, rgba(85,114,255,0.2) 0%, transparent 70%);
            border-radius: 50%;
            animation: haloBreath 3s ease-in-out infinite;
        }
        @keyframes haloBreath {
            0%, 100% { opacity: 0.6; transform: scale(0.9); }
            50%       { opacity: 1;   transform: scale(1.1); }
        }
        .logo-svg {
            position: relative;
            z-index: 1;
            animation: logoFloat 4s ease-in-out infinite;
            filter: drop-shadow(0 0 22px rgba(85,114,255,0.65));
        }
        @keyframes logoFloat {
            0%, 100% { transform: translateY(0);   filter: drop-shadow(0 0 22px rgba(85,114,255,0.65)); }
            50%       { transform: translateY(-5px); filter: drop-shadow(0 0 34px rgba(85,114,255,0.9));  }
        }

        /* ── Text ── */
        .brand-name {
            text-align: center;
            color: #e8edf8;
            font-size: 30px;
            font-weight: 900;
            letter-spacing: 10px;
            margin: 0 0 4px;
            text-shadow: 0 0 30px rgba(85,114,255,0.6);
        }
        .brand-sub {
            text-align: center;
            color: rgba(255,255,255,0.35);
            font-size: 11px;
            letter-spacing: 4px;
            margin: 0 0 32px;
        }

        /* ── Form ── */
        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: #374151;
            margin-bottom: 8px;
        }
        .form-input {
            width: 100%;
            padding: 12px 16px;
            border: 1.5px solid #e5e7eb;
            border-radius: 12px;
            font-size: 14px;
            font-family: 'Cairo', sans-serif;
            color: #111827;
            outline: none;
            transition: all 0.25s;
            background: #fafafa;
        }
        .form-input:focus {
            border-color: #5572ff;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(85,114,255,0.12);
        }
        .form-input.error-border { border-color: #ef4444; }
        .form-group { margin-bottom: 22px; }

        .input-icon-wrap {
            position: relative;
        }
        .input-icon-wrap .form-input {
            padding-right: 44px;
        }
        .input-icon {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 14px;
            pointer-events: none;
        }

        /* ── Submit button ── */
        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #5572ff 0%, #6366f1 50%, #4f8ef7 100%);
            color: #fff;
            font-family: 'Cairo', sans-serif;
            font-size: 15px;
            font-weight: 800;
            border: none;
            border-radius: 13px;
            cursor: pointer;
            letter-spacing: 1px;
            transition: all 0.3s;
            box-shadow: 0 6px 24px rgba(85,114,255,0.45);
            position: relative;
            overflow: hidden;
        }
        .btn-login::before {
            content: '';
            position: absolute;
            top: 0; left: -100%;
            width: 60%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.18), transparent);
            transition: left 0.5s;
        }
        .btn-login:hover { transform: translateY(-2px); box-shadow: 0 10px 32px rgba(85,114,255,0.6); }
        .btn-login:hover::before { left: 140%; }
        .btn-login:active { transform: translateY(0); }

        /* ── Alert ── */
        .alert-error {
            background: #fef2f2;
            border: 1px solid #fca5a5;
            color: #b91c1c;
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 13px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .err-msg { color: #ef4444; font-size: 12px; margin-top: 5px; }

        /* Remember me */
        .remember-wrap {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 22px;
        }
        .remember-wrap input[type="checkbox"] {
            width: 17px; height: 17px;
            accent-color: #5572ff;
            cursor: pointer;
        }
        .remember-wrap label {
            font-size: 13px;
            color: #6b7280;
            cursor: pointer;
        }
    </style>
</head>
<body>

<div style="width:100%;max-width:480px;padding:24px 16px;">

    <!-- Brand header above card -->
    <div class="logo-wrapper" style="margin-bottom:20px;">
        <!-- FULL ENHANCED LOGO -->
        <svg class="logo-svg" width="130" height="130" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <filter id="lspk" x="-120%" y="-120%" width="340%" height="340%">
                    <feGaussianBlur in="SourceGraphic" stdDeviation="2" result="b1"/>
                    <feGaussianBlur in="SourceGraphic" stdDeviation="0.8" result="b2"/>
                    <feMerge><feMergeNode in="b1"/><feMergeNode in="b2"/><feMergeNode in="SourceGraphic"/></feMerge>
                </filter>
                <filter id="lblu" x="-80%" y="-80%" width="260%" height="260%">
                    <feGaussianBlur in="SourceGraphic" stdDeviation="2.5" result="b"/>
                    <feMerge><feMergeNode in="b"/><feMergeNode in="SourceGraphic"/></feMerge>
                </filter>
                <radialGradient id="lcbg" cx="45%" cy="32%" r="68%">
                    <stop offset="0%" stop-color="#1f2b78"/>
                    <stop offset="55%" stop-color="#151c55"/>
                    <stop offset="100%" stop-color="#090d22"/>
                </radialGradient>
                <radialGradient id="lmg" cx="50%" cy="0%" r="100%">
                    <stop offset="0%" stop-color="#f0f3ff"/>
                    <stop offset="100%" stop-color="#c8d0e8"/>
                </radialGradient>
            </defs>

            <!-- Badge background -->
            <circle cx="50" cy="50" r="48" fill="url(#lcbg)"/>

            <!-- Precision rings -->
            <circle cx="50" cy="50" r="46.5" stroke="#c0cae0" stroke-width="1.1" fill="none" opacity="0.55"/>
            <circle cx="50" cy="50" r="44"   stroke="#c0cae0" stroke-width="0.5" fill="none" opacity="0.2"/>
            <circle cx="50" cy="50" r="41"   stroke="#c0cae0" stroke-width="0.4" stroke-dasharray="2.5,5" fill="none" opacity="0.22"/>
            <circle cx="50" cy="50" r="38"   stroke="#4466ee" stroke-width="0.3" stroke-dasharray="1,8"   fill="none" opacity="0.28"/>

            <!-- M letter with gradient stroke -->
            <polyline points="14,72 14,32 50,52 86,32 86,72"
                fill="none" stroke="url(#lmg)" stroke-width="11.5"
                stroke-linejoin="miter" stroke-miterlimit="10" stroke-linecap="butt"/>

            <!-- Inner sheen on M -->
            <polyline points="14,72 14,32 50,52 86,32 86,72"
                fill="none" stroke="#ffffff" stroke-width="10"
                stroke-linejoin="miter" stroke-miterlimit="10" stroke-linecap="butt" opacity="0.08"/>

            <!-- Blue accent peaks -->
            <polyline points="9,31 14,22 19,31" fill="none" stroke="#5572ff" stroke-width="2.2" stroke-linejoin="round" filter="url(#lblu)"/>
            <polyline points="81,31 86,22 91,31" fill="none" stroke="#5572ff" stroke-width="2.2" stroke-linejoin="round" filter="url(#lblu)"/>

            <!-- ══ DIAMOND SPARKLES ══ -->
            <!-- Top-right large -->
            <path d="M 80,12 L 82.5,19.5 L 90,22 L 82.5,24.5 L 80,32 L 77.5,24.5 L 70,22 L 77.5,19.5 Z"
                fill="white" opacity="0.93" filter="url(#lspk)"/>
            <!-- Top-left large -->
            <path d="M 20,12 L 22.5,19.5 L 30,22 L 22.5,24.5 L 20,32 L 17.5,24.5 L 10,22 L 17.5,19.5 Z"
                fill="white" opacity="0.93" filter="url(#lspk)"/>
            <!-- Top-center medium -->
            <path d="M 50,4 L 52,10.5 L 58.5,12.5 L 52,14.5 L 50,21 L 48,14.5 L 41.5,12.5 L 48,10.5 Z"
                fill="white" opacity="0.86" filter="url(#lspk)"/>
            <!-- Left-side small -->
            <path d="M 11,52 L 12.5,57 L 17.5,58.5 L 12.5,60 L 11,65 L 9.5,60 L 4.5,58.5 L 9.5,57 Z"
                fill="white" opacity="0.68" filter="url(#lspk)"/>
            <!-- Right-side small -->
            <path d="M 89,52 L 90.5,57 L 95.5,58.5 L 90.5,60 L 89,65 L 87.5,60 L 82.5,58.5 L 87.5,57 Z"
                fill="white" opacity="0.68" filter="url(#lspk)"/>
            <!-- Bottom-left tiny -->
            <path d="M 26,77 L 27,80.5 L 30.5,81.5 L 27,82.5 L 26,86 L 25,82.5 L 21.5,81.5 L 25,80.5 Z"
                fill="white" opacity="0.5"/>
            <!-- Bottom-right tiny -->
            <path d="M 74,77 L 75,80.5 L 78.5,81.5 L 75,82.5 L 74,86 L 73,82.5 L 69.5,81.5 L 73,80.5 Z"
                fill="white" opacity="0.5"/>
            <!-- Micro accent diamonds -->
            <path d="M 38,13 L 39,16.5 L 42.5,17.5 L 39,18.5 L 38,22 L 37,18.5 L 33.5,17.5 L 37,16.5 Z"
                fill="white" opacity="0.58" filter="url(#lspk)"/>
            <path d="M 62,13 L 63,16.5 L 66.5,17.5 L 63,18.5 L 62,22 L 61,18.5 L 57.5,17.5 L 61,16.5 Z"
                fill="white" opacity="0.58" filter="url(#lspk)"/>
            <!-- Blue accent dots -->
            <circle cx="36.5" cy="57" r="1.3" fill="#7b92ff" opacity="0.65"/>
            <circle cx="63.5" cy="57" r="1.3" fill="#7b92ff" opacity="0.65"/>
            <circle cx="50"   cy="88" r="1.1" fill="#7b92ff" opacity="0.4"/>
            <circle cx="19"   cy="42" r="0.9" fill="#aabbff" opacity="0.5"/>
            <circle cx="81"   cy="42" r="0.9" fill="#aabbff" opacity="0.5"/>
        </svg>
    </div>

    <p class="brand-name">MAGZANI</p>
    <p class="brand-sub">WAREHOUSES &amp; INVOICES</p>

    <!-- Login Card -->
    <div class="login-card">

        @if(session('error'))
        <div class="alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <span>{{ session('error') }}</span>
        </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <!-- Email -->
            <div class="form-group">
                <label class="form-label" for="email">
                    البريد الإلكتروني
                </label>
                <div class="input-icon-wrap">
                    <input
                        id="email" type="email" name="email"
                        value="{{ old('email') }}"
                        required autocomplete="email" autofocus
                        placeholder="أدخل البريد الإلكتروني"
                        class="form-input @error('email') error-border @enderror">
                    <i class="fas fa-envelope input-icon"></i>
                </div>
                @error('email')<p class="err-msg">{{ $message }}</p>@enderror
            </div>

            <!-- Password -->
            <div class="form-group">
                <label class="form-label" for="password">
                    كلمة المرور
                </label>
                <div class="input-icon-wrap">
                    <input
                        id="password" type="password" name="password"
                        required autocomplete="current-password"
                        placeholder="أدخل كلمة المرور"
                        class="form-input @error('password') error-border @enderror">
                    <i class="fas fa-lock input-icon"></i>
                </div>
                @error('password')<p class="err-msg">{{ $message }}</p>@enderror
            </div>

            <!-- Remember me -->
            <div class="remember-wrap">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember">تذكرني</label>
            </div>

            <!-- Submit -->
            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt" style="margin-left:8px;"></i>
                تسجيل الدخول
            </button>
        </form>

    </div>

    <!-- Footer note -->
    <p style="text-align:center;color:rgba(255,255,255,0.2);font-size:11px;margin-top:20px;letter-spacing:2px;">
        © {{ date('Y') }} MAGZANI — All rights reserved
    </p>

</div>

</body>
</html>