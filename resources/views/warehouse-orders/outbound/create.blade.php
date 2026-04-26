@extends('layouts.app')

@section('title', 'إنشاء أذن إخراج بضاعة')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-arrow-up text-danger"></i>
                إنشاء أذن إخراج بضاعة
            </h1>
        </div>
        <a href="{{ route('warehouse-orders.outbound.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-right"></i>
            رجوع
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <form method="POST" action="{{ route('warehouse-orders.outbound.store') }}">
                @csrf

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
                                <select name="warehouse_id" id="warehouse_id" class="form-select" required>
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
                                <label class="form-label">الغرض <span class="text-danger">*</span></label>
                                <select name="purpose" class="form-select" required>
                                    <option value="sale">بيع</option>
                                    <option value="transfer">تحويل</option>
                                    <option value="return">مرتجع</option>
                                    <option value="damage">تالف</option>
                                    <option value="sample">عينة</option>
                                    <option value="other">أخرى</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">اسم المستلم</label>
                            <input type="text" name="recipient_name" class="form-control"
                                   placeholder="اسم الشخص أو الجهة المستلمة">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">ملاحظات</label>
                            <textarea name="notes" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>

                <!-- الأصناف -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-boxes text-warning"></i>
                            الأصناف المطلوبة
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            سيتم التحقق من توفر الكميات في المخزن عند اعتماد الأذن
                        </div>

                        <div id="items-container">
                            <!-- الصف الأول -->
                            <div class="item-row card mb-3">
                                <div class="card-body">
                                    <div class="row align-items-end">
                                        <div class="col-md-5 mb-2">
                                            <label class="form-label">الصنف <span class="text-danger">*</span></label>
                                            <select name="items[0][product_id]"
                                                    class="form-select product-select" required>
                                                <option value="">اختر الصنف</option>
                                                @foreach($products as $product)
                                                <option value="{{ $product->id }}"
                                                        data-unit="{{ $product->baseUnit->name ?? '' }}">
                                                    {{ $product->name }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <label class="form-label">الكمية المطلوبة <span class="text-danger">*</span></label>
                                            <input type="number" name="items[0][requested_quantity]"
                                                   class="form-control" step="0.001" min="0.001" required>
                                        </div>
                                        <div class="col-md-2 mb-2">
                                            <label class="form-label">الوحدة <span class="text-danger">*</span></label>
                                            <input type="text" name="items[0][unit]" class="form-control" required>
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
                            <a href="{{ route('warehouse-orders.outbound.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i>
                                إلغاء
                            </a>
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-save"></i>
                                حفظ الأذن
                            </button>
                        </div>
                    </div>
                </div>
            </form>
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
                        <li class="mb-2">اختر المخزن الذي سيتم الصرف منه</li>
                        <li class="mb-2">حدد الغرض من الإخراج</li>
                        <li class="mb-2">حدد اسم الجهة أو الشخص المستلم</li>
                        <li class="mb-2">أضف الأصناف المطلوبة بكمياتها</li>
                        <li class="mb-2">بمجرد الاعتماد سيتم خصم الكميات من المخزون</li>
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
let itemCount = 1;

document.getElementById('add-item').addEventListener('click', function() {
    const container = document.getElementById('items-container');
    const template = `
        <div class="item-row card mb-3">
            <div class="card-body">
                <div class="row align-items-end">
                    <div class="col-md-5 mb-2">
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
                    <div class="col-md-3 mb-2">
                        <label class="form-label">الكمية المطلوبة <span class="text-danger">*</span></label>
                        <input type="number" name="items[${itemCount}][requested_quantity]" class="form-control"
                               step="0.001" min="0.001" required>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="form-label">الوحدة <span class="text-danger">*</span></label>
                        <input type="text" name="items[${itemCount}][unit]" class="form-control" required>
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
</script>
@endpush
