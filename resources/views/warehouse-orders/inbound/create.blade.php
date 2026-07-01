@extends('layouts.app')

@section('title', 'إنشاء أذن إدخال بضاعة')

@section('content')
<div class="wo-warehouse-order-page container-fluid px-2 px-md-4 pb-4"
     id="inbound-create-root"
     data-stock-url="{{ route('warehouse-orders.stock-preview') }}">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <h1 class="h3 mb-0 flex-grow-1" style="min-width: 0;">
            <i class="fas fa-arrow-down text-success ms-1"></i>
            إنشاء أذن إدخال بضاعة
        </h1>
        <a href="{{ route('warehouse-orders.inbound.index') }}" class="btn btn-outline-secondary flex-shrink-0">
            <i class="fas fa-arrow-right ms-1"></i>
            رجوع
        </a>
    </div>

    <div class="row g-4">
        <div class="col-12 col-lg-8 order-2 order-lg-1">
            <form method="POST" action="{{ route('warehouse-orders.inbound.store') }}" id="inbound-store-form">
                @csrf

                @if ($errors->any())
                <div class="alert alert-danger mb-3" role="alert">
                    <ul class="mb-0 pe-3 small">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-file-alt text-primary"></i>
                        بيانات الأذن
                    </h5>
                </div>
                <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">المخزن <span class="text-danger">*</span></label>
                                <select name="warehouse_id" id="inbound-warehouse-select" class="form-select" required>
                                    <option value="">اختر المخزن</option>
                                    @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}" @selected(old('warehouse_id', request('warehouse_id')) == $warehouse->id)>{{ $warehouse->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">التاريخ <span class="text-danger">*</span></label>
                                <input type="date" name="order_date" class="form-control"
                                       value="{{ old('order_date', today()->format('Y-m-d')) }}" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">رقم المرجع</label>
                                <input type="text" name="reference_number" class="form-control"
                                       value="{{ old('reference_number') }}"
                                       placeholder="رقم الفاتورة أو المستند">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">الغرض</label>
                                <select class="form-select" disabled>
                                    <option>إدخال بضاعة للمخزن</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">ملاحظات</label>
                            <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                        </div>
                </div>
            </div>

            <!-- الأصناف -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-boxes text-warning"></i>
                        الأصناف
                    </h5>
                </div>
                <div class="card-body">
                    <div id="items-container">
                        <!-- الصف الأول -->
                        <div class="item-row card mb-3 border shadow-sm">
                            <div class="card-body">
                                <div class="row g-3 align-items-end">
                                    <div class="col-12 col-lg-5">
                                        <label class="form-label">الصنف <span class="text-danger">*</span></label>
                                        <select name="items[0][product_id]" class="form-select product-select" required>
                                            <option value="">اختر الصنف</option>
                                            @foreach($products as $product)
                                            <option value="{{ $product->id }}"
                                                    data-unit="{{ optional($product->baseunit)->base_unit_label ?? '' }}">
                                                {{ $product->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-6 col-md-3 col-lg-2">
                                        <label class="form-label">الكمية <span class="text-danger">*</span></label>
                                        <input type="number" name="items[0][quantity]" class="form-control qty-input"
                                               step="0.001" min="0.001" required>
                                    </div>
                                    <div class="col-6 col-md-3 col-lg-2">
                                        <label class="form-label">الوحدة <span class="text-danger">*</span></label>
                                        <input type="text" name="items[0][unit]" class="form-control" required>
                                    </div>
                                    <div class="col-6 col-md-3 col-lg-2">
                                        <label class="form-label">تكلفة الوحدة</label>
                                        <input type="number" name="items[0][unit_cost]" class="form-control unit-cost-input"
                                               step="0.01" min="0" placeholder="0.00">
                                    </div>
                                    <div class="col-6 col-md-3 col-lg-1 d-grid">
                                        <label class="form-label d-none d-md-block">&nbsp;</label>
                                        <button type="button" class="btn btn-danger remove-item" title="حذف الصنف">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="row mt-3 align-items-center gy-2">
                                    <div class="col-12 col-md-6">
                                        <small class="text-muted">الكمية في المخزن:
                                            <strong class="stock-qty-display">—</strong>
                                            <span class="stock-system-total-hint text-muted d-none ms-1"></span></small>
                                    </div>
                                    <div class="col-12 col-md-6 text-md-start">
                                        <small class="text-muted">إجمالي التكلفة:
                                            <strong class="line-total-display">0.00</strong></small>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="items[0][notes]"
                                               class="form-control" placeholder="ملاحظات الصنف">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn btn-outline-primary" id="add-item">
                        <i class="fas fa-plus"></i>
                        إضافة صنف
                    </button>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-4">
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save ms-1"></i>
                            حفظ الأذن
                        </button>
                        <a href="{{ route('warehouse-orders.inbound.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times ms-1"></i>
                            إلغاء
                        </a>
                    </div>
                </div>
            </div>

            </form>
        </div>

        <div class="col-12 col-lg-4 order-1 order-lg-2">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle text-info"></i>
                        تعليمات
                    </h5>
                </div>
                <div class="card-body">
                    <ul class="mb-0">
                        <li class="mb-2">اختر المخزن الذي سيتم استلام البضاعة فيه</li>
                        <li class="mb-2">حدد تاريخ الاستلام الفعلي</li>
                        <li class="mb-2">أضف جميع الأصناف المستلمة مع كمياتها</li>
                        <li class="mb-2">يمكن إضافة تكلفة الوحدة لحساب التكلفة الإجمالية</li>
                        <li class="mb-2">بعد الحفظ سيتم تحديث المخزون تلقائياً</li>
                    </ul>
                </div>
            </div>

            @if(session('company_logo'))
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-body text-center">
                    <img src="{{ session('company_logo') }}" alt="شعار الشركة"
                         class="img-fluid mb-2" style="max-height: 80px;">
                    <h5 class="mb-0">{{ session('company_name', 'اسم الشركة') }}</h5>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const root = document.getElementById('inbound-create-root');
    const stockUrl = root ? root.dataset.stockUrl : '';
    let itemCount = 1;

    function formatNum(n, decimals) {
        const x = parseFloat(n);
        if (Number.isNaN(x)) return (0).toFixed(decimals);
        return x.toFixed(decimals);
    }

    function updateLineTotal(row) {
        const qtyEl = row.querySelector('.qty-input');
        const costEl = row.querySelector('.unit-cost-input');
        const totalEl = row.querySelector('.line-total-display');
        if (!totalEl) return;
        const q = qtyEl ? parseFloat(qtyEl.value) : 0;
        const u = costEl ? parseFloat(costEl.value) : 0;
        const total = (Number.isNaN(q) ? 0 : q) * (Number.isNaN(u) ? 0 : u);
        totalEl.textContent = formatNum(total, 2);
    }

    function getWarehouseId() {
        const sel = document.getElementById('inbound-warehouse-select');
        return sel && sel.value ? sel.value : '';
    }

    async function fetchStockForRow(row) {
        const wh = getWarehouseId();
        const productSel = row.querySelector('.product-select');
        const stockEl = row.querySelector('.stock-qty-display');
        const unitCostInput = row.querySelector('.unit-cost-input');
        if (!stockEl) return;

        if (!wh || !productSel || !productSel.value) {
            stockEl.textContent = '—';
            const hintEl = row.querySelector('.stock-system-total-hint');
            if (hintEl) {
                hintEl.textContent = '';
                hintEl.classList.add('d-none');
            }
            updateLineTotal(row);
            return;
        }

        stockEl.textContent = '…';
        try {
            const url = new URL(stockUrl, window.location.origin);
            url.searchParams.set('warehouse_id', wh);
            url.searchParams.set('product_id', productSel.value);
            const res = await fetch(url.toString(), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            });
            if (!res.ok) throw new Error('bad response');
            const data = await res.json();
            stockEl.textContent = formatNum(data.quantity_in_warehouse ?? 0, 3);

            const hintEl = row.querySelector('.stock-system-total-hint');
            if (hintEl) {
                const qw = parseFloat(data.quantity_in_warehouse) || 0;
                const qt = parseFloat(data.quantity_total_all_warehouses) || 0;
                if (qw === 0 && qt > 0) {
                    hintEl.textContent = '(إجمالي الكمية في كل المخازن: ' + formatNum(qt, 3) + ')';
                    hintEl.classList.remove('d-none');
                } else {
                    hintEl.textContent = '';
                    hintEl.classList.add('d-none');
                }
            }

            if (data.suggested_unit_cost != null && unitCostInput) {
                const cur = parseFloat(unitCostInput.value);
                if (!unitCostInput.value || unitCostInput.value === '' || (!Number.isNaN(cur) && cur === 0)) {
                    unitCostInput.value = formatNum(data.suggested_unit_cost, 2);
                }
            }
        } catch (err) {
            stockEl.textContent = '؟';
        }
        updateLineTotal(row);
    }

    function refreshAllItemRows() {
        document.querySelectorAll('.item-row').forEach(function (row) {
            fetchStockForRow(row);
        });
    }

    document.getElementById('add-item').addEventListener('click', function () {
        const container = document.getElementById('items-container');
        const template = `
        <div class="item-row card mb-3 border shadow-sm">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-lg-5">
                        <label class="form-label">الصنف <span class="text-danger">*</span></label>
                        <select name="items[${itemCount}][product_id]" class="form-select product-select" required>
                            <option value="">اختر الصنف</option>
                            @foreach($products as $product)
                            <option value="{{ $product->id }}" data-unit="{{ optional($product->baseunit)->base_unit_label ?? '' }}">
                                {{ $product->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <label class="form-label">الكمية <span class="text-danger">*</span></label>
                        <input type="number" name="items[${itemCount}][quantity]" class="form-control qty-input"
                               step="0.001" min="0.001" required>
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <label class="form-label">الوحدة <span class="text-danger">*</span></label>
                        <input type="text" name="items[${itemCount}][unit]" class="form-control" required>
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <label class="form-label">تكلفة الوحدة</label>
                        <input type="number" name="items[${itemCount}][unit_cost]" class="form-control unit-cost-input"
                               step="0.01" min="0" placeholder="0.00">
                    </div>
                    <div class="col-6 col-md-3 col-lg-1 d-grid">
                        <label class="form-label d-none d-md-block">&nbsp;</label>
                        <button type="button" class="btn btn-danger remove-item" title="حذف الصنف">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                <div class="row mt-3 align-items-center gy-2">
                    <div class="col-12 col-md-6">
                        <small class="text-muted">الكمية في المخزن:
                            <strong class="stock-qty-display">—</strong>
                            <span class="stock-system-total-hint text-muted d-none ms-1"></span></small>
                    </div>
                    <div class="col-12 col-md-6 text-md-start">
                        <small class="text-muted">إجمالي التكلفة:
                            <strong class="line-total-display">0.00</strong></small>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <input type="text" name="items[${itemCount}][notes]"
                               class="form-control" placeholder="ملاحظات الصنف">
                    </div>
                </div>
            </div>
        </div>
    `;
        container.insertAdjacentHTML('beforeend', template);
        itemCount++;
        const lastRow = container.querySelector('.item-row:last-child');
        if (lastRow) fetchStockForRow(lastRow);
    });

    document.addEventListener('click', function (e) {
        if (e.target.closest('.remove-item')) {
            const row = e.target.closest('.item-row');
            const rows = document.querySelectorAll('.item-row');
            if (rows.length > 1) {
                row.remove();
            } else {
                alert('يجب أن يكون هناك صنف واحد على الأقل');
            }
        }
    });

    document.addEventListener('change', function (e) {
        if (e.target.id === 'inbound-warehouse-select') {
            refreshAllItemRows();
            return;
        }
        if (e.target.classList.contains('product-select')) {
            const selectedOption = e.target.options[e.target.selectedIndex];
            const unit = selectedOption.getAttribute('data-unit');
            const row = e.target.closest('.item-row');
            const unitInput = row.querySelector('input[name*="[unit]"]');
            if (unit && unitInput) {
                unitInput.value = unit;
            }
            fetchStockForRow(row);
        }
    });

    document.addEventListener('input', function (e) {
        if (e.target.classList.contains('qty-input') || e.target.classList.contains('unit-cost-input')) {
            updateLineTotal(e.target.closest('.item-row'));
        }
    });

    refreshAllItemRows();
})();
</script>
@endpush
