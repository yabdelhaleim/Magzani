<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>تفاصيل التحويل #{{ $transfer->id }} - نظام المخازن</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        .animate-fade-in {
            animation: fadeIn 0.5s ease-in-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .hover-lift {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .hover-lift:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }
        
        .timeline-item {
            position: relative;
            padding-right: 2rem;
        }
        
        .timeline-item::before {
            content: '';
            position: absolute;
            right: 0;
            top: 0;
            width: 2px;
            height: 100%;
            background: linear-gradient(to bottom, #3b82f6, #8b5cf6);
        }
        
        .timeline-dot {
            position: absolute;
            right: -5px;
            top: 0;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        /* ✅ تنسيق جديد للكميات */
        .qty-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 0.75rem;
            border-radius: 0.5rem;
            font-weight: 600;
            font-size: 0.875rem;
        }
        
        .qty-increase {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            color: #065f46;
            border: 1px solid #6ee7b7;
        }
        
        .qty-decrease {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #991b1b;
            border: 1px solid #fca5a5;
        }
        
        .qty-arrow {
            font-size: 1.25rem;
            font-weight: bold;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 to-blue-50 min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-7xl">
        
        <!-- Header with Actions -->
        <div class="mb-8 animate-fade-in">
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
                <div class="flex-1">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-16 h-16 rounded-xl bg-gradient-to-r from-blue-500 to-indigo-600 flex items-center justify-center">
                            <i class="fas fa-exchange-alt text-white text-2xl"></i>
                        </div>
                        <div>
                            <h1 class="text-3xl lg:text-4xl font-bold text-gray-800">تفاصيل التحويل</h1>
                            <div class="flex flex-wrap items-center gap-4 mt-2">
                                <div class="flex items-center gap-2">
                                    <span class="text-2xl font-bold text-blue-600">#{{ $transfer->transfer_number }}</span>
                                </div>
                                <span class="text-gray-600">
                                    <i class="fas fa-calendar-alt text-blue-500 ml-2"></i>
                                    {{ $transfer->created_at->format('Y-m-d H:i') }}
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Breadcrumb -->
                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        <a href="{{ route('dashboard') }}" class="hover:text-blue-600 transition-colors">
                            <i class="fas fa-home"></i>
                        </a>
                        <i class="fas fa-chevron-left"></i>
                        <a href="{{ route('transfers.index') }}" class="hover:text-blue-600 transition-colors">
                            التحويلات
                        </a>
                        <i class="fas fa-chevron-left"></i>
                        <span class="text-blue-600 font-semibold">تفاصيل التحويل</span>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="flex flex-wrap gap-3">
                    @if($transfer->status === 'received')
                    <form action="{{ route('transfers.reverse', $transfer->id) }}" 
                          method="POST" 
                          onsubmit="return confirm('⚠️ هل أنت متأكد من عكس هذا التحويل؟\nسيتم إرجاع المنتجات إلى المخزن المصدر.\n\nهذا الإجراء لا يمكن التراجع عنه.')">
                        @csrf
                        <button type="submit" 
                                class="inline-flex items-center gap-3 px-6 py-3 bg-gradient-to-r from-orange-500 to-amber-600 hover:from-orange-600 hover:to-amber-700 text-white rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 hover-lift">
                            <i class="fas fa-undo"></i>
                            <span class="font-semibold">عكس التحويل</span>
                        </button>
                    </form>
                    @endif

                    @if(in_array($transfer->status, ['draft', 'pending', 'received']))
                    <form action="{{ route('transfers.cancel', $transfer->id) }}" 
                          method="POST"
                          onsubmit="return confirm('⚠️ هل أنت متأكد من إلغاء هذا التحويل؟\n\n{{ $transfer->status === 'received' ? 'سيتم إرجاع المنتجات للمخزن المصدر.' : '' }}\n\nهذا الإجراء لا يمكن التراجع عنه.')">
                        @csrf
                        <button type="submit" 
                                class="inline-flex items-center gap-3 px-6 py-3 bg-gradient-to-r from-red-500 to-pink-600 hover:from-red-600 hover:to-pink-700 text-white rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 hover-lift">
                            <i class="fas fa-times-circle"></i>
                            <span class="font-semibold">إلغاء التحويل</span>
                        </button>
                    </form>
                    @endif
                    
                    <a href="{{ route('transfers.index') }}" 
                       class="inline-flex items-center gap-3 px-6 py-3 bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 hover-lift">
                        <i class="fas fa-arrow-right"></i>
                        <span class="font-semibold">رجوع للقائمة</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Messages -->
        @if(session('success'))
        <div class="mb-6 bg-gradient-to-r from-green-500 to-emerald-600 rounded-xl shadow-lg overflow-hidden animate-fade-in">
            <div class="px-6 py-4 text-white flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <i class="fas fa-check-circle text-2xl"></i>
                    <div>
                        <h3 class="font-bold text-lg">تم بنجاح!</h3>
                        <p class="text-green-100">{{ session('success') }}</p>
                    </div>
                </div>
                <button onclick="this.parentElement.parentElement.remove()" class="text-white hover:text-green-100">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        @endif

        @if(session('error'))
        <div class="mb-6 bg-gradient-to-r from-red-500 to-pink-600 rounded-xl shadow-lg overflow-hidden animate-fade-in">
            <div class="px-6 py-4 text-white flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <i class="fas fa-exclamation-circle text-2xl"></i>
                    <div>
                        <h3 class="font-bold text-lg">حدث خطأ!</h3>
                        <p class="text-red-100">{{ session('error') }}</p>
                    </div>
                </div>
                <button onclick="this.parentElement.parentElement.remove()" class="text-white hover:text-red-100">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        @endif

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column: Transfer Details -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Transfer Information Card -->
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden animate-fade-in">
                    <div class="bg-gradient-to-r from-blue-500 to-indigo-600 px-6 py-4">
                        <h2 class="text-xl font-bold text-white flex items-center gap-3">
                            <i class="fas fa-info-circle"></i>
                            معلومات التحويل الأساسية
                        </h2>
                    </div>
                    
                    <div class="p-6">
                        <!-- Warehouse Transfer Flow -->
                        <div class="mb-8">
                            <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                                <!-- From Warehouse -->
                                <div class="text-center">
                                    <div class="w-20 h-20 rounded-full bg-gradient-to-r from-red-100 to-red-50 border-4 border-white shadow-lg flex items-center justify-center mb-4 mx-auto">
                                        <i class="fas fa-warehouse text-red-500 text-3xl"></i>
                                    </div>
                                    <h4 class="text-sm text-gray-500 mb-2">المخزن المصدر</h4>
                                    <p class="text-lg font-bold text-gray-800">{{ $transfer->fromWarehouse->name }}</p>
                                    <div class="mt-2 text-sm text-gray-600">
                                        <i class="fas fa-map-marker-alt ml-1"></i>
                                        {{ $transfer->fromWarehouse->location ?? 'غير محدد' }}
                                    </div>
                                </div>
                                
                                <!-- Transfer Arrow -->
                                <div class="hidden md:block">
                                    <div class="w-16 h-2 bg-gradient-to-r from-red-400 to-green-400 rounded-full"></div>
                                    <div class="flex justify-center mt-2">
                                        <i class="fas fa-exchange-alt text-blue-500 text-2xl"></i>
                                    </div>
                                </div>
                                
                                <!-- To Warehouse -->
                                <div class="text-center">
                                    <div class="w-20 h-20 rounded-full bg-gradient-to-r from-green-100 to-green-50 border-4 border-white shadow-lg flex items-center justify-center mb-4 mx-auto">
                                        <i class="fas fa-warehouse text-green-500 text-3xl"></i>
                                    </div>
                                    <h4 class="text-sm text-gray-500 mb-2">المخزن الوجهة</h4>
                                    <p class="text-lg font-bold text-gray-800">{{ $transfer->toWarehouse->name }}</p>
                                    <div class="mt-2 text-sm text-gray-600">
                                        <i class="fas fa-map-marker-alt ml-1"></i>
                                        {{ $transfer->toWarehouse->location ?? 'غير محدد' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Details Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                                    <i class="fas fa-calendar-alt text-blue-500"></i>
                                    <span>تاريخ التحويل</span>
                                </label>
                                <div class="flex items-center gap-3 px-4 py-3 bg-blue-50 rounded-xl">
                                    <i class="fas fa-calendar text-blue-500"></i>
                                    <span class="font-semibold text-gray-800">{{ $transfer->transfer_date->format('Y-m-d') }}</span>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                                    <i class="fas fa-clock text-purple-500"></i>
                                    <span>وقت الإنشاء</span>
                                </label>
                                <div class="flex items-center gap-3 px-4 py-3 bg-purple-50 rounded-xl">
                                    <i class="fas fa-clock text-purple-500"></i>
                                    <span class="font-semibold text-gray-800">{{ $transfer->created_at->format('H:i') }}</span>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                                    <i class="fas fa-user text-green-500"></i>
                                    <span>المنشئ</span>
                                </label>
                                <div class="flex items-center gap-3 px-4 py-3 bg-green-50 rounded-xl">
                                    <i class="fas fa-user text-green-500"></i>
                                    <span class="font-semibold text-gray-800">{{ $transfer->createdBy->name ?? 'غير معروف' }}</span>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                                    <i class="fas fa-tag text-orange-500"></i>
                                    <span>الحالة</span>
                                </label>
                                <div class="px-4 py-3 rounded-xl font-bold text-center
                                    {{ $transfer->status == 'received' ? 'bg-gradient-to-r from-green-100 to-green-50 text-green-700 border border-green-200' : 
                                       ($transfer->status == 'pending' ? 'bg-gradient-to-r from-yellow-100 to-yellow-50 text-yellow-700 border border-yellow-200' : 
                                       ($transfer->status == 'reversed' ? 'bg-gradient-to-r from-orange-100 to-orange-50 text-orange-700 border border-orange-200' : 
                                       'bg-gradient-to-r from-red-100 to-red-50 text-red-700 border border-red-200')) }}">
                                    <div class="flex items-center justify-center gap-2">
                                        <i class="fas 
                                            {{ $transfer->status == 'received' ? 'fa-check-circle' : 
                                               ($transfer->status == 'pending' ? 'fa-clock' : 
                                               ($transfer->status == 'reversed' ? 'fa-undo' : 'fa-times-circle')) }}"></i>
                                        <span>
                                            {{ $transfer->status == 'received' ? 'مستلم' : 
                                               ($transfer->status == 'pending' ? 'معلق' : 
                                               ($transfer->status == 'reversed' ? 'معكوس' : 'ملغي')) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Notes -->
                        @if($transfer->notes)
                        <div class="mt-6">
                            <label class="block text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                                <i class="fas fa-sticky-note text-yellow-500"></i>
                                <span>ملاحظات إضافية</span>
                            </label>
                            <div class="px-4 py-3 bg-yellow-50 border border-yellow-200 rounded-xl">
                                <p class="text-gray-700">{{ $transfer->notes }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- ✅ Products Card - مع تفاصيل الكميات -->
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden animate-fade-in" style="animation-delay: 0.1s;">
                    <div class="bg-gradient-to-r from-green-500 to-emerald-600 px-6 py-4">
                        <div class="flex items-center justify-between">
                            <h2 class="text-xl font-bold text-white flex items-center gap-3">
                                <i class="fas fa-boxes"></i>
                                المنتجات المحولة وتأثيرها على المخزون
                            </h2>
                            <span class="px-4 py-2 bg-white text-emerald-600 rounded-full font-bold">
                                {{ $transfer->items->count() }} منتجات
                            </span>
                        </div>
                    </div>
                    
                    <div class="p-6">
                        <!-- Products Details -->
                        <div class="space-y-6">
                            @foreach($transfer->items as $index => $item)
                            <div class="bg-gradient-to-r from-gray-50 to-white rounded-xl border-2 border-gray-200 overflow-hidden hover:shadow-lg transition-shadow">
                                <!-- Product Header -->
                                <div class="bg-gradient-to-r from-blue-500 to-indigo-500 px-6 py-3 flex items-center justify-between">
                                    <div class="flex items-center gap-4">
                                        <span class="w-10 h-10 bg-white text-blue-600 rounded-full flex items-center justify-center font-bold text-lg">
                                            {{ $index + 1 }}
                                        </span>
                                        <div>
                                            <h3 class="text-lg font-bold text-white">{{ $item->product->name }}</h3>
                                            <div class="flex items-center gap-3 mt-1">
                                                <span class="bg-white/20 px-3 py-1 rounded-full text-xs text-white">
                                                    {{ $item->product->sku }}
                                                </span>
                                                <span class="text-white/80 text-sm">
                                                    {{ $item->product->unit }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-left">
                                        <p class="text-white/80 text-sm">الكمية المحولة</p>
                                        <p class="text-3xl font-bold text-white">{{ number_format($item->quantity, 2) }}</p>
                                    </div>
                                </div>

                                <!-- Stock Changes -->
                                <div class="p-6">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <!-- From Warehouse Stock -->
                                        <div class="bg-red-50 rounded-xl p-6 border-2 border-red-200">
                                            <div class="flex items-center gap-3 mb-4">
                                                <div class="w-12 h-12 bg-gradient-to-r from-red-500 to-red-600 rounded-full flex items-center justify-center">
                                                    <i class="fas fa-warehouse text-white text-lg"></i>
                                                </div>
                                                <div>
                                                    <h4 class="font-bold text-gray-800">{{ $transfer->fromWarehouse->name }}</h4>
                                                    <p class="text-xs text-gray-600">المخزن المصدر</p>
                                                </div>
                                            </div>
                                            
                                            <div class="space-y-3">
                                                <div class="flex items-center justify-between">
                                                    <span class="text-sm text-gray-600">الكمية قبل التحويل:</span>
                                                    <span class="text-lg font-bold text-gray-800">{{ number_format($item->before_from_qty ?? 0, 2) }}</span>
                                                </div>
                                                
                                                <div class="flex items-center justify-center">
                                                    <div class="qty-badge qty-decrease">
                                                        <i class="fas fa-arrow-down qty-arrow"></i>
                                                        <span>{{ number_format($item->quantity, 2) }}</span>
                                                    </div>
                                                </div>
                                                
                                                <div class="flex items-center justify-between pt-3 border-t-2 border-red-300">
                                                    <span class="text-sm font-semibold text-gray-700">الكمية بعد التحويل:</span>
                                                    <span class="text-2xl font-bold text-red-600">{{ number_format($item->after_from_qty ?? 0, 2) }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- To Warehouse Stock -->
                                        <div class="bg-green-50 rounded-xl p-6 border-2 border-green-200">
                                            <div class="flex items-center gap-3 mb-4">
                                                <div class="w-12 h-12 bg-gradient-to-r from-green-500 to-green-600 rounded-full flex items-center justify-center">
                                                    <i class="fas fa-warehouse text-white text-lg"></i>
                                                </div>
                                                <div>
                                                    <h4 class="font-bold text-gray-800">{{ $transfer->toWarehouse->name }}</h4>
                                                    <p class="text-xs text-gray-600">المخزن الوجهة</p>
                                                </div>
                                            </div>
                                            
                                            <div class="space-y-3">
                                                <div class="flex items-center justify-between">
                                                    <span class="text-sm text-gray-600">الكمية قبل التحويل:</span>
                                                    <span class="text-lg font-bold text-gray-800">{{ number_format($item->before_to_qty ?? 0, 2) }}</span>
                                                </div>
                                                
                                                <div class="flex items-center justify-center">
                                                    <div class="qty-badge qty-increase">
                                                        <i class="fas fa-arrow-up qty-arrow"></i>
                                                        <span>{{ number_format($item->quantity, 2) }}</span>
                                                    </div>
                                                </div>
                                                
                                                <div class="flex items-center justify-between pt-3 border-t-2 border-green-300">
                                                    <span class="text-sm font-semibold text-gray-700">الكمية بعد التحويل:</span>
                                                    <span class="text-2xl font-bold text-green-600">{{ number_format($item->after_to_qty ?? 0, 2) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Product Notes & Price -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
                                        <div class="bg-purple-50 rounded-lg p-4 border border-purple-200">
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <p class="text-xs text-gray-600 mb-1">سعر الوحدة</p>
                                                    <p class="text-xl font-bold text-purple-600">{{ number_format($item->product->price, 2) }} د.ع</p>
                                                </div>
                                                <div class="text-left">
                                                    <p class="text-xs text-gray-600 mb-1">القيمة الإجمالية</p>
                                                    <p class="text-xl font-bold text-purple-600">{{ number_format($item->quantity * $item->product->price, 2) }} د.ع</p>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        @if($item->notes)
                                        <div class="bg-yellow-50 rounded-lg p-4 border border-yellow-200">
                                            <p class="text-xs text-gray-600 mb-2 flex items-center gap-2">
                                                <i class="fas fa-sticky-note text-yellow-500"></i>
                                                <span>ملاحظات</span>
                                            </p>
                                            <p class="text-sm text-gray-700">{{ $item->notes }}</p>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <!-- Products Summary -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
                            <div class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-xl p-6 text-center border border-blue-200">
                                <p class="text-sm text-gray-600 mb-2">عدد المنتجات</p>
                                <p class="text-4xl font-bold text-blue-600">{{ $transfer->items->count() }}</p>
                            </div>
                            
                            <div class="bg-gradient-to-r from-green-50 to-green-100 rounded-xl p-6 text-center border border-green-200">
                                <p class="text-sm text-gray-600 mb-2">إجمالي الكميات</p>
                                <p class="text-4xl font-bold text-green-600">
                                    {{ number_format($transfer->items->sum('quantity'), 2) }}
                                </p>
                            </div>
                            
                            <div class="bg-gradient-to-r from-purple-50 to-purple-100 rounded-xl p-6 text-center border border-purple-200">
                                <p class="text-sm text-gray-600 mb-2">القيمة الإجمالية</p>
                                <p class="text-4xl font-bold text-purple-600">
                                    {{ number_format($transfer->items->sum(function($item) { 
                                        return $item->quantity * $item->product->price; 
                                    }), 2) }} د.ع
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Status & Timeline -->
            <div class="space-y-8">
                <!-- Status Summary Card -->
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden animate-fade-in" style="animation-delay: 0.2s;">
                    <div class="bg-gradient-to-r from-purple-500 to-indigo-600 px-6 py-4">
                        <h2 class="text-xl font-bold text-white flex items-center gap-3">
                            <i class="fas fa-chart-line"></i>
                            ملخص الحالة
                        </h2>
                    </div>
                    
                    <div class="p-6">
                        <div class="space-y-6">
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm text-gray-600">مستوى الإكمال</span>
                                    <span class="font-bold text-gray-800">
                                        {{ $transfer->status == 'received' ? '100%' : 
                                           ($transfer->status == 'pending' ? '50%' : 
                                           ($transfer->status == 'reversed' ? '75%' : '0%')) }}
                                    </span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-3">
                                    <div class="h-3 rounded-full 
                                        {{ $transfer->status == 'received' ? 'bg-gradient-to-r from-green-500 to-emerald-500 w-full' : 
                                           ($transfer->status == 'pending' ? 'bg-gradient-to-r from-yellow-500 to-amber-500 w-1/2' : 
                                           ($transfer->status == 'reversed' ? 'bg-gradient-to-r from-orange-500 to-amber-500 w-3/4' : 'bg-gradient-to-r from-red-500 to-pink-500 w-0')) }}">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div class="text-center p-4 bg-gray-50 rounded-xl">
                                    <p class="text-sm text-gray-500 mb-2">تاريخ الإنشاء</p>
                                    <p class="font-bold text-gray-800">{{ $transfer->created_at->format('Y-m-d') }}</p>
                                </div>
                                
                                <div class="text-center p-4 bg-gray-50 rounded-xl">
                                    <p class="text-sm text-gray-500 mb-2">تاريخ التحديث</p>
                                    <p class="font-bold text-gray-800">{{ $transfer->updated_at->format('Y-m-d') }}</p>
                                </div>
                            </div>
                            
                            <div class="p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl border border-blue-200">
                                <h4 class="font-semibold text-gray-800 mb-3">إحصائيات سريعة</h4>
                                <div class="space-y-3">
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">متوسط الكمية</span>
                                        <span class="font-bold text-gray-800">
                                            {{ number_format($transfer->items->avg('quantity'), 2) }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">أعلى كمية</span>
                                        <span class="font-bold text-red-600">
                                            {{ number_format($transfer->items->max('quantity'), 2) }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">أدنى كمية</span>
                                        <span class="font-bold text-green-600">
                                            {{ number_format($transfer->items->min('quantity'), 2) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Timeline Card -->
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden animate-fade-in" style="animation-delay: 0.3s;">
                    <div class="bg-gradient-to-r from-gray-800 to-gray-900 px-6 py-4">
                        <h2 class="text-xl font-bold text-white flex items-center gap-3">
                            <i class="fas fa-history"></i>
                            سجل التحويل
                        </h2>
                    </div>
                    
                    <div class="p-6">
                        <div class="space-y-6 timeline-item">
                            <div class="timeline-dot"></div>
                            
                            <!-- Created -->
                            <div>
                                <div class="flex items-center gap-3 mb-2">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-r from-blue-500 to-indigo-500 flex items-center justify-center">
                                        <i class="fas fa-plus text-white"></i>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-800">تم الإنشاء</p>
                                        <p class="text-xs text-gray-500">{{ $transfer->created_at->format('Y-m-d H:i') }}</p>
                                    </div>
                                </div>
                                <p class="text-sm text-gray-600 mr-14">تم إنشاء التحويل بواسطة {{ $transfer->createdBy->name ?? 'المسؤول' }}</p>
                            </div>
                            
                            <!-- Status Changes -->
                            @if($transfer->status == 'received')
                            <div>
                                <div class="flex items-center gap-3 mb-2">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-r from-green-500 to-emerald-500 flex items-center justify-center">
                                        <i class="fas fa-check text-white"></i>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-800">تم الاستلام</p>
                                        <p class="text-xs text-gray-500">{{ $transfer->updated_at->format('Y-m-d H:i') }}</p>
                                    </div>
                                </div>
                                <p class="text-sm text-gray-600 mr-14">تم تسليم المنتجات إلى المخزن الوجهة</p>
                            </div>
                            @endif
                            
                            @if($transfer->status == 'reversed')
                            <div>
                                <div class="flex items-center gap-3 mb-2">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-r from-orange-500 to-amber-500 flex items-center justify-center">
                                        <i class="fas fa-undo text-white"></i>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-800">تم العكس</p>
                                        <p class="text-xs text-gray-500">{{ $transfer->reversed_at ? $transfer->reversed_at->format('Y-m-d H:i') : $transfer->updated_at->format('Y-m-d H:i') }}</p>
                                    </div>
                                </div>
                                <p class="text-sm text-gray-600 mr-14">تم إرجاع المنتجات إلى المخزن المصدر بواسطة {{ $transfer->reversedBy->name ?? 'المسؤول' }}</p>
                            </div>
                            @endif
                            
                            @if($transfer->status == 'cancelled')
                            <div>
                                <div class="flex items-center gap-3 mb-2">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-r from-red-500 to-pink-500 flex items-center justify-center">
                                        <i class="fas fa-times text-white"></i>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-800">تم الإلغاء</p>
                                        <p class="text-xs text-gray-500">{{ $transfer->cancelled_at ? $transfer->cancelled_at->format('Y-m-d H:i') : $transfer->updated_at->format('Y-m-d H:i') }}</p>
                                    </div>
                                </div>
                                <p class="text-sm text-gray-600 mr-14">تم إلغاء التحويل بواسطة {{ $transfer->cancelledBy->name ?? 'المسؤول' }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden animate-fade-in" style="animation-delay: 0.4s;">
                    <div class="bg-gradient-to-r from-indigo-500 to-blue-600 px-6 py-4">
                        <h2 class="text-xl font-bold text-white flex items-center gap-3">
                            <i class="fas fa-bolt"></i>
                            إجراءات سريعة
                        </h2>
                    </div>
                    
                    <div class="p-6">
                        <div class="space-y-4">
                            <a href="{{ route('transfers.index') }}" 
                               class="flex items-center gap-3 px-4 py-3 bg-gradient-to-r from-gray-100 to-gray-50 hover:from-gray-200 hover:to-gray-100 text-gray-800 rounded-xl transition-all duration-300 hover-lift">
                                <div class="w-10 h-10 rounded-lg bg-gradient-to-r from-blue-500 to-indigo-500 flex items-center justify-center">
                                    <i class="fas fa-list text-white"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="font-semibold">كل التحويلات</p>
                                    <p class="text-xs text-gray-600">عرض جميع التحويلات</p>
                                </div>
                                <i class="fas fa-arrow-left text-gray-400"></i>
                            </a>
                            
                            <button onclick="window.print()" 
                                    class="w-full flex items-center gap-3 px-4 py-3 bg-gradient-to-r from-green-100 to-green-50 hover:from-green-200 hover:to-green-100 text-green-800 rounded-xl transition-all duration-300 hover-lift">
                                <div class="w-10 h-10 rounded-lg bg-gradient-to-r from-green-500 to-emerald-500 flex items-center justify-center">
                                    <i class="fas fa-print text-white"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="font-semibold">طباعة التقرير</p>
                                    <p class="text-xs text-green-600">طباعة تفاصيل التحويل</p>
                                </div>
                                <i class="fas fa-arrow-left text-green-400"></i>
                            </button>
                            
                            @if($transfer->status == 'received')
                            <form action="{{ route('transfers.reverse', $transfer->id) }}" 
                                  method="POST" 
                                  onsubmit="return confirm('⚠️ هل أنت متأكد من عكس هذا التحويل؟')">
                                @csrf
                                <button type="submit" 
                                        class="w-full flex items-center gap-3 px-4 py-3 bg-gradient-to-r from-orange-100 to-orange-50 hover:from-orange-200 hover:to-orange-100 text-orange-800 rounded-xl transition-all duration-300 hover-lift">
                                    <div class="w-10 h-10 rounded-lg bg-gradient-to-r from-orange-500 to-amber-500 flex items-center justify-center">
                                        <i class="fas fa-undo text-white"></i>
                                    </div>
                                    <div class="flex-1">
                                        <p class="font-semibold">عكس التحويل</p>
                                        <p class="text-xs text-orange-600">إرجاع المنتجات للمخزن المصدر</p>
                                    </div>
                                    <i class="fas fa-arrow-left text-orange-400"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alpine.js -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Print Styles -->
    <style media="print">
        @media print {
            body * {
                visibility: hidden;
            }
            .bg-white, .bg-white * {
                visibility: visible;
            }
            .bg-white {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                box-shadow: none !important;
                border: 1px solid #ddd !important;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</body>
</html>