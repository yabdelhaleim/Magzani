@extends('layouts.app')

@section('title', 'فاتورة مبيعات جديدة')
@section('page-title', 'فاتورة مبيعات جديدة')

@push('styles')
<style>
    :root {
        --tf-bg:          #f4f7fe;
        --tf-surface:     #ffffff;
        --tf-surface2:    #f8faff;
        --tf-border:      #e4eaf7;
        --tf-border-soft: #edf0f9;

        --tf-indigo:      #4f63d2;
        --tf-indigo-light:#7088e8;
        --tf-indigo-soft: #eef0fc;

        --tf-blue:        #3a8ef0;
        --tf-blue-soft:   #e8f2ff;
        --tf-green:       #0faa7e;
        --tf-green-soft:  #e6f8f3;
        --tf-red:         #dc2626;
        --tf-red-soft:    #fee2e2;
        --tf-amber:       #e8930a;
        --tf-amber-soft:  #fff4e0;
        --tf-violet:      #7c5cec;
        --tf-violet-soft: #f0ecff;

        --tf-text-h:      #1a2140;
        --tf-text-b:      #3d4f72;
        --tf-text-m:      #7e90b0;
        --tf-text-d:      #94a3b8;

        --tf-shadow-sm:   0 2px 12px rgba(79,99,210,0.07);
        --tf-shadow-card: 0 2px 0 0 rgba(0,0,0,0.04), 0 4px 20px rgba(79,99,210,0.08);
        --tf-shadow-lg:   0 8px 30px rgba(79,99,210,0.10);
    }

    .tf-page {
        background: var(--tf-bg);
        background-image:
            radial-gradient(ellipse 80% 60% at 10% -10%,  rgba(79,99,210,0.12) 0%, transparent 50%),
            radial-gradient(ellipse 60% 50% at 90% 110%, rgba(58,142,240,0.1) 0%, transparent 50%);
        min-height: 100vh;
        padding: 26px 22px;
    }

    @keyframes tfFadeUp {
        from { opacity: 0; transform: translateY(18px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    @keyframes tfShimmer {
        0%   { background-position: -600px 0; }
        100% { background-position: 600px 0; }
    }
    @keyframes iconBounce {
        0%,100% { transform: translateY(0) rotate(0deg); }
        30%     { transform: translateY(-4px) rotate(-8deg); }
        60%     { transform: translateY(-2px) rotate(4deg); }
    }

    .tf-section { animation: tfFadeUp 0.55s cubic-bezier(0.22,1,0.36,1) both; }
    .tf-section:nth-child(1) { animation-delay: 0.04s; }

    .tf-card {
        background: var(--tf-surface); border-radius: 20px;
        border: 1px solid var(--tf-border);
        overflow: hidden; box-shadow: var(--tf-shadow-card);
        margin-bottom: 20px; position: relative;
        transition: all .35s cubic-bezier(.22,1,.36,1);
    }
    .tf-card:hover { transform: translateY(-3px); box-shadow: var(--tf-shadow-lg); }
    .tf-card::after {
        content: ''; position: absolute; inset: 0;
        background: linear-gradient(105deg, transparent 40%, rgba(255,255,255,.45) 50%, transparent 60%);
        background-size: 600px 100%; opacity: 0; pointer-events: none; transition: opacity .3s;
    }
    .tf-card:hover::after { opacity: 1; animation: tfShimmer .7s ease forwards; }

    .tf-card-head {
        display: flex; justify-content: space-between; align-items: center;
        padding: 20px 24px; border-bottom: 1px solid var(--tf-border-soft);
        background: var(--tf-surface2); flex-wrap: wrap; gap: 12px;
    }
    .tf-card-title { display: flex; align-items: center; gap: 12px; }
    .tf-card-icon {
        width: 48px; height: 48px; border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        font-size: 18px;
        transition: transform .4s cubic-bezier(.34,1.56,.64,1);
    }
    .tf-card:hover .tf-card-icon { animation: iconBounce .6s ease; }
    .tf-card.green .tf-card-icon { background: var(--tf-green-soft); color: var(--tf-green); }
    .tf-card.blue .tf-card-icon { background: var(--tf-blue-soft); color: var(--tf-blue); }
    .tf-card.indigo .tf-card-icon { background: var(--tf-indigo-soft); color: var(--tf-indigo); }

    .tf-title-text { font-size: 18px; font-weight: 800; color: var(--tf-text-h); }
    .tf-title-sub { font-size: 12px; color: var(--tf-text-m); font-weight: 600; }

    .tf-card-body { padding: 20px; }

    .tf-btn {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 12px 20px; border-radius: 14px;
        font-size: 14px; font-weight: 800; cursor: pointer;
        font-family: 'Cairo', sans-serif; text-decoration: none;
        transition: all .25s; border: none;
    }
    .tf-btn-primary {
        background: linear-gradient(135deg, var(--tf-green), #0d8a6e);
        color: var(--tf-surface); border: none;
        box-shadow: 0 4px 16px rgba(15,170,126,0.35);
    }
    .tf-btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(15,170,126,0.45); }
    .tf-btn-secondary {
        background: var(--tf-surface); color: var(--tf-text-b);
        border: 1.5px solid var(--tf-border);
    }
    .tf-btn-secondary:hover { background: var(--tf-surface2); }
    .tf-btn-danger {
        background: linear-gradient(135deg, var(--tf-red), #d63c4c);
        color: var(--tf-surface); border: none;
        box-shadow: 0 4px 16px rgba(232,75,90,0.35);
    }
    .tf-btn-danger:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(232,75,90,0.45); }

    .tf-input, .tf-select {
        width: 100%; padding: 12px 16px;
        border: 1.5px solid var(--tf-border); border-radius: 14px;
        font-size: 14px; font-family: 'Cairo', sans-serif;
        color: var(--tf-text-h); background: var(--tf-surface);
        transition: all .25s; outline: none;
    }
    .tf-input:focus, .tf-select:focus {
        border-color: var(--tf-green);
        box-shadow: 0 0 0 3px rgba(15,170,126,0.12);
    }

    .tf-label {
        display: block; font-size: 12px; font-weight: 700;
        color: var(--tf-text-m); margin-bottom: 6px;
    }

    .tf-grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
    .tf-grid-2 { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; }
    @media (max-width: 900px) { .tf-grid-3 { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 600px) { .tf-grid-3, .tf-grid-2 { grid-template-columns: 1fr; } }

    .tf-table-wrapper { overflow-x: auto; }
    .tf-table { width: 100%; border-collapse: collapse; }
    .tf-table thead th {
        padding: 14px 16px; text-align: right;
        font-size: 11px; font-weight: 800; color: var(--tf-text-m);
        text-transform: uppercase; letter-spacing: .5px;
        border-bottom: 1.5px solid var(--tf-border-soft);
        background: var(--tf-surface2); white-space: nowrap;
    }
    .tf-table tbody tr { transition: background .18s; }
    .tf-table tbody tr:hover { background: var(--tf-surface2); }
    .tf-table tbody td { padding: 14px 16px; border-bottom: 1px solid var(--tf-border-soft); vertical-align: middle; }

    .tf-row-item {
        display: grid; grid-template-columns: 40px 2fr 1.5fr 1fr 1fr 1fr 1fr 1fr 1fr 50px;
        gap: 10px; align-items: center;
        padding: 12px; border-radius: 14px; background: var(--tf-surface);
        border: 1px solid var(--tf-border); margin-bottom: 10px;
        transition: all .2s;
    }
    .tf-row-item:hover { border-color: var(--tf-indigo-light); }
    .tf-row-item.warning { border-color: var(--tf-red); background: var(--tf-red-soft); }

    .tf-badge {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 6px 14px; border-radius: 50px;
        font-size: 11px; font-weight: 800;
    }
    .tf-badge.green  { background: var(--tf-green-soft); color: var(--tf-green); }
    .tf-badge.amber  { background: var(--tf-amber-soft); color: var(--tf-amber); }
    .tf-badge.red    { background: var(--tf-red-soft); color: var(--tf-red); }
    .tf-badge.blue   { background: var(--tf-blue-soft); color: var(--tf-blue); }

    .tf-action-btn {
        display: inline-flex; align-items: center; justify-content: center;
        width: 36px; height: 36px; border-radius: 10px;
        cursor: pointer; transition: all .2s; border: none;
    }
    .tf-action-btn.del { background: var(--tf-red-soft); color: var(--tf-red); }
    .tf-action-btn.del:hover { background: var(--tf-red); color: var(--tf-surface); }

    .tf-total-box {
        padding: 20px; border-radius: 16px;
        background: var(--tf-surface); border: 1px solid var(--tf-border);
    }
    .tf-total-row {
        display: flex; justify-content: space-between; align-items: center;
        padding: 10px 0; border-bottom: 1px solid var(--tf-border-soft);
    }
    .tf-total-row:last-child { border-bottom: none; }
    .tf-total-label { font-size: 14px; color: var(--tf-text-m); font-weight: 600; }
    .tf-total-value { font-size: 14px; font-weight: 800; color: var(--tf-text-h); }
    .tf-total-value.green { color: var(--tf-green); }
    .tf-total-value.red { color: var(--tf-red); }
    .tf-total-value.blue { color: var(--tf-blue); }

    .tf-grand-total {
        font-size: 22px; font-weight: 900; color: var(--tf-green);
    }

    .tf-empty {
        display: flex; flex-direction: column; align-items: center;
        padding: 50px 24px; text-align: center;
    }
    .tf-empty-icon {
        width: 80px; height: 80px; border-radius: 22px;
        display: flex; align-items: center; justify-content: center;
        font-size: 28px; margin-bottom: 16px;
        background: var(--tf-surface2); color: var(--tf-text-m);
    }
    .tf-empty-title { font-size: 16px; font-weight: 800; color: var(--tf-text-h); margin-bottom: 6px; }
    .tf-empty-sub { font-size: 13px; color: var(--tf-text-m); margin-bottom: 20px; }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('invoiceForm', () => ({
        warehouseId: '',
        customerId: '',
        
        // جميع بيانات المنتجات مع المخزون والوحدات
        productsData: {!! json_encode($products->mapWithKeys(function($p) { 
            $baseUnit = $p->baseunit;
            return [$p->id => [
                'id' => $p->id,
                'name' => $p->name,
                'code' => $p->code ?? '',
                'base_unit' => $p->base_unit ?? 'piece',
                'base_purchase_price' => (float)($p->base_purchase_price ?? 0),
                'base_selling_price' => (float)($p->base_selling_price ?? 0),
                'tax_rate' => (float)($p->tax_rate ?? 0),
                'discount' => (float)($p->default_discount ?? 0),
                // مخزون المنتج في كل مخزن (بالوحدة الأساسية)
                'stock' => $p->warehouses->mapWithKeys(function($w) {
                    return [$w->id => (float)$w->pivot->quantity];
                })->toArray(),
                // وحدات البيع المتاحة للمنتج
                'selling_units' => $p->activeSellingUnits->map(function($su) {
                    return [
                        'id' => $su->id,
                        'unit_code' => $su->unit_code,
                        'unit_label' => $su->unit_label,
                        'conversion_factor' => (float)$su->conversion_factor,
                        'quantity_in_base_unit' => (float)$su->quantity_in_base_unit,
                        'selling_price' => (float)($su->unit_selling_price ?? 0),
                        'purchase_price' => (float)($su->unit_purchase_price ?? 0),
                        'is_default' => (bool)$su->is_default,
                    ];
                })->toArray()
            ]];
        })->toArray()) !!},
        
        items: [
            { 
                product_id: '', 
                product_name: '',
                selling_unit_id: '',
                unit_code: '',
                unit_label: '',
                conversion_factor: 1,
                quantity: 1, 
                price: 0, 
                tax_rate: 0,
                discount: 0,
                total: 0,
                available_stock: 0,
                available_stock_in_unit: 0,
                show_stock_warning: false
            }
        ],
        subtotal: 0,
        totalDiscount: 0,
        totalTax: 0,
        shipping: 0,
        grandTotal: 0,
        paid: 0,
        remaining: 0,

        // جلب المخزون المتاح للمنتج في المخزن المحدد
        getAvailableStock(productId) {
            if (!productId || !this.warehouseId || !this.productsData[productId]) {
                return 0;
            }
            const productStock = this.productsData[productId].stock || {};
            return productStock[this.warehouseId] || 0;
        },

        // جلب وحدات البيع المتاحة للمنتج
        getSellingUnits(productId) {
            if (!productId || !this.productsData[productId]) {
                return [];
            }
            return this.productsData[productId].selling_units || [];
        },

        // تحميل بيانات المنتج عند اختياره
        loadProductData(index) {
            const item = this.items[index];
            
            if (!item.product_id || !this.productsData[item.product_id]) {
                this.resetItem(index);
                return;
            }

            const productData = this.productsData[item.product_id];
            item.product_name = productData.name;
            item.tax_rate = productData.tax_rate;
            item.discount = productData.discount;
            
            // تحديث الكمية المتاحة بالوحدة الأساسية
            item.available_stock = this.getAvailableStock(item.product_id);
            
            // تحديد الوحدة الافتراضية
            const defaultUnit = productData.selling_units.find(u => u.is_default);
            const firstUnit = productData.selling_units[0];
            
            if (defaultUnit) {
                this.selectUnit(index, defaultUnit.id);
            } else if (firstUnit) {
                this.selectUnit(index, firstUnit.id);
            } else {
                // إذا لم توجد وحدات بيع، استخدم السعر الأساسي
                item.unit_code = productData.base_unit;
                item.unit_label = productData.base_unit;
                item.conversion_factor = 1;
                item.price = productData.base_selling_price;
                item.quantity = 1;
                item.available_stock_in_unit = item.available_stock;
            }
            
            this.checkStockAvailability(index);
            this.calculateItemTotal(index);
        },

        // تحديد وحدة البيع
        selectUnit(index, sellingUnitId) {
            const item = this.items[index];
            
            if (!item.product_id || !sellingUnitId) {
                return;
            }

            const productData = this.productsData[item.product_id];
            const sellingUnit = productData.selling_units.find(u => u.id === parseInt(sellingUnitId));
            
            if (!sellingUnit) {
                return;
            }

            item.selling_unit_id = sellingUnit.id;
            item.unit_code = sellingUnit.unit_code;
            item.unit_label = sellingUnit.unit_label;
            item.conversion_factor = sellingUnit.conversion_factor;
            item.price = sellingUnit.selling_price > 0 ? sellingUnit.selling_price : productData.base_selling_price;
            
            // حساب الكمية المتاحة بالوحدة المختارة
            item.available_stock_in_unit = Math.floor(item.available_stock / item.conversion_factor);
            
            this.checkStockAvailability(index);
            this.calculateItemTotal(index);
        },

        // التحقق من توفر الكمية
        checkStockAvailability(index) {
            const item = this.items[index];
            if (!item.product_id || !this.warehouseId) {
                item.show_stock_warning = false;
                return;
            }

            // حساب الكمية المطلوبة بالوحدة الأساسية
            const quantity = item.quantity || 0;
            const requiredInBaseUnit = quantity * item.conversion_factor;
            item.show_stock_warning = requiredInBaseUnit > item.available_stock;
        },

        // تحديث كل الأصناف عند تغيير المخزن
        updateAllItemsStock() {
            this.items.forEach((item, index) => {
                if (item.product_id) {
                    item.available_stock = this.getAvailableStock(item.product_id);
                    if (item.conversion_factor) {
                        item.available_stock_in_unit = Math.floor(item.available_stock / item.conversion_factor);
                    }
                    this.checkStockAvailability(index);
                }
            });
        },

        // إعادة تعيين الصنف
        resetItem(index) {
            const item = this.items[index];
            item.product_name = '';
            item.selling_unit_id = '';
            item.unit_code = '';
            item.unit_label = '';
            item.conversion_factor = 1;
            item.price = 0;
            item.tax_rate = 0;
            item.discount = 0;
            item.available_stock = 0;
            item.available_stock_in_unit = 0;
            item.show_stock_warning = false;
        },

        addItem() {
            this.items.push({ 
                product_id: '', 
                product_name: '',
                selling_unit_id: '',
                unit_code: '',
                unit_label: '',
                conversion_factor: 1,
                quantity: 1, 
                price: 0, 
                tax_rate: 0,
                discount: 0,
                total: 0,
                available_stock: 0,
                available_stock_in_unit: 0,
                show_stock_warning: false
            });
        },

        removeItem(index) {
            if (this.items.length > 1) {
                this.items.splice(index, 1);
                this.calculateTotals();
            }
        },

        calculateItemTotal(index) {
            const item = this.items[index];
            const quantity = item.quantity || 0;
            const subtotal = quantity * item.price;
            const discount = subtotal * (item.discount / 100);
            const afterDiscount = subtotal - discount;
            const tax = afterDiscount * (item.tax_rate / 100);
            item.total = Math.round((afterDiscount + tax) * 100) / 100;
            
            this.checkStockAvailability(index);
            this.calculateTotals();
        },

        calculateTotals() {
            this.subtotal = Math.round(this.items.reduce((s, i) => {
                const quantity = i.quantity || 0;
                return s + (quantity * i.price);
            }, 0) * 100) / 100;
            
            this.totalDiscount = Math.round(this.items.reduce((s, i) => {
                const quantity = i.quantity || 0;
                const itemSubtotal = quantity * i.price;
                return s + (itemSubtotal * (i.discount / 100));
            }, 0) * 100) / 100;
            
            this.totalTax = Math.round(this.items.reduce((s, i) => {
                const quantity = i.quantity || 0;
                const itemSubtotal = quantity * i.price;
                const afterDiscount = itemSubtotal - (itemSubtotal * (i.discount / 100));
                return s + (afterDiscount * (i.tax_rate / 100));
            }, 0) * 100) / 100;
            
            this.grandTotal = Math.round((this.subtotal - this.totalDiscount + this.totalTax + Number(this.shipping || 0)) * 100) / 100;
            this.remaining = Math.round((this.grandTotal - (Number(this.paid) || 0)) * 100) / 100;
        },

        updatePaid() {
            this.remaining = Math.round((this.grandTotal - (Number(this.paid) || 0)) * 100) / 100;
        },

        // التحقق قبل الحفظ
        validateForm() {
            if (!this.warehouseId) {
                alert('⚠️ يجب اختيار المخزن أولاً');
                return false;
            }

            if (!this.customerId) {
                alert('⚠️ يجب اختيار العميل');
                return false;
            }

            // التحقق من وجود أصناف
            if (this.items.length === 0 || !this.items[0].product_id) {
                alert('⚠️ يجب إضافة صنف واحد على الأقل');
                return false;
            }

            // التحقق من وجود أصناف تتجاوز المخزون المتاح
            const outOfStockItems = this.items.filter(item => {
                if (!item.product_id) return false;
                const quantity = item.quantity || 0;
                const requiredInBaseUnit = quantity * item.conversion_factor;
                return requiredInBaseUnit > item.available_stock;
            });

            if (outOfStockItems.length > 0) {
                const itemsList = outOfStockItems.map(item => {
                    const quantity = item.quantity || 0;
                    const requiredInBaseUnit = quantity * item.conversion_factor;
                    return `- ${item.product_name} (${item.unit_label}): مطلوب ${quantity}، متوفر ${item.available_stock_in_unit}`;
                }).join('\n');
                
                alert(`⚠️ الأصناف التالية تتجاوز الكمية المتاحة:\n\n${itemsList}\n\nيرجى تعديل الكميات قبل الحفظ.`);
                return false;
            }

            return true;
        }
    }));
});
</script>
@endpush

@section('content')
<div x-data="invoiceForm" class="tf-page">
    <form method="POST" action="{{ route('invoices.sales.store') }}" 
          @submit="if (!validateForm()) { $event.preventDefault(); }">
        @csrf

        <div class="tf-card tf-section">
            <div class="tf-card-body" style="padding: 24px;">
                <div class="tf-grid-3">
                    <div>
                        <label class="tf-label">رقم الفاتورة</label>
                        <input type="text" readonly class="tf-input" style="background: var(--tf-surface2); font-weight: 800; color: var(--tf-indigo);"
                               value="SALE-{{ now()->format('YmdHis') }}">
                    </div>
                    <div>
                        <label class="tf-label">التاريخ *</label>
                        <input type="date" name="invoice_date" value="{{ date('Y-m-d') }}" class="tf-input" required>
                    </div>
                    <div>
                        <label class="tf-label">
                            <i class="fas fa-warehouse" style="color: var(--tf-indigo);"></i> المخزن *
                        </label>
                        <select name="warehouse_id" x-model="warehouseId" @change="updateAllItemsStock()" class="tf-select" required>
                            <option value="">اختر المخزن</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                        @error('warehouse_id')
                            <p style="color: var(--tf-red); font-size: 12px; margin-top: 4px;">{{ $message }}</p>
                        @enderror
                    </div>
                    <div style="grid-column: span 2;">
                        <label class="tf-label">
                            <i class="fas fa-user" style="color: var(--tf-green);"></i> العميل *
                        </label>
                        <select name="customer_id" x-model="customerId" class="tf-select" required>
                            <option value="">اختر العميل</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                            @endforeach
                        </select>
                        @error('customer_id')
                            <p style="color: var(--tf-red); font-size: 12px; margin-top: 4px;">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="tf-card tf-section">
            <div class="tf-card-head">
                <div class="tf-card-title">
                    <div class="tf-card-icon indigo"><i class="fas fa-boxes"></i></div>
                    <div>
                        <h3 class="tf-title-text">الأصناف</h3>
                        <p class="tf-title-sub">إضافة منتجات للفاتورة</p>
                    </div>
                </div>
                <button type="button" @click="addItem()" class="tf-btn tf-btn-primary">
                    <i class="fas fa-plus"></i> إضافة صنف
                </button>
            </div>
            <div class="tf-card-body">
                <div class="tf-table-wrapper">
                    <table class="tf-table">
                        <thead>
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th>الصنف</th>
                                <th style="width: 160px;">الوحدة</th>
                                <th style="text-align: center; background: var(--tf-green-soft); width: 100px;">المتاح</th>
                                <th style="width: 120px;">الكمية</th>
                                <th style="width: 110px;">السعر</th>
                                <th style="width: 80px;">خصم %</th>
                                <th style="width: 80px;">ضريبة %</th>
                                <th style="text-align: center; background: var(--tf-blue-soft); width: 120px;">الإجمالي</th>
                                <th style="width: 50px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(item, index) in items" :key="index">
                                <tr :class="{ 'warning-row': item.show_stock_warning }">
                                    <td><span style="font-weight: 800; color: var(--tf-text-m);" x-text="index + 1"></span></td>
                                    <td>
                                        <select :name="'items[' + index + '][product_id]'" 
                                                x-model="item.product_id"
                                                @change="loadProductData(index)"
                                                class="tf-select"
                                                :style="!warehouseId && item.product_id ? 'border-color: var(--tf-red); background: var(--tf-red-soft);' : ''">
                                            <option value="">اختر الصنف</option>
                                            @foreach($products as $product)
                                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                                            @endforeach
                                        </select>
                                        <p x-show="!warehouseId && item.product_id" style="color: var(--tf-red); font-size: 11px; margin-top: 4px; font-weight: 700;">
                                            ⚠️ اختر المخزن أولاً
                                        </p>
                                    </td>
                                    <td>
                                        <select x-show="item.product_id && getSellingUnits(item.product_id).length > 0"
                                                :name="'items[' + index + '][selling_unit_id]'"
                                                x-model="item.selling_unit_id"
                                                @change="selectUnit(index, item.selling_unit_id)"
                                                class="tf-select" style="background: var(--tf-violet-soft); border-color: var(--tf-violet);">
                                            <template x-for="unit in getSellingUnits(item.product_id)" :key="unit.id">
                                                <option :value="unit.id" x-text="unit.unit_label + ' (' + unit.conversion_factor + 'x)'"></option>
                                            </template>
                                        </select>
                                        <span x-show="item.product_id && item.unit_label" 
                                              class="tf-badge" style="background: var(--tf-violet-soft); color: var(--tf-violet);"
                                              x-text="item.unit_label"></span>
                                        <span x-show="!item.product_id" style="color: var(--tf-text-d); font-size: 11px;">-</span>
                                    </td>
                                    <td style="text-align: center; background: var(--tf-green-soft);">
                                        <div style="display: flex; flex-direction: column; align-items: center; gap: 2px;">
                                            <span x-show="item.product_id && warehouseId"
                                                  style="font-weight: 900; font-size: 18px;"
                                                  :style="{
                                                      'color': item.available_stock_in_unit >= item.quantity ? 'var(--tf-green)' : (item.available_stock_in_unit > 0 ? 'var(--tf-amber)' : 'var(--tf-red)'),
                                                  }"
                                                  x-text="item.available_stock_in_unit">
                                            </span>
                                            <span x-show="item.unit_label" style="font-size: 10px; color: var(--tf-text-m);" x-text="item.unit_label"></span>
                                            <i x-show="item.show_stock_warning" class="fas fa-exclamation-triangle" style="color: var(--tf-red);"></i>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="number" x-show="item.product_id" 
                                               :name="'items[' + index + '][quantity]'" 
                                               x-model="item.quantity" 
                                               @input="calculateItemTotal(index)"
                                               class="tf-input" style="text-align: center; font-weight: 700;"
                                               :style="item.show_stock_warning ? 'border-color: var(--tf-red); background: var(--tf-red-soft);' : ''"
                                               min="0.001" step="0.001" required>
                                        <span x-show="!item.product_id" style="color: var(--tf-text-d); font-size: 11px;">-</span>
                                    </td>
                                    <td>
                                        <input type="number" x-show="item.product_id"
                                               :name="'items[' + index + '][price]'" 
                                               x-model="item.price" 
                                               @input="calculateItemTotal(index)"
                                               class="tf-input" style="text-align: center; font-weight: 700;" 
                                               min="0" step="0.01" required>
                                        <span x-show="!item.product_id" style="color: var(--tf-text-d); font-size: 11px;">-</span>
                                    </td>
                                    <td>
                                        <input type="number" :name="'items[' + index + '][discount]'" x-model="item.discount" @input="calculateItemTotal(index)"
                                               class="tf-input" style="text-align: center;" min="0" max="100" placeholder="%">
                                    </td>
                                    <td>
                                        <input type="number" :name="'items[' + index + '][tax_rate]'" x-model="item.tax_rate" @input="calculateItemTotal(index)"
                                               class="tf-input" style="text-align: center;" min="0" max="100" placeholder="%">
                                    </td>
                                    <td style="text-align: center; background: var(--tf-blue-soft);">
                                        <span style="font-weight: 900; font-size: 16px; color: var(--tf-blue);" x-text="item.total.toFixed(2) + ' ج.م'"></span>
                                    </td>
                                    <td>
                                        <button type="button" @click="removeItem(index)" class="tf-action-btn del">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                    <input type="hidden" :name="'items[' + index + '][conversion_factor]'" x-model="item.conversion_factor">
                                    <input type="hidden" :name="'items[' + index + '][unit_code]'" x-model="item.unit_code">
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
                <div x-show="!warehouseId" style="margin-top: 16px; padding: 16px; background: var(--tf-amber-soft); border-radius: 14px; border-right: 4px solid var(--tf-amber); display: flex; align-items: center; gap: 12px;">
                    <i class="fas fa-exclamation-circle" style="color: var(--tf-amber); font-size: 24px;"></i>
                    <div>
                        <p style="font-weight: 800; color: var(--tf-text-h);">⚠️ يجب اختيار المخزن أولاً</p>
                        <p style="font-size: 13px; color: var(--tf-text-m);">لن تتمكن من رؤية الكميات المتاحة والوحدات للأصناف حتى تختار المخزن</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="tf-grid-3 tf-section">
            <div class="tf-total-box">
                <h4 style="font-size: 14px; font-weight: 800; color: var(--tf-text-h); margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-calculator" style="color: var(--tf-green);"></i> الحسابات
                </h4>
                <div class="tf-total-row">
                    <span class="tf-total-label">الإجمالي الجزئي:</span>
                    <span class="tf-total-value" x-text="subtotal.toFixed(2) + ' ج.م'"></span>
                </div>
                <div class="tf-total-row">
                    <span class="tf-total-label" style="color: var(--tf-red);">الخصم:</span>
                    <span class="tf-total-value red" x-text="'-' + totalDiscount.toFixed(2) + ' ج.م'"></span>
                </div>
                <div class="tf-total-row">
                    <span class="tf-total-label" style="color: var(--tf-blue);">الضريبة:</span>
                    <span class="tf-total-value blue" x-text="'+' + totalTax.toFixed(2) + ' ج.م'"></span>
                </div>
                <div class="tf-total-row" style="border-top: 2px solid var(--tf-border-soft); margin-top: 8px; padding-top: 12px;">
                    <span class="tf-total-label" style="font-size: 16px;">الإجمالي النهائي:</span>
                    <span class="tf-total-value tf-grand-total" x-text="grandTotal.toFixed(2) + ' ج.م'"></span>
                </div>
            </div>

            <div class="tf-total-box">
                <h4 style="font-size: 14px; font-weight: 800; color: var(--tf-text-h); margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-wallet" style="color: var(--tf-blue);"></i> الدفع
                </h4>
                <div style="margin-bottom: 16px;">
                    <label class="tf-label">المبلغ المدفوع</label>
                    <input type="number" name="paid" x-model="paid" @input="updatePaid()" class="tf-input" style="font-weight: 700; font-size: 18px;" min="0" step="0.01" placeholder="0.00">
                </div>
                <div>
                    <label class="tf-label">المتبقي</label>
                    <div style="font-size: 28px; font-weight: 900; padding: 12px; border-radius: 14px;"
                         :style="remaining > 0 ? 'background: var(--tf-amber-soft); color: var(--tf-amber);' : 'background: var(--tf-green-soft); color: var(--tf-green);'"
                         x-text="remaining.toFixed(2) + ' ج.م'"></div>
                </div>
            </div>

            <div class="tf-total-box">
                <h4 style="font-size: 14px; font-weight: 800; color: var(--tf-text-h); margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-sticky-note" style="color: var(--tf-violet);"></i> ملاحظات
                </h4>
                <textarea name="notes" class="tf-input" style="height: 120px; resize: none;" placeholder="أضف ملاحظات إضافية هنا..."></textarea>
            </div>
        </div>

        <div class="tf-card tf-section">
            <div class="tf-card-body" style="padding: 20px 24px; display: flex; justify-content: flex-end; gap: 12px;">
                <a href="{{ route('invoices.sales.index') }}" class="tf-btn tf-btn-secondary">
                    <i class="fas fa-times"></i> إلغاء
                </a>
                <button type="submit" class="tf-btn tf-btn-primary">
                    <i class="fas fa-save"></i> حفظ الفاتورة
                </button>
            </div>
        </div>
    </form>
</div>
@endsection