<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>إدارة التحويلات</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <style>
        * { font-family: 'Cairo', sans-serif; }
        .card-shadow { box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
    </style>
</head>
<body class="bg-gray-50">
    <div class="container mx-auto px-4 py-6 max-w-7xl">
        
        <!-- ============================================ -->
        <!-- Header -->
        <!-- ============================================ -->
        <div class="mb-6">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">📦 إدارة التحويلات</h1>
                    <p class="text-gray-600 text-sm mt-1">إدارة ومتابعة تحويلات المخزون</p>
                </div>
                <a href="{{ route('transfers.create') }}" 
                   class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-bold flex items-center gap-2">
                    <i class="fas fa-plus"></i>
                    تحويل جديد
                </a>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-lg p-4 card-shadow">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm text-gray-500">الإجمالي</p>
                            <p class="text-2xl font-bold text-gray-800">{{ $transfers->total() }}</p>
                        </div>
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-exchange-alt text-blue-600"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg p-4 card-shadow">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm text-gray-500">مستلمة</p>
                            <p class="text-2xl font-bold text-green-600">
                                {{ $transfers->where('status', 'received')->count() }}
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-check-circle text-green-600"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg p-4 card-shadow">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm text-gray-500">معلقة</p>
                            <p class="text-2xl font-bold text-yellow-600">
                                {{ $transfers->whereIn('status', ['draft', 'pending'])->count() }}
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-clock text-yellow-600"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg p-4 card-shadow">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm text-gray-500">ملغية</p>
                            <p class="text-2xl font-bold text-red-600">
                                {{ $transfers->where('status', 'cancelled')->count() }}
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-times-circle text-red-600"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================ -->
        <!-- Messages -->
        <!-- ============================================ -->
        @if(session('success'))
        <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-lg">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <i class="fas fa-check-circle text-green-500 text-xl"></i>
                    <p class="text-green-800 font-bold">{{ session('success') }}</p>
                </div>
                <button onclick="this.parentElement.parentElement.remove()" class="text-green-500 hover:text-green-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        @endif

        @if(session('error'))
        <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <i class="fas fa-exclamation-circle text-red-500 text-xl"></i>
                    <p class="text-red-800 font-bold">{{ session('error') }}</p>
                </div>
                <button onclick="this.parentElement.parentElement.remove()" class="text-red-500 hover:text-red-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        @endif

        <!-- ============================================ -->
        <!-- Filters -->
        <!-- ============================================ -->
        <div class="bg-white rounded-lg card-shadow mb-6">
            <div class="bg-gray-800 text-white px-6 py-4 rounded-t-lg">
                <h3 class="font-bold flex items-center gap-2">
                    <i class="fas fa-filter"></i>
                    فلاتر البحث
                </h3>
            </div>
            
            <form method="GET" class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- From Warehouse -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">المخزن المصدر</label>
                        <select name="from_warehouse" class="w-full px-4 py-2 border-2 rounded-lg focus:border-blue-500">
                            <option value="">الكل</option>
                            @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" {{ request('from_warehouse') == $warehouse->id ? 'selected' : '' }}>
                                {{ $warehouse->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- To Warehouse -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">المخزن الوجهة</label>
                        <select name="to_warehouse" class="w-full px-4 py-2 border-2 rounded-lg focus:border-blue-500">
                            <option value="">الكل</option>
                            @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" {{ request('to_warehouse') == $warehouse->id ? 'selected' : '' }}>
                                {{ $warehouse->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">الحالة</label>
                        <select name="status" class="w-full px-4 py-2 border-2 rounded-lg focus:border-blue-500">
                            <option value="">الكل</option>
                            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>مسودة</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>معلقة</option>
                            <option value="received" {{ request('status') == 'received' ? 'selected' : '' }}>مستلمة</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>ملغية</option>
                            <option value="reversed" {{ request('status') == 'reversed' ? 'selected' : '' }}>معكوسة</option>
                        </select>
                    </div>

                    <!-- Date -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">من تاريخ</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" 
                               class="w-full px-4 py-2 border-2 rounded-lg focus:border-blue-500">
                    </div>
                </div>
                
                <div class="flex gap-3 mt-4">
                    <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-bold">
                        <i class="fas fa-search"></i> بحث
                    </button>
                    <a href="{{ route('transfers.index') }}" class="px-6 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg font-bold">
                        <i class="fas fa-redo"></i> إعادة تعيين
                    </a>
                </div>
            </form>
        </div>

        <!-- ============================================ -->
        <!-- Transfers List -->
        <!-- ============================================ -->
        <div class="space-y-4">
            @forelse($transfers as $transfer)
            <div class="bg-white rounded-lg card-shadow overflow-hidden" x-data="{ open: false }">
                
                <!-- Transfer Header -->
                <div class="p-6 border-b">
                    <div class="flex justify-between items-start">
                        <!-- Info -->
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-3">
                                <h3 class="text-xl font-bold text-gray-800">#{{ $transfer->transfer_number }}</h3>
                                
                                <!-- Status Badge -->
                                <span class="px-3 py-1 rounded-full text-sm font-bold
                                    {{ $transfer->status == 'received' ? 'bg-green-100 text-green-700' : '' }}
                                    {{ $transfer->status == 'pending' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                    {{ $transfer->status == 'draft' ? 'bg-gray-100 text-gray-700' : '' }}
                                    {{ $transfer->status == 'cancelled' ? 'bg-red-100 text-red-700' : '' }}
                                    {{ $transfer->status == 'reversed' ? 'bg-orange-100 text-orange-700' : '' }}">
                                    @if($transfer->status == 'received') ✅ مستلمة
                                    @elseif($transfer->status == 'pending') ⏳ معلقة
                                    @elseif($transfer->status == 'draft') 📝 مسودة
                                    @elseif($transfer->status == 'cancelled') ❌ ملغية
                                    @elseif($transfer->status == 'reversed') 🔄 معكوسة
                                    @endif
                                </span>

                                <!-- Items Count -->
                                <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm font-bold">
                                    <i class="fas fa-boxes"></i> {{ $transfer->items_count ?? $transfer->items->count() }} منتج
                                </span>
                            </div>
                            
                            <!-- Warehouses -->
                            <div class="flex flex-wrap items-center gap-6 text-sm">
                                <div class="flex items-center gap-2">
                                    <span class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center">
                                        <i class="fas fa-warehouse text-red-600"></i>
                                    </span>
                                    <div>
                                        <p class="text-gray-500 text-xs">من</p>
                                        <p class="font-bold text-gray-800">{{ $transfer->fromWarehouse->name }}</p>
                                    </div>
                                </div>

                                <i class="fas fa-arrow-left text-gray-400"></i>

                                <div class="flex items-center gap-2">
                                    <span class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center">
                                        <i class="fas fa-warehouse text-green-600"></i>
                                    </span>
                                    <div>
                                        <p class="text-gray-500 text-xs">إلى</p>
                                        <p class="font-bold text-gray-800">{{ $transfer->toWarehouse->name }}</p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2">
                                    <span class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                                        <i class="fas fa-calendar text-blue-600"></i>
                                    </span>
                                    <div>
                                        <p class="text-gray-500 text-xs">التاريخ</p>
                                        <p class="font-bold text-gray-800">{{ $transfer->transfer_date->format('Y-m-d') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex gap-2">
                            <button @click="open = !open" 
                                    class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-sm font-bold">
                                <i class="fas" :class="open ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                                <span x-text="open ? 'إخفاء' : 'تفاصيل'"></span>
                            </button>
                            
                            <a href="{{ route('transfers.show', $transfer->id) }}" 
                               class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-bold">
                                <i class="fas fa-eye"></i> عرض
                            </a>

                            @if($transfer->status == 'received')
                            <form action="{{ route('transfers.reverse', $transfer->id) }}" method="POST" 
                                  onsubmit="return confirm('⚠️ هل تريد عكس هذا التحويل؟')">
                                @csrf
                                <button type="submit" class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-lg text-sm font-bold">
                                    <i class="fas fa-undo"></i> عكس
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Transfer Details (Collapsible) -->
                <div x-show="open" x-transition class="border-t bg-gray-50">
                    <div class="p-6">
                        <!-- Summary Stats -->
                        <div class="grid grid-cols-3 gap-4 mb-6">
                            <div class="text-center p-4 bg-white rounded-lg border-2 border-blue-200">
                                <p class="text-sm text-gray-500 mb-1">عدد المنتجات</p>
                                <p class="text-2xl font-bold text-blue-600">{{ $transfer->items->count() }}</p>
                            </div>
                            <div class="text-center p-4 bg-white rounded-lg border-2 border-green-200">
                                <p class="text-sm text-gray-500 mb-1">إجمالي الكمية</p>
                                <p class="text-2xl font-bold text-green-600">
                                    {{ number_format($transfer->total_quantity_sent ?? $transfer->items->sum('quantity_sent'), 2) }}
                                </p>
                            </div>
                            <div class="text-center p-4 bg-white rounded-lg border-2 border-purple-200">
                                <p class="text-sm text-gray-500 mb-1">الكمية المستلمة</p>
                                <p class="text-2xl font-bold text-purple-600">
                                    {{ number_format($transfer->total_quantity_received ?? $transfer->items->sum('quantity_received'), 2) }}
                                </p>
                            </div>
                        </div>

                        <!-- Products Table -->
                        <div class="bg-white rounded-lg border overflow-hidden">
                            <table class="w-full">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="px-4 py-3 text-right text-sm font-bold text-gray-700">#</th>
                                        <th class="px-4 py-3 text-right text-sm font-bold text-gray-700">المنتج</th>
                                        <th class="px-4 py-3 text-center text-sm font-bold text-gray-700">الكمية المحولة</th>
                                        <th class="px-4 py-3 text-center text-sm font-bold text-gray-700">المخزن المصدر</th>
                                        <th class="px-4 py-3 text-center text-sm font-bold text-gray-700">المخزن الوجهة</th>
                                        <th class="px-4 py-3 text-right text-sm font-bold text-gray-700">ملاحظات</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    @foreach($transfer->items as $index => $item)
                                    @php
                                        // ✅ جلب بيانات المخزون قبل وبعد
                                        $movements = \App\Models\InventoryMovement::where('reference_id', $transfer->id)
                                            ->where('product_id', $item->product_id)
                                            ->orderBy('movement_type', 'asc')
                                            ->get();
                                        
                                        $sourceMovement = $movements->firstWhere('movement_type', 'transfer_out');
                                        $destMovement = $movements->firstWhere('movement_type', 'transfer_in');
                                    @endphp
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 text-center">
                                            <span class="inline-block w-8 h-8 rounded-full bg-blue-100 text-blue-600 font-bold flex items-center justify-center">
                                                {{ $index + 1 }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div>
                                                <p class="font-bold text-gray-800">{{ $item->product->name }}</p>
                                                <p class="text-sm text-gray-500">{{ $item->product->sku ?? $item->product->code }}</p>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="inline-block px-4 py-2 bg-blue-100 text-blue-700 rounded-lg font-bold text-lg">
                                                {{ number_format($item->quantity_sent, 2) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="text-center">
                                                @if($sourceMovement)
                                                <div class="mb-2">
                                                    <span class="text-xs text-gray-500">قبل:</span>
                                                    <span class="inline-block px-3 py-1 bg-gray-100 rounded-lg font-bold text-sm">
                                                        {{ number_format($sourceMovement->quantity_before, 2) }}
                                                    </span>
                                                </div>
                                                <div class="flex items-center justify-center gap-2">
                                                    <i class="fas fa-arrow-down text-red-500"></i>
                                                </div>
                                                <div class="mt-2">
                                                    <span class="text-xs text-gray-500">بعد:</span>
                                                    <span class="inline-block px-3 py-1 bg-red-100 text-red-700 rounded-lg font-bold text-sm">
                                                        {{ number_format($sourceMovement->quantity_after, 2) }}
                                                    </span>
                                                </div>
                                                @else
                                                <span class="text-gray-400 text-sm">لا توجد بيانات</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="text-center">
                                                @if($destMovement)
                                                <div class="mb-2">
                                                    <span class="text-xs text-gray-500">قبل:</span>
                                                    <span class="inline-block px-3 py-1 bg-gray-100 rounded-lg font-bold text-sm">
                                                        {{ number_format($destMovement->quantity_before, 2) }}
                                                    </span>
                                                </div>
                                                <div class="flex items-center justify-center gap-2">
                                                    <i class="fas fa-arrow-up text-green-500"></i>
                                                </div>
                                                <div class="mt-2">
                                                    <span class="text-xs text-gray-500">بعد:</span>
                                                    <span class="inline-block px-3 py-1 bg-green-100 text-green-700 rounded-lg font-bold text-sm">
                                                        {{ number_format($destMovement->quantity_after, 2) }}
                                                    </span>
                                                </div>
                                                @else
                                                <span class="text-gray-400 text-sm">لا توجد بيانات</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            @if($item->notes)
                                            <div class="flex items-start gap-2">
                                                <i class="fas fa-sticky-note text-gray-400 mt-1"></i>
                                                <p class="text-sm text-gray-600">{{ $item->notes }}</p>
                                            </div>
                                            @else
                                            <span class="text-gray-400 text-sm">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <!-- Empty State -->
            <div class="bg-white rounded-lg card-shadow p-12 text-center">
                <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-exchange-alt text-gray-400 text-4xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-700 mb-2">لا توجد تحويلات</h3>
                <p class="text-gray-500 mb-6">لم يتم إنشاء أي تحويلات بعد</p>
                <a href="{{ route('transfers.create') }}" 
                   class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-bold">
                    <i class="fas fa-plus"></i>
                    إنشاء أول تحويل
                </a>
            </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($transfers->hasPages())
        <div class="mt-6 bg-white rounded-lg card-shadow p-4">
            <div class="flex justify-between items-center">
                <div class="text-sm text-gray-600">
                    عرض {{ $transfers->firstItem() }} - {{ $transfers->lastItem() }} من {{ $transfers->total() }}
                </div>
                {{ $transfers->links() }}
            </div>
        </div>
        @endif
    </div>
</body>
</html>