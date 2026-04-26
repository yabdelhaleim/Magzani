@extends('layouts.app')

@section('title', 'إنشاء أذن إدخال بضاعة')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-arrow-down text-success"></i>
                إنشاء أذن إدخال بضاعة
            </h1>
        </div>
        <a href="{{ route('warehouse-orders.inbound.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-right"></i>
            رجوع
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-file-alt text-primary"></i>
                        بيانات الأذن
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('warehouse-orders.inbound.store') }}">
                        @csrf

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">المخزن <span class="text-danger">*</span></label>
                                <select name="warehouse_id" class="form-select" required>
                                    <option value="">اختر المخزن</option>
                                    @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">التاريخ <span class="text-danger">*</span></label>
                                <input type="date" name="order_date" class="form-control"
                                       value="{{ today()->format('Y-m-d') }}" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">رقم المرجع</label>
                                <input type="text" name="reference_number" class="form-control"
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
                            <textarea name="notes" class="form-control" rows="2"></textarea>
                        </div>
                    </form>
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
                        <div class="item-row card mb-3">
                            <div class="card-body">
                                <div class="row align-items-end">
                                    <div class="col-md-4 mb-2">
                                        <label class="form-label">الصنف <span class="text-danger">*</span></label>
                                        <select name="items[0][product_id]" class="form-select product-select" required>
                                            <option value="">اختر الصنف</option>
                                            @foreach($products as $product)
                                            <option value="{{ $product->id }}"
                                                    data-unit="{{ $product->baseUnit->name ?? '' }}">
                                                {{ $product->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <label class="form-label">الكمية <span class="text-danger">*</span></label>
                                        <input type="number" name="items[0][quantity]" class="form-control"
                                               step="0.001" min="0.001" required>
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <label class="form-label">الوحدة <span class="text-danger">*</span></label>
                                        <input type="text" name="items[0][unit]" class="form-control" required>
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <label class="form-label">التكلفة</label>
                                        <input type="number" name="items[0][unit_cost]" class="form-control"
                                               step="0.01" min="0" placeholder="0.00">
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <button type="button" class="btn btn-danger w-100 remove-item">
                                            <i class="fas fa-trash"></i>
                                        </button>
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
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('warehouse-orders.inbound.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i>
                            إلغاء
                        </a>
                        <button type="submit" form="main-form" class="btn btn-success">
                            <i class="fas fa-save"></i>
                            حفظ الأذن
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
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
</form>

<form id="main-form" method="POST" action="{{ route('warehouse-orders.inbound.store') }}" style="display:none;">
    @csrf
</form>

@push('scripts')
<script>
let itemCount = 1;

document.getElementById('add-item').addEventListener('click', function() {
    const container = document.getElementById('items-container');
    const template = `
        <div class="item-row card mb-3">
            <div class="card-body">
                <div class="row align-items-end">
                    <div class="col-md-4 mb-2">
                        <label class="form-label">الصنف <span class="text-danger">*</span></label>
                        <select name="items[${itemCount}][product_id]" class="form-select product-select" required>
                            <option value="">اختر الصنف</option>
                            @foreach($products as $product)
                            <option value="{{ $product->id }}" data-unit="{{ $product->baseUnit->name ?? '' }}">
                                {{ $product->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="form-label">الكمية <span class="text-danger">*</span></label>
                        <input type="number" name="items[${itemCount}][quantity]" class="form-control"
                               step="0.001" min="0.001" required>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="form-label">الوحدة <span class="text-danger">*</span></label>
                        <input type="text" name="items[${itemCount}][unit]" class="form-control" required>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="form-label">التكلفة</label>
                        <input type="number" name="items[${itemCount}][unit_cost]" class="form-control"
                               step="0.01" min="0" placeholder="0.00">
                    </div>
                    <div class="col-md-2 mb-2">
                        <button type="button" class="btn btn-danger w-100 remove-item">
                            <i class="fas fa-trash"></i>
                        </button>
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
});

document.addEventListener('click', function(e) {
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

document.addEventListener('change', function(e) {
    if (e.target.classList.contains('product-select')) {
        const selectedOption = e.target.options[e.target.selectedIndex];
        const unit = selectedOption.getAttribute('data-unit');
        const row = e.target.closest('.item-row');
        const unitInput = row.querySelector('input[name*="[unit]"]');
        if (unit && unitInput) {
            unitInput.value = unit;
        }
    }
});

document.querySelector('button[form="main-form"]').addEventListener('click', function(e) {
    e.preventDefault();
    const mainForm = document.getElementById('main-form');
    const otherForm = document.querySelector('form:not(#main-form)');

    // نسخ جميع الحقول من النموذج الآخر
    otherForm.querySelectorAll('input, select, textarea').forEach(field => {
        mainForm.appendChild(field.cloneNode(true));
    });

    mainForm.submit();
});
</script>
@endpush
