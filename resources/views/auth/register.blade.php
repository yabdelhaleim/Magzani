<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>إنشاء حساب - نظام المخازن</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Google Fonts - Cairo -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    
    <style>
        * {
            font-family: 'Cairo', sans-serif;
        }
        
        .register-bg {
            background: linear-gradient(135deg, #1e3a5f 0%, #0f172a 100%);
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }
        
        .input-focus:focus {
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.3);
        }
    </style>
</head>
<body class="register-bg min-h-screen flex items-center justify-center py-8">
    
    <div class="w-full max-w-md px-4">
        <!-- Logo & Title -->
        <div class="text-center mb-8">
            <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl flex items-center justify-center shadow-2xl mx-auto mb-4">
                <i class="fas fa-warehouse text-4xl text-white"></i>
            </div>
            <h1 class="text-3xl font-bold text-white">نظام المخازن</h1>
            <p class="text-gray-400 mt-2">إنشاء حساب جديد</p>
        </div>

        <!-- Register Form -->
        <div class="glass-card rounded-2xl shadow-2xl p-8">
            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <!-- Name -->
                <div class="mb-4">
                    <label for="name" class="block text-gray-700 text-sm font-bold mb-2">
                        <i class="fas fa-user ml-2 text-gray-400"></i>
                        الاسم الكامل
                    </label>
                    <input 
                        id="name" 
                        type="text" 
                        name="name" 
                        value="{{ old('name') }}"
                        required 
                        autocomplete="name"
                        autofocus
                        class="input-focus w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-indigo-500 focus:outline-none transition-all @error('name') border-red-500 @else @enderror"
                        placeholder="أدخل الاسم الكامل"
                    >
                    @error('name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div class="mb-4">
                    <label for="email" class="block text-gray-700 text-sm font-bold mb-2">
                        <i class="fas fa-envelope ml-2 text-gray-400"></i>
                        البريد الإلكتروني
                    </label>
                    <input 
                        id="email" 
                        type="email" 
                        name="email" 
                        value="{{ old('email') }}"
                        required 
                        autocomplete="email"
                        class="input-focus w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-indigo-500 focus:outline-none transition-all @error('email') border-red-500 @else @enderror"
                        placeholder="أدخل البريد الإلكتروني"
                    >
                    @error('email')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Phone -->
                <div class="mb-4">
                    <label for="phone" class="block text-gray-700 text-sm font-bold mb-2">
                        <i class="fas fa-phone ml-2 text-gray-400"></i>
                        رقم الهاتف (اختياري)
                    </label>
                    <input 
                        id="phone" 
                        type="text" 
                        name="phone" 
                        value="{{ old('phone') }}"
                        autocomplete="tel"
                        class="input-focus w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-indigo-500 focus:outline-none transition-all @error('phone') border-red-500 @else @enderror"
                        placeholder="أدخل رقم الهاتف"
                    >
                    @error('phone')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div class="mb-4">
                    <label for="password" class="block text-gray-700 text-sm font-bold mb-2">
                        <i class="fas fa-lock ml-2 text-gray-400"></i>
                        كلمة المرور
                    </label>
                    <input 
                        id="password" 
                        type="password" 
                        name="password" 
                        required 
                        autocomplete="new-password"
                        class="input-focus w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-indigo-500 focus:outline-none transition-all @error('password') border-red-500 @else @enderror"
                        placeholder="أدخل كلمة المرور (6 أحرف على الأقل)"
                    >
                    @error('password')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Confirm Password -->
                <div class="mb-6">
                    <label for="password_confirmation" class="block text-gray-700 text-sm font-bold mb-2">
                        <i class="fas fa-lock ml-2 text-gray-400"></i>
                        تأكيد كلمة المرور
                    </label>
                    <input 
                        id="password_confirmation" 
                        type="password" 
                        name="password_confirmation" 
                        required 
                        autocomplete="new-password"
                        class="input-focus w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-indigo-500 focus:outline-none transition-all"
                        placeholder="أعد إدخال كلمة المرور"
                    >
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full bg-gradient-to-r from-blue-500 to-purple-600 text-white font-bold py-3 px-4 rounded-lg hover:from-blue-600 hover:to-purple-700 transition-all transform hover:scale-[1.02] shadow-lg">
                    <i class="fas fa-user-plus ml-2"></i>
                    إنشاء حساب
                </button>
            </form>

            <!-- Login Link -->
            <div class="mt-6 text-center">
                <p class="text-gray-600">
                    لديك حساب بالفعل؟
                    <a href="{{ route('login') }}" class="text-indigo-600 hover:text-indigo-800 font-semibold">
                        تسجيل الدخول
                    </a>
                </p>
            </div>
        </div>
    </div>

</body>
</html>
