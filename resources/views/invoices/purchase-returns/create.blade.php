@extends('layouts.app')

@section('title', 'إنشاء مرتجع شراء')

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&family=Tajawal:wght@300;400;500;700;800&display=swap');

    :root {
        --primary: #1a1f3a;
        --accent: #4f6ef7;
        --accent-light: #6b84ff;
        --accent-soft: rgba(79,110,247,0.10);
        --success: #059669;
        --success-soft: rgba(5,150,105,0.10);
        --danger: #e03355;
        --danger-soft: rgba(224,51,85,0.10);
        --warning: #d97706;
        --warning-soft: rgba(217,119,6,0.10);
        --surface: #ffffff;
        --surface-2: #f8f9fd;
        --border: rgba(0,0,0,0.07);
        --text-main: #1a1f3a;
        --text-muted: #8b92a5;
    }

    body {
        font-family: 'Cairo', 'Tajawal', sans-serif;
        background: var(--surface-2);
        color: var(--text-main);
    }

    .form-card {
        background: var(--surface);
        border-radius: 16px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        padding: 24px;
        margin-bottom: 20px;
    }

    .form-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
        padding-bottom: 16px;
        border-bottom: 1px solid var(--border);
    }

    .form-header h2 {
        font-size: 1.3rem;
        font-weight: 700;
        color: var(--primary);
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--text-main);
        margin-bottom: 8px;
    }

    .form-control {
        width: 100%;
        padding: 12px 16px;
        border: 1.5px solid #e5e7eb;
        border-radius: 10px;
        font-size: 0.9rem;
        font-family: inherit;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .form-control:focus {
        outline: none;
        border-color: var(--accent);
        box-shadow: 0 0 0 3px rgba(79,110,247,0.1);
    }

    select.form-control {
        appearance: none;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
        background-position: left 12px center;
        background-repeat: no-repeat;
        background-size: 20px;
        padding-left: 40px;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 12px 24px;
        border-radius: 50px;
        font-size: 0.9rem;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--accent), var(--accent-light));
        color: white;
    }

    .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 20px rgba(79,110,247,0.3);
    }

    .btn-secondary {
        background: #f3f4f6;
        color: var(--text-muted);
    }

    .btn-secondary:hover {
        background: #e5e7eb;
    }

    .btn-danger {
        background: var(--danger-soft);
        color: var(--danger);
    }

    .btn-danger:hover {
        background: var(--danger);
        color: white;
    }

    .items-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 16px;
    }

    .items-table th,
    .items-table td {
        padding: 12px;
        text-align: right;
        border-bottom: 1px solid var(--border);
    }

    .items-table th {
        background: var(--surface-2);
        font-size: 0.75rem;
        font-weight: 700;
        color: var(--text-muted);
        text-transform: uppercase;
    }

    .items-table td {
        font-size: 0.9rem;
    }

    .items-table input[type="number"] {
        width: 80px;
        padding: 8px;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        text-align: center;
    }

    .items-table input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
    }

    .total-box {
        background: linear-gradient(135deg, var(--primary), #2d3250);
        color: white;
        padding: 20px;
        border-radius: 12px;
        margin-top: 20px;
    }

    .total-box h4 {
        font-size: 0.85rem;
        opacity: 0.8;
        margin-bottom: 4px;
    }

    .total-box .amount {
        font-size: 1.8rem;
        font-weight: 800;
    }

    .invoice-selector {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 12px;
        margin-top: 16px;
    }

    .invoice-option {
        background: var(--surface);
        border: 2px solid var(--border);
        border-radius: 10px;
        padding: 16px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .invoice-option:hover {
        border-color: var(--accent);
    }

    .invoice-option.selected {
        border-color: var(--accent);
        background: var(--accent-soft);
    }

    .invoice-option h4 {
        font-size: 0.95rem;
        font-weight: 600;
        margin-bottom: 4px;
    }

    .invoice-option p {
        font-size: 0.8rem;
        color: var(--text-muted);
    }

    @media (max-width: 768px) {
        .form-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
        }
    }
</style>
@endpush

@section('content')
<div class="p-6 max-w-5xl mx-auto">
    @if (session('error'))
        <div class="mb-6 p-4 bg-red-50 border-2 border-red-200 rounded-xl">
            <p class="font-bold text-red-800">{{ session('error') }}</p>
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 p-4 bg-red-50 border-2 border-red-200 rounded-xl">
            <h4 class="font-bold text-red-800 mb-2">يرجى تصحيح الأخطاء التالية:</h4>
            <ul class="list-disc list-inside text-red-700 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Header -->
    <div class="form-header">
        <div>
            <h2>إنشاء مرتجع شراء جديد</h2>
            <p style="color: var(--text-muted); font-size: 0.85rem; margin-top: 4px;">قم بإخال بيانات المرتجع الجديد</p>
        </div>
        <a href="{{ route('invoices.purchase-returns.index') }}" class="btn btn-secondary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M19 12H5M12 19l-7-7 7-7"/>
            </svg>
            رجوع
        </a>
    </div>

    <form action="{{ route('invoices.purchase-returns.store') }}" method="POST" id="returnForm">
        @csrf

        <!-- Invoice Selection -->
        <div class="form-card">
            <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 16px;">
                <i class="fas fa-file-invoice ml-2"></i>
                اختر فاتورة الشراء
            </h3>

            @if($invoices->count() > 0)
            <div class="invoice-selector">
                @foreach($invoices as $inv)
                <div class="invoice-option {{ old('purchase_invoice_id') == $inv->id ? 'selected' : '' }}" 
                     onclick="selectInvoice({{ $inv->id }}, '{{ $inv->invoice_number }}')">
                    <h4>{{ $inv->invoice_number }}</h4>
                    <p>{{ $inv->supplier->name ?? 'غير معروف' }} - {{ $inv->total }} ج.م</p>
                </div>
                @endforeach
            </div>
            <input type="hidden" name="purchase_invoice_id" id="selectedInvoiceId" value="{{ old('purchase_invoice_id', $invoice?->id ?? '') }}">
            @else
            <p style="color: var(--text-muted); text-align: center; padding: 20px;">
                لا توجد فواتير متاحة للإرجاع
            </p>
            @endif
        </div>

        <!-- Return Details -->
        <div class="form-card">
            <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 16px;">
                <i class="fas fa-info-circle ml-2"></i>
                تفاصيل المرتجع
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group">
                    <label>تاريخ الإرجاع</label>
                    <input type="date" name="return_date" class="form-control" value="{{ old('return_date', now()->format('Y-m-d')) }}" required>
                </div>

                <div class="form-group">
                    <label>سبب الإرجاع</label>
                    <input type="text" name="return_reason" class="form-control" value="{{ old('return_reason') }}" placeholder="أدخل سبب الإرجاع">
                </div>
            </div>

            <div class="form-group">
                <label>ملاحظات</label>
                <textarea name="notes" class="form-control" rows="2" placeholder="أي ملاحظات إضافية">{{ old('notes') }}</textarea>
            </div>
        </div>

        <!-- Items -->
        @if($invoice && $availableItems && (is_array($availableItems) ? count($availableItems) > 0 : $availableItems->count() > 0))
        <div class="form-card">
            <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 16px;">
                <i class="fas fa-box ml-2"></i>
                الأصناف المراد إرجاعها
            </h3>

            <table class="items-table">
                <thead>
                    <tr>
                        <th width="40"></th>
                        <th>الصنف</th>
                        <th class="text-center">الكمية الأصلية</th>
                        <th class="text-center">السعر</th>
                        <th class="text-center">الكمية المردودة</th>
                        <th class="text-center">الحالة</th>
                        <th class="text-center">سبب الإرجاع</th>
                        <th class="text-left">الإجمالي</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($availableItems as $index => $item)
                    <tr>
                        <td>
                            <input type="checkbox" 
                                   name="items[{{ $index }}][selected]" 
                                   id="item_{{ $item['purchase_invoice_item_id'] }}"
                                   onchange="toggleItem({{ $item['purchase_invoice_item_id'] }}, {{ $index }})">
                            <input type="hidden" 
                                   name="items[{{ $index }}][purchase_invoice_item_id]" 
                                   value="{{ $item['purchase_invoice_item_id'] }}">
                        </td>
                        <td>
                            <label for="item_{{ $item['purchase_invoice_item_id'] }}" class="font-semibold">
                                {{ $item['product']['name'] ?? 'غير معروف' }}
                            </label>
                        </td>
                        <td class="text-center">{{ $item['available_qty'] }}</td>
                        <td class="text-center">{{ number_format($item['unit_price'], 2) }} ج.م</td>
                        <td class="text-center">
                            <input type="number" 
                                   name="items[{{ $index }}][quantity_returned]" 
                                   id="qty_{{ $item['purchase_invoice_item_id'] }}"
                                   min="0.001" max="{{ $item['available_qty'] }}" 
                                   step="0.001"
                                   value="0" 
                                   disabled
                                   onchange="calculateTotal()">
                        </td>
                        <td class="text-center">
                            <select name="items[{{ $index }}][item_condition]" 
                                    id="condition_{{ $item['purchase_invoice_item_id'] }}"
                                    class="form-control"
                                    disabled
                                    style="padding: 6px 10px; font-size: 0.85rem;">
                                <option value="good">جيد</option>
                                <option value="damaged">تالف</option>
                                <option value="defective">معيب</option>
                            </select>
                        </td>
                        <td class="text-center">
                            <input type="text" 
                                   name="items[{{ $index }}][return_reason]" 
                                   id="reason_{{ $item['purchase_invoice_item_id'] }}"
                                   class="form-control"
                                   disabled
                                   placeholder="سبب الإرجاع"
                                   style="padding: 6px 10px; font-size: 0.85rem;">
                        </td>
                        <td class="text-left font-bold" id="total_{{ $item['purchase_invoice_item_id'] }}">
                            0.00 ج.م
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <input type="hidden" name="items_data" id="itemsData">
        </div>

        <!-- Totals -->
        <div class="total-box">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h4>إجمالي المرتجع</h4>
                    <div class="amount" id="grandTotal">0.00 ج.م</div>
                </div>
                <button type="submit" class="btn btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/>
                        <polyline points="17,21 17,13 7,13 7,21"/>
                        <polyline points="7,3 7,8 15,8"/>
                    </svg>
                    حفظ المرتجع
                </button>
            </div>
        </div>
        @else
        <div class="form-card" style="text-align: center; padding: 40px;">
            <i class="fas fa-info-circle" style="font-size: 2rem; color: var(--text-muted);"></i>
            <p style="margin-top: 12px; color: var(--text-muted);">
                اختر فاتورة شراء أعلاه لعرض الأصناف المتاحة للإرجاع
            </p>
        </div>
        @endif
    </form>
</div>

@push('scripts')
<script>
    const itemsData = @json($availableItems ?? []);

    function selectInvoice(id, number) {
        document.querySelectorAll('.invoice-option').forEach(el => el.classList.remove('selected'));
        event.target.closest('.invoice-option').classList.add('selected');
        document.getElementById('selectedInvoiceId').value = id;
        
        // Submit form to reload with items
        const form = document.createElement('form');
        form.method = 'GET';
        form.action = '{{ route("invoices.purchase-returns.create") }}';
        const input = document.createElement('input');
        input.name = 'invoice_id';
        input.value = id;
        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    }

    function toggleItem(id, index) {
        const checkbox = document.getElementById('item_' + id);
        const qtyInput = document.getElementById('qty_' + id);
        const conditionSelect = document.getElementById('condition_' + id);
        const reasonInput = document.getElementById('reason_' + id);
        
        if (checkbox.checked) {
            qtyInput.disabled = false;
            conditionSelect.disabled = false;
            reasonInput.disabled = false;
            qtyInput.value = 1;
            // Set default condition and reason
            conditionSelect.value = 'good';
            reasonInput.value = 'إرجاع بسبب انتهاء الصلاحية';
        } else {
            qtyInput.disabled = true;
            conditionSelect.disabled = true;
            reasonInput.disabled = true;
            qtyInput.value = 0;
            conditionSelect.value = 'good';
            reasonInput.value = '';
        }
        calculateTotal();
    }

    function calculateTotal() {
        let total = 0;
        
        if (itemsData && itemsData.length > 0) {
            itemsData.forEach(function(item, index) {
                const checkbox = document.getElementById('item_' + item.purchase_invoice_item_id);
                if (checkbox && checkbox.checked) {
                    const qty = parseFloat(document.getElementById('qty_' + item.purchase_invoice_item_id).value) || 0;
                    const price = item.unit_price;
                    const itemTotal = qty * price;
                    document.getElementById('total_' + item.purchase_invoice_item_id).textContent = itemTotal.toFixed(2) + ' ج.م';
                    total += itemTotal;
                } else {
                    document.getElementById('total_' + item.purchase_invoice_item_id).textContent = '0.00 ج.م';
                }
            });
        }

        document.getElementById('grandTotal').textContent = total.toFixed(2) + ' ج.م';
    }
</script>
@endpush
@endsection
