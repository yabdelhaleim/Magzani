@extends('layouts.app')

@section('title', 'الموردين')
@section('page-title', 'إدارة الموردين')

@section('content')
<div class="space-y-6">
    
    <!-- Header Actions -->
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">قائمة الموردين</h2>
            <p class="text-gray-600 mt-1">عرض وإدارة جميع الموردين في النظام</p>
        </div>
        <a href="{{ route('suppliers.create') }}" 
           class="flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all shadow-lg hover:shadow-xl">
            <i class="fas fa-plus-circle"></i>
            <span>إضافة مورد جديد</span>
        </a>
    </div>

    <!-- Statistics Cards -->
    @if(isset($statistics))
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Suppliers -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">إجمالي الموردين</p>
                    <h3 class="text-3xl font-bold text-gray-800">{{ $statistics['total_suppliers'] ?? 0 }}</h3>
                </div>
                <div class="w-14 h-14 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-users text-2xl text-blue-600"></i>
                </div>
            </div>
        </div>

        <!-- Active Suppliers -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">الموردين النشطين</p>
                    <h3 class="text-3xl font-bold text-green-600">{{ $statistics['active_suppliers'] ?? 0 }}</h3>
                </div>
                <div class="w-14 h-14 bg-green-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-check-circle text-2xl text-green-600"></i>
                </div>
            </div>
        </div>

        <!-- Total Purchases -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">إجمالي المشتريات</p>
                    <h3 class="text-2xl font-bold text-purple-600">{{ number_format($statistics['total_purchases'] ?? 0, 2) }}</h3>
                    <p class="text-xs text-gray-500 mt-1">جنيه مصري</p>
                </div>
                <div class="w-14 h-14 bg-purple-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-shopping-cart text-2xl text-purple-600"></i>
                </div>
            </div>
        </div>

        <!-- Total Balance -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">إجمالي المستحقات</p>
                    <h3 class="text-2xl font-bold text-red-600">{{ number_format($statistics['total_balance'] ?? 0, 2) }}</h3>
                    <p class="text-xs text-gray-500 mt-1">جنيه مصري</p>
                </div>
                <div class="w-14 h-14 bg-red-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-wallet text-2xl text-red-600"></i>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
        <form method="GET" action="{{ route('suppliers.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">البحث</label>
                <input type="text" 
                       name="search" 
                       placeholder="ابحث بالاسم أو الهاتف" 
                       value="{{ request('search') }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">الحالة</label>
                <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">الكل</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>نشط</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>غير نشط</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">الترتيب</label>
                <select name="sort" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>الاسم</option>
                    <option value="created_at" {{ request('sort') == 'created_at' ? 'selected' : '' }}>تاريخ الإضافة</option>
                    <option value="balance" {{ request('sort') == 'balance' ? 'selected' : '' }}>الرصيد</option>
                </select>
            </div>
            
            <div class="flex items-end">
                <button type="submit" class="w-full px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-search ml-2"></i>
                    بحث
                </button>
            </div>
        </form>
    </div>

    <!-- Suppliers Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">#</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">المورد</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">معلومات الاتصال</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">إجمالي المشتريات</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">الرصيد المستحق</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">الحالة</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($suppliers as $supplier)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            #{{ $supplier->id }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white font-semibold">
                                    {{ substr($supplier->name, 0, 2) }}
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-800">{{ $supplier->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $supplier->created_at->format('Y-m-d') }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2 text-sm text-gray-600">
                                    <i class="fas fa-phone text-gray-400"></i>
                                    <span>{{ $supplier->phone ?? 'غير متوفر' }}</span>
                                </div>
                                @if($supplier->email)
                                <div class="flex items-center gap-2 text-sm text-gray-600">
                                    <i class="fas fa-envelope text-gray-400"></i>
                                    <span>{{ $supplier->email }}</span>
                                </div>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <p class="text-lg font-bold text-purple-600">{{ number_format($supplier->purchaseInvoices()->sum('total'), 2) }}</p>
                            <p class="text-xs text-gray-500">ج.م</p>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <p class="text-lg font-bold {{ ($supplier->balance ?? 0) > 0 ? 'text-red-600' : 'text-green-600' }}">
                                {{ number_format($supplier->balance ?? 0, 2) }}
                            </p>
                            <p class="text-xs text-gray-500">ج.م</p>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($supplier->is_active ?? true)
                                <span class="inline-flex items-center gap-1 px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold">
                                    <i class="fas fa-check-circle"></i>
                                    نشط
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-xs font-semibold">
                                    <i class="fas fa-pause-circle"></i>
                                    غير نشط
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('suppliers.show', $supplier->id) }}" 
                                   class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                                   title="عرض التفاصيل">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('suppliers.edit', $supplier->id) }}" 
                                   class="p-2 text-yellow-600 hover:bg-yellow-50 rounded-lg transition-colors"
                                   title="تعديل">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="{{ route('suppliers.statement', $supplier->id) }}" 
                                   class="p-2 text-purple-600 hover:bg-purple-50 rounded-lg transition-colors"
                                   title="كشف الحساب">
                                    <i class="fas fa-file-invoice"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
                                <p class="text-gray-500 text-lg mb-4">لا توجد موردين حالياً</p>
                                <a href="{{ route('suppliers.create') }}" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                    <i class="fas fa-plus ml-2"></i>
                                    إضافة أول مورد
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($suppliers->hasPages())
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-600">
                    عرض {{ $suppliers->firstItem() }} إلى {{ $suppliers->lastItem() }} من أصل {{ $suppliers->total() }} مورد
                </div>
                <div>
                    {{ $suppliers->links() }}
                </div>
            </div>
        </div>
        @endif
    </div>

</div>
@endsection