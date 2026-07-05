<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>تسجيل الدخول - KAYAN</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;800&family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">

    <style>
        * {
            font-family: 'Cairo', 'Outfit', sans-serif;
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: radial-gradient(ellipse at 10% 10%, rgba(37, 99, 235, 0.18) 0%, transparent 60%),
                        radial-gradient(ellipse at 90% 90%, rgba(99, 102, 241, 0.16) 0%, transparent 60%),
                        linear-gradient(135deg, #090d22 0%, #030712 100%);
            position: relative;
            overflow-x: hidden;
        }

        /* Ambient Stars Background */
        body::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: 
                radial-gradient(circle, rgba(255, 255, 255, 0.08) 1.2px, transparent 1.2px),
                radial-gradient(circle, rgba(99, 102, 241, 0.12) 1px, transparent 1px);
            background-size: 80px 80px, 40px 40px;
            background-position: 0 0, 20px 20px;
            z-index: 0;
            pointer-events: none;
            animation: driftBackground 40s linear infinite;
        }

        @keyframes driftBackground {
            from { background-position: 0 0, 20px 20px; }
            to { background-position: 80px 80px, 100px 100px; }
        }

        .login-container {
            width: 100%;
            max-width: 440px;
            padding: 20px;
            z-index: 10;
            animation: fadeInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) both;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(24px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Glassmorphic login card */
        .glass-card {
            background: rgba(17, 24, 39, 0.65);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 28px;
            padding: 44px 36px 36px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5),
                        0 0 40px 0 rgba(37, 99, 235, 0.1);
            position: relative;
        }

        .glass-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #2563eb, #6366f1, #38bdf8, #2563eb);
            background-size: 200% 100%;
            border-radius: 28px 28px 0 0;
            animation: borderGlow 4s linear infinite;
        }

        @keyframes borderGlow {
            from { background-position: 0% 0%; }
            to { background-position: 200% 0%; }
        }

        /* Logo and Brand styles */
        .logo-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 32px;
        }

        .logo-wrapper {
            position: relative;
            animation: logoFloat 4s ease-in-out infinite;
        }

        @keyframes logoFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-6px); }
        }

        .logo-shadow {
            position: absolute;
            inset: -10px;
            background: radial-gradient(circle, rgba(37, 99, 235, 0.4) 0%, transparent 70%);
            filter: blur(8px);
            border-radius: 50%;
            pointer-events: none;
        }

        .brand-title {
            color: #ffffff;
            font-size: 26px;
            font-weight: 900;
            letter-spacing: 6px;
            margin-top: 14px;
            text-align: center;
            text-shadow: 0 0 20px rgba(37, 99, 235, 0.3);
        }

        .brand-subtitle {
            color: #9ca3af;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 3px;
            margin-top: 4px;
            text-align: center;
            opacity: 0.85;
        }

        /* Form Inputs */
        .form-label {
            display: block;
            color: #e5e7eb;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .input-wrapper {
            position: relative;
        }

        .glass-input {
            width: 100%;
            padding: 13px 44px 13px 16px;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 14px;
            color: #ffffff;
            font-size: 14px;
            outline: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .glass-input::placeholder {
            color: #6b7280;
        }

        .glass-input:focus {
            background: rgba(255, 255, 255, 0.08);
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.18);
        }

        .glass-input.error-border {
            border-color: #ef4444;
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.15);
        }

        .input-icon {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 15px;
            pointer-events: none;
            transition: color 0.3s;
        }

        .glass-input:focus + .input-icon {
            color: #2563eb;
        }

        /* Button styles */
        .btn-submit {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: #ffffff;
            font-size: 15px;
            font-weight: 700;
            border-radius: 14px;
            border: none;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 8px 24px rgba(37, 99, 235, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(37, 99, 235, 0.5);
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        /* Custom Checkbox */
        .checkbox-container {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            user-select: none;
            color: #9ca3af;
            font-size: 13px;
        }

        .checkbox-container input {
            display: none;
        }

        .custom-checkbox {
            width: 18px;
            height: 18px;
            border: 1.5px solid rgba(255, 255, 255, 0.2);
            border-radius: 6px;
            display: inline-block;
            position: relative;
            transition: all 0.2s;
            background: rgba(255, 255, 255, 0.04);
        }

        .checkbox-container input:checked + .custom-checkbox {
            background: #2563eb;
            border-color: #2563eb;
        }

        .checkbox-container input:checked + .custom-checkbox::after {
            content: '\f00c';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            font-size: 10px;
            color: #ffffff;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        /* Alerts */
        .glass-alert {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.25);
            border-radius: 12px;
            padding: 12px 16px;
            color: #fca5a5;
            font-size: 13px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .error-message {
            color: #f87171;
            font-size: 12px;
            margin-top: 6px;
            display: block;
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="glass-card">
        <!-- Logo and brand header -->
        <div class="logo-container">
            <div class="logo-wrapper">
                <div class="logo-shadow"></div>
                <!-- KAYAN LOGO -->
                <svg class="logo-svg" width="100" height="100" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <radialGradient id="kayanBgLarge" cx="40%" cy="30%" r="75%">
                            <stop offset="0%" stop-color="#2563eb" />
                            <stop offset="60%" stop-color="#1d4ed8" />
                            <stop offset="100%" stop-color="#0b132b" />
                        </radialGradient>
                        <linearGradient id="kLeftLarge" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#60a5fa" />
                            <stop offset="100%" stop-color="#2563eb" />
                        </linearGradient>
                        <linearGradient id="kRightTopLarge" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#38bdf8" />
                            <stop offset="100%" stop-color="#0284c7" />
                        </linearGradient>
                        <linearGradient id="kRightBottomLarge" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#0284c7" />
                            <stop offset="100%" stop-color="#1e3a8a" />
                        </linearGradient>
                        <filter id="glowLarge" x="-20%" y="-20%" width="140%" height="140%">
                            <feGaussianBlur stdDeviation="3" result="blur" />
                            <feComposite in="SourceGraphic" in2="blur" operator="over" />
                        </filter>
                    </defs>
                    <rect x="5" y="5" width="90" height="90" rx="26" fill="url(#kayanBgLarge)" stroke="rgba(255, 255, 255, 0.15)" stroke-width="1.5" />
                    <path d="M 34 26 L 46 18 L 46 71 L 34 79 Z" fill="url(#kLeftLarge)" filter="url(#glowLarge)" />
                    <path d="M 46 44 L 68 20 L 78 20 L 53 47 Z" fill="url(#kRightTopLarge)" />
                    <path d="M 50 44 L 75 72 L 64 72 L 46 51 Z" fill="url(#kRightBottomLarge)" />
                    <path d="M 46 44 L 56 48 L 46 52 Z" fill="#e0f2fe" opacity="0.9" />
                </svg>
            </div>
            <h1 class="brand-title">KAYAN</h1>
            <p class="brand-subtitle">INTELLIGENT ERP PLATFORM</p>
        </div>

        @if(session('error'))
        <div class="glass-alert">
            <i class="fas fa-exclamation-circle text-red-400"></i>
            <span>{{ session('error') }}</span>
        </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-6">
            @csrf

            <!-- Email -->
            <div>
                <label class="form-label" for="email">البريد الإلكتروني</label>
                <div class="input-wrapper">
                    <input
                        id="email" type="email" name="email"
                        value="{{ old('email') }}"
                        required autocomplete="email" autofocus
                        placeholder="أدخل البريد الإلكتروني"
                        class="glass-input @error('email') error-border @enderror">
                    <i class="fas fa-envelope input-icon"></i>
                </div>
                @error('email')
                    <span class="error-message"><i class="fas fa-circle-exclamation ml-1"></i>{{ $message }}</span>
                @enderror
            </div>

            <!-- Password -->
            <div>
                <label class="form-label" for="password">كلمة المرور</label>
                <div class="input-wrapper">
                    <input
                        id="password" type="password" name="password"
                        required autocomplete="current-password"
                        placeholder="أدخل كلمة المرور"
                        class="glass-input @error('password') error-border @enderror">
                    <i class="fas fa-lock input-icon"></i>
                </div>
                @error('password')
                    <span class="error-message"><i class="fas fa-circle-exclamation ml-1"></i>{{ $message }}</span>
                @enderror
            </div>

            <!-- Remember me -->
            <div class="flex items-center justify-between pt-2 pb-2">
                <label class="checkbox-container">
                    <input type="checkbox" id="remember" name="remember">
                    <span class="custom-checkbox"></span>
                    <span>تذكرني على هذا الجهاز</span>
                </label>
            </div>

            <!-- Submit -->
            <button type="submit" class="btn-submit">
                <span>تسجيل الدخول</span>
                <i class="fas fa-arrow-left-long"></i>
            </button>
        </form>
    </div>

    <!-- Footer -->
    <p class="text-center text-xs text-gray-500 mt-8 tracking-widest opacity-60">
        © {{ date('Y') }} KAYAN — All rights reserved
    </p>
</div>

</body>
</html>