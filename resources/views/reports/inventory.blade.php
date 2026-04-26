@extends('layouts.app')

@section('title', 'تقرير المخزون')
@section('page-title', 'تقرير المخزون')

@push('styles')
<style>
    .tf-page { background: #f4f7fe; min-height: 100vh; padding: 26px 22px; }
    .tf-card { background: white; border-radius: 20px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); margin-bottom: 20px; }
    .tf-card-head { padding: 20px; background: linear-gradient(135deg, #0ea5e9, #06b6d4); color: white; border-radius: 20px 20px 0 0; }
    .tf-btn { padding: 10px 20px; border-radius: 10px; font-weight: 600; border: none; cursor: pointer; }
    .tf-btn-primary { background: linear-gradient(135deg, #0ea5e9, #06b6d4); color: white; }
    .tf-table { width: 100%; border-collapse: collapse; }
    .tf-table th { padding: 12px; text-align: right; background: #f8faff; font-weight: 600; color: #64748b; }
    .tf-table td { padding: 12px; border-top: 1px solid #e4eaf7; }
    .tf-stat { padding: 20px; text-align: center; }
    .tf-stat-value { font-size: 2rem; font-weight: 800; }
    .tf-input, .tf-select { width: 100%; padding: 10px; border: 1px solid #e4eaf7; border-radius: 10px; }

    /* طباعة احترافية */
    @media print {
        @page { size: A4 landscape; margin: 8mm; }
        * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        body { background: white !important; font-size: 10pt !important; }
        .tf-page { background: white !important; padding: 0 !important; }
        .no-print { display: none !important; }
        .tf-card { box-shadow: none !important; border: 1px solid #000 !important; border-radius: 0 !important; page-break-inside: avoid; }
        .tf-card-head { background: #0369a1 !important; padding: 10px !important; border-radius: 0 !important; }
        .tf-stat { border: 1px solid #000 !important; padding: 10px !important; }
        .tf-header-gradient { background: #0369a1 !important; border-radius: 0 !important; padding: 15px !important; }

        .print-header { display: block !important; page-break-after: avoid; border-bottom: 3px solid #0369a1 !important; padding-bottom: 15px !important; }
        .print-footer { display: block !important; position: fixed; bottom: 0; left: 0; right: 0; text-align: center; font-size: 8pt; border-top: 1px solid #ddd; padding: 8px; }
        .print-logo { max-height: 60px !important; }
        .print-company-name { font-size: 20pt !important; font-weight: 900 !important; color: #0369a1 !important; }
        .print-title { font-size: 16pt !important; font-weight: 800 !important; text-align: center; text-decoration: underline; }

        th { background: #0369a1 !important; color: white !important; border: 1px solid #000 !important; padding: 6px !important; font-size: 9pt !important; }
        td { border: 1px solid #000 !important; padding: 6px !important; font-size: 9pt !important; }
        tbody tr:nth-child(even) { background: #f8fafc !important; }
    }

    .print-header, .print-footer { display: none; }
</style>
@endpush

@section('content')
@php
    $company = \App\Models\Company::first();
@endphp

<div class="tf-page">
    {{-- Print Header --}}
    <div class="print-header">
        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
            <div style="text-align: right; flex: 1;">
                @if($company && $company->logo)
                    <img src="{{ asset('storage/' . $company->logo) }}" class="print-logo" alt="شعار الشركة">
                @endif
            </div>
            <div style="text-align: center; flex: 2;">
                <h1 class="print-company-name">{{ $company->name ?? 'شركة ماجزاني' }}</h1>
                @if($company->address ?? null)
                    <p style="font-size: 9pt; margin: 3px 0;"><i class="fas fa-map-marker-alt"></i> {{ $company->address }}</p>
                @endif
                @if($company->phone ?? null)
                    <p style="font-size: 9pt; margin: 3px 0;"><i class="fas fa-phone"></i> {{ $company->phone }}</p>
                @endif
            </div>
            <div style="text-align: left; flex: 1;">
                <p style="font-size: 9pt; margin: 2px 0;"><strong>التاريخ:</strong> {{ \Carbon\Carbon::now()->format('Y-m-d') }}</p>
                <p style="font-size: 9pt; margin: 2px 0;"><strong>الساعة:</strong> {{ \Carbon\Carbon::now()->format('H:i') }}</p>
            </div>
        </div>
        <h2 class="print-title"><i class="fas fa-boxes"></i> تقرير المخزون الشامل</h2>
    </div>

    <!-- Header -->
    <div class="tf-header-gradient tf-section mb-6 no-print">
        <h2 class="text-4xl font-bold mb-2">تقرير المخزون الشامل</h2>
        <p class="text-white/80">حالة المخزون الحالية لجميع المنتجات</p>
    </div>

    <!-- Filters -->
    <div class="tf-card p-5 no-print">
        <form method="GET" action="{{ route('reports.inventory') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-semibold mb-2">المخزن</label>
                <select name="warehouse_id" class="tf-select">
                    <option value="">كل المخازن</option>
                    @foreach($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}" {{ request('warehouse_id') == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-2">الفئة</label>
                <select name="category_id" class="tf-select">
                    <option value="">كل الفئات</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-2">الحالة</label>
                <select name="status" class="tf-select">
                    <option value="">الكل</option>
                    <option value="in_stock" {{ request('status') == 'in_stock' ? 'selected' : '' }}>متوفر</option>
                    <option value="low_stock" {{ request('status') == 'low_stock' ? 'selected' : '' }}>منخفض</option>
                    <option value="out_of_stock" {{ request('status') == 'out_of_stock' ? 'selected' : '' }}>نفذ من المخزون</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="tf-btn tf-btn-primary flex-1"><i class="fas fa-search"></i> عرض</button>
                <button type="button" onclick="window.print()" class="tf-btn bg-gray-200"><i class="fas fa-print"></i></button>
            </div>
        </form>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="tf-card tf-stat">
            <div class="text-gray-600 mb-2">إجمالي المنتجات</div>
            <div class="tf-stat-value text-blue-600">{{ $stats['total_products'] ?? 0 }}</div>
        </div>
        <div class="tf-card tf-stat">
            <div class="text-gray-600 mb-2">القيمة الإجمالية</div>
            <div class="tf-stat-value text-green-600">{{ number_format($stats['total_value'] ?? 0) }} ج.م</div>
        </div>
        <div class="tf-card tf-stat">
            <div class="text-gray-600 mb-2">منخفض المخزون</div>
            <div class="tf-stat-value text-orange-600">{{ $stats['low_stock'] ?? 0 }}</div>
        </div>
        <div class="tf-card tf-stat">
            <div class="text-gray-600 mb-2">نفذ من المخزون</div>
            <div class="tf-stat-value text-red-600">{{ $stats['out_of_stock'] ?? 0 }}</div>
        </div>
    </div>

    <!-- Table -->
    <div class="tf-card">
        <div class="tf-card-head">
            <h3><i class="fas fa-boxes ml-2"></i>تفاصيل المخزون</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="tf-table">
                <thead>
                    <tr>
                        <th>المنتج</th>
                        <th>الكود</th>
                        <th>الفئة</th>
                        <th>الكمية</th>
                        <th>سعر التكلفة</th>
                        <th>سعر البيع</th>
                        <th>القيمة</th>
                        <th>الحالة</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inventory as $product)
                    <tr>
                        <td>{{ $product->name }}</td>
                        <td>{{ $product->code ?? '-' }}</td>
                        <td>{{ $product->warehouse_name ?? '-' }}</td>
                        <td>{{ number_format($product->quantity, 2) }}</td>
                        <td>{{ number_format($product->purchase_price, 2) }} ج.م</td>
                        <td>{{ number_format($product->selling_price, 2) }} ج.م</td>
                        <td>{{ number_format($product->total_value, 2) }} ج.م</td>
                        <td>
                            @if($product->quantity <= 0)
                                <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-xs font-semibold">نفذ</span>
                            @elseif($product->quantity <= $product->min_stock)
                                <span class="px-3 py-1 bg-orange-100 text-orange-700 rounded-full text-xs font-semibold">منخفض</span>
                            @else
                                <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold">متوفر</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-8 text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-3 block"></i>
                            لا توجد منتجات
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Print Footer --}}
    <div class="print-footer">
        <p>تم طباعة هذا التقرير من نظام إدارة المخازن - {{ $company->name ?? 'شركة ماجزاني' }} - {{ \Carbon\Carbon::now()->format('Y-m-d H:i') }}</p>
    </div>
</div>
@endsection
