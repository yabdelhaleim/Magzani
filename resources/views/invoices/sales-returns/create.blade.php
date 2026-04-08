@extends('layouts.app')

@section('title', 'إضافة مرتجع مبيعات')
@section('page-title', 'إضافة مرتجع مبيعات')

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
            radial-gradient(ellipse 80% 60% at 10% -10%,  rgba(232,75,90,0.12) 0%, transparent 50%),
            radial-gradient(ellipse 60% 50% at 90% 110%, rgba(124,92,236,0.1) 0%, transparent 50%);
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

    .tf-card-head {
        display: flex; justify-content: space-between; align-items: center;
        padding: 20px 24px; border-bottom: 1px solid var(--tf-border-soft);
        background: linear-gradient(135deg, var(--tf-red-soft), var(--tf-surface2)); flex-wrap: wrap; gap: 12px;
    }
    .tf-card-title { display: flex; align-items: center; gap: 12px; }
    .tf-card-icon {
        width: 48px; height: 48px; border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        font-size: 18px;
        transition: transform .4s cubic-bezier(.34,1.56,.64,1);
    }
    .tf-card:hover .tf-card-icon { animation: iconBounce .6s ease; }
    .tf-card.red .tf-card-icon { background: var(--tf-red); color: var(--tf-surface); }
    .tf-card.blue .tf-card-icon { background: var(--tf-blue); color: var(--tf-surface); }

    .tf-title-text { font-size: 18px; font-weight: 800; color: var(--tf-text-h); }
    .tf-title-sub { font-size: 12px; color: var(--tf-text-m); font-weight: 600; }

    .tf-card-body { padding: 24px; }

    .tf-btn {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 12px 20px; border-radius: 14px;
        font-size: 14px; font-weight: 800; cursor: pointer;
        font-family: 'Cairo', sans-serif; text-decoration: none;
        transition: all .25s; border: none;
    }
    .tf-btn-primary {
        background: linear-gradient(135deg, var(--tf-red), #d63c4c);
        color: var(--tf-surface); border: none;
        box-shadow: 0 4px 16px rgba(232,75,90,0.35);
    }
    .tf-btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(232,75,90,0.45); }
    .tf-btn-secondary {
        background: var(--tf-surface); color: var(--tf-text-b);
        border: 1.5px solid var(--tf-border);
    }
    .tf-btn-secondary:hover { background: var(--tf-surface2); }
    .tf-btn:disabled { opacity: 0.5; cursor: not-allowed; }

    .tf-input, .tf-select {
        width: 100%; padding: 12px 16px;
        border: 1.5px solid var(--tf-border); border-radius: 14px;
        font-size: 14px; font-family: 'Cairo', sans-serif;
        color: var(--tf-text-h); background: var(--tf-surface);
        transition: all .25s; outline: none;
    }
    .tf-input:focus, .tf-select:focus {
        border-color: var(--tf-red);
        box-shadow: 0 0 0 3px rgba(232,75,90,0.12);
    }

    .tf-label {
        display: block; font-size: 12px; font-weight: 700;
        color: var(--tf-text-m); margin-bottom: 6px;
    }

    .tf-grid-2 { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
    @media (max-width: 600px) { .tf-grid-2 { grid-template-columns: 1fr; } }

    .tf-table-wrapper { overflow-x: auto; }
    .tf-table { width: 100%; border-collapse: collapse; }
    .tf-table thead th {
        padding: 12px 14px; text-align: right;
        font-size: 11px; font-weight: 800; color: var(--tf-text-m);
        text-transform: uppercase; letter-spacing: .5px;
        border-bottom: 1.5px solid var(--tf-border-soft);
        background: var(--tf-surface2); white-space: nowrap;
    }
    .tf-table tbody tr { transition: background .18s; }
    .tf-table tbody tr:hover { background: var(--tf-surface2); }
    .tf-table tbody td { padding: 12px 14px; border-bottom: 1px solid var(--tf-border-soft); vertical-align: middle; }

    .tf-row-item {
        display: grid; grid-template-columns: 50px 2fr 1fr 1fr 1fr 1fr 50px;
        gap: 12px; align-items: center;
        padding: 16px; border-radius: 14px; background: var(--tf-surface2);
        border: 1px solid var(--tf-border); margin-bottom: 10px;
    }
    .tf-row-item.warning { border-color: var(--tf-red); background: var(--tf-red-soft); }

    .tf-total-box {
        padding: 20px; border-radius: 16px;
        background: linear-gradient(135deg, var(--tf-red-soft), var(--tf-surface2));
        border: 1px solid var(--tf-red);
    }
    .tf-total-value {
        font-size: 32px; font-weight: 900; color: var(--tf-red);
    }

    .tf-alert {
        padding: 16px; border-radius: 14px; border-right: 4px solid;
        background: var(--tf-surface);
    }
    .tf-alert-warning { border-color: var(--tf-amber); background: var(--tf-amber-soft); }

    .tf-empty {
        display: flex; flex-direction: column; align-items: center;
        padding: 40px 24px; text-align: center;
    }
    .tf-empty-icon {
        width: 64px; height: 64px; border-radius: 16px;
        display: flex; align-items: center; justify-content: center;
        font-size: 24px; margin-bottom: 12px;
        background: var(--tf-surface2); color: var(--tf-text-m);
    }

    .tf-action-btn-del {
        display: inline-flex; align-items: center; justify-content: center;
        width: 36px; height: 36px; border-radius: 10px;
        cursor: pointer; transition: all .2s; border: none;
        background: var(--tf-red-soft); color: var(--tf-red);
    }
    .tf-action-btn-del:hover { background: var(--tf-red); color: var(--tf-surface); }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('returnForm', () => ({
        invoiceId: '',
        invoices: @json($invoicesData),
        
        items: [],
        total: 0,

        get selectedInvoice() {
            if (!this.invoiceId) return null;
            return this.invoices.find(inv => inv.id == this.invoiceId);
        },

        get availableItems() {
            if (!this.selectedInvoice) return [];
            return this.selectedInvoice.items.filter(item => item.available_quantity > 0);
        },

        onInvoiceChange() {
            this.items = [];
            this.total = 0;
        },

        addItem() {
            this.items.push({
                product_id: '',
                product_name: '',
                quantity: 1,
                price: 0,
                available_quantity: 0,
                total: 0,
                show_warning: false
            });
        },

        removeItem(index) {
            this.items.splice(index, 1);
            this.calculateTotal();
        },

        loadItemData(index) {
            const item = this.items[index];
            if (!item.product_id || !this.selectedInvoice) {
                item.price = 0;
                item.available_quantity = 0;
                item.show_warning = false;
                return;
            }

            const invoiceItem = this.selectedInvoice.items.find(
                i => i.product_id == item.product_id
            );

            if (invoiceItem) {
                item.product_name = invoiceItem.product_name;
                item.price = invoiceItem.price;
                item.available_quantity = invoiceItem.available_quantity;
                this.checkQuantity(index);
            }
        },

        checkQuantity(index) {
            const item = this.items[index];
            item.show_warning = item.quantity > item.available_quantity;
            this.calculateItemTotal(index);
        },

        calculateItemTotal(index) {
            const item = this.items[index];
            item.total = Math.round((item.quantity * item.price) * 100) / 100;
            this.calculateTotal();
        },

        calculateTotal() {
            this.total = Math.round(this.items.reduce((sum, item) => sum + item.total, 0) * 100) / 100;
        },

        validateForm() {
            if (!this.invoiceId) {
                alert('⚠️ يجب اختيار فاتورة أولاً');
                return false;
            }

            if (this.items.length === 0) {
                alert('⚠️ يجب إضافة صنف واحد على الأقل');
                return false;
            }

            const invalidItems = this.items.filter(item => 
                item.product_id && item.quantity > item.available_quantity
            );

            if (invalidItems.length > 0) {
                const itemsList = invalidItems.map(item => 
                    `- ${item.product_name}: مطلوب ${item.quantity}، متاح ${item.available_quantity}`
                ).join('\n');
                
                alert(`⚠️ الأصناف التالية تتجاوز الكمية المتاحة:\n\n${itemsList}`);
                return false;
            }

            return true;
        }
    }));
});
</script>
@endpush

@section('content')
<div x-data="returnForm" class="tf-page">
    <form method="POST" action="{{ route('invoices.sales-returns.store') }}" enctype="multipart/form-data" @submit="if (!validateForm()) { $event.preventDefault(); }">
        @csrf

        <div class="tf-card tf-section">
            <div class="tf-card-head">
                <div class="tf-card-title">
                    <div class="tf-card-icon red"><i class="fas fa-undo-alt"></i></div>
                    <div>
                        <h2 class="tf-title-text">إنشاء مرتجع مبيعات جديد</h2>
                        <p class="tf-title-sub">إرجاع منتجات من فاتورة مبيعات</p>
                    </div>
                </div>
            </div>
            <div class="tf-card-body">
                <div class="tf-grid-2">
                    <div>
                        <label class="tf-label"><i class="fas fa-file-invoice" style="color: var(--tf-indigo);"></i> فاتورة البيع *</label>
                        <select name="sales_invoice_id" x-model="invoiceId" @change="onInvoiceChange()" class="tf-select" required>
                            <option value="">اختر فاتورة</option>
                            <template x-for="invoice in invoices" :key="invoice.id">
                                <option :value="invoice.id" x-text="`${invoice.invoice_number} - ${invoice.customer_name}`"></option>
                            </template>
                        </select>
                        @error('sales_invoice_id')
                            <p style="color: var(--tf-red); font-size: 12px; margin-top: 4px;">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="tf-label"><i class="fas fa-calendar" style="color: var(--tf-blue);"></i> تاريخ المرتجع</label>
                        <input type="date" name="return_date" value="{{ date('Y-m-d') }}" class="tf-input">
                    </div>
                </div>

                <div x-show="!invoiceId" class="tf-alert tf-alert-warning" style="margin-top: 16px;">
                    <div style="display: flex; align-items: flex-start; gap: 12px;">
                        <i class="fas fa-exclamation-triangle" style="color: var(--tf-amber); font-size: 20px; margin-top: 2px;"></i>
                        <div>
                            <p style="font-weight: 800; color: var(--tf-text-h);">يجب اختيار فاتورة أولاً</p>
                            <p style="font-size: 13px; color: var(--tf-text-m);">لن تتمكن من إضافة أصناف حتى تختار فاتورة البيع</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="invoiceId && selectedInvoice" class="tf-card tf-section">
            <div class="tf-card-head">
                <div class="tf-card-title">
                    <div class="tf-card-icon blue"><i class="fas fa-file-invoice"></i></div>
                    <div>
                        <h3 class="tf-title-text">تفاصيل فاتورة البيع</h3>
                        <p class="tf-title-sub">معلومات الفاتورة المختارة</p>
                    </div>
                </div>
            </div>
            <div class="tf-card-body">
                <div class="tf-grid-2" style="margin-bottom: 20px;">
                    <div style="padding: 16px; border-radius: 14px; background: var(--tf-surface2);">
                        <div style="font-size: 11px; color: var(--tf-text-m);">رقم الفاتورة</div>
                        <div style="font-weight: 800; color: var(--tf-blue);" x-text="selectedInvoice.invoice_number"></div>
                    </div>
                    <div style="padding: 16px; border-radius: 14px; background: var(--tf-surface2);">
                        <div style="font-size: 11px; color: var(--tf-text-m);">العميل</div>
                        <div style="font-weight: 800; color: var(--tf-text-h);" x-text="selectedInvoice.customer_name"></div>
                    </div>
                </div>

                <h4 style="font-size: 14px; font-weight: 800; color: var(--tf-text-h); margin-bottom: 12px;">أصناف الفاتورة الأصلية</h4>
                <div class="tf-table-wrapper">
                    <table class="tf-table">
                        <thead>
                            <tr>
                                <th>الصنف</th>
                                <th>الكمية الأصلية</th>
                                <th>المرتجعة</th>
                                <th>المتاح</th>
                                <th>السعر</th>
                                <th>الإجمالي</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="item in selectedInvoice.items" :key="item.product_id">
                                <tr>
                                    <td style="font-weight: 600;" x-text="item.product_name"></td>
                                    <td x-text="item.original_quantity"></td>
                                    <td style="color: var(--tf-red); font-weight: 700;" x-text="item.returned_quantity"></td>
                                    <td>
                                        <span :style="item.available_quantity > 0 ? 'color: var(--tf-green); font-weight: 700;' : 'color: var(--tf-text-m);'" x-text="item.available_quantity"></span>
                                    </td>
                                    <td x-text="item.price.toFixed(2) + ' ج.م'"></td>
                                    <td style="font-weight: 700;" x-text="(item.original_quantity * item.price).toFixed(2) + ' ج.م'"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="tf-card tf-section" x-show="invoiceId">
            <div class="tf-card-head">
                <div class="tf-card-title">
                    <div class="tf-card-icon red"><i class="fas fa-boxes"></i></div>
                    <div>
                        <h3 class="tf-title-text">الأصناف المرتجعة</h3>
                        <p class="tf-title-sub">إضافة أصناف للإرجاع</p>
                    </div>
                </div>
                <button type="button" @click="addItem()" :disabled="availableItems.length === 0" class="tf-btn tf-btn-primary">
                    <i class="fas fa-plus"></i> إضافة صنف
                </button>
            </div>
            <div class="tf-card-body">
                <div x-show="invoiceId && availableItems.length === 0" class="tf-empty">
                    <div class="tf-empty-icon"><i class="fas fa-boxes"></i></div>
                    <p style="font-weight: 700; color: var(--tf-text-h);">لا توجد أصناف متاحة للإرجاع</p>
                    <p style="font-size: 13px; color: var(--tf-text-m);">جميع أصناف هذه الفاتورة تم إرجاعها بالكامل</p>
                </div>

                <div x-show="availableItems.length > 0">
                    <template x-for="(item, index) in items" :key="index">
                        <div class="tf-row-item" :class="{ 'warning': item.show_warning }">
                            <div style="font-weight: 800; color: var(--tf-text-m);" x-text="index + 1"></div>
                            <div>
                                <label class="tf-label">الصنف</label>
                                <select :name="'items[' + index + '][product_id]'" x-model="item.product_id" @change="loadItemData(index)" class="tf-select" required>
                                    <option value="">اختر الصنف</option>
                                    <template x-for="availItem in availableItems" :key="availItem.product_id">
                                        <option :value="availItem.product_id" x-text="availItem.product_name"></option>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <label class="tf-label">المتاح</label>
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <span :style="item.available_quantity > item.quantity ? 'color: var(--tf-green); font-weight: 800;' : (item.available_quantity > 0 ? 'color: var(--tf-amber); font-weight: 800;' : 'color: var(--tf-red); font-weight: 800;')" x-text="item.available_quantity"></span>
                                    <i x-show="item.show_warning" class="fas fa-exclamation-triangle" style="color: var(--tf-red);"></i>
                                </div>
                                <p x-show="item.show_warning" style="color: var(--tf-red); font-size: 10px; margin-top: 2px;">تجاوز المتاح!</p>
                            </div>
                            <div>
                                <label class="tf-label">الكمية</label>
                                <input type="number" :name="'items[' + index + '][quantity]'" x-model="item.quantity" @input="checkQuantity(index)" class="tf-input" :style="item.show_warning ? 'border-color: var(--tf-red); background: var(--tf-red-soft);' : ''" min="0.001" step="0.001" required>
                            </div>
                            <div>
                                <label class="tf-label">السعر</label>
                                <input type="number" :name="'items[' + index + '][price]'" x-model="item.price" @input="calculateItemTotal(index)" class="tf-input" readonly style="background: var(--tf-surface2);">
                            </div>
                            <div style="text-align: center;">
                                <label class="tf-label">الإجمالي</label>
                                <div style="font-weight: 800; color: var(--tf-green);" x-text="item.total.toFixed(2) + ' ج.م'"></div>
                            </div>
                            <button type="button" @click="removeItem(index)" class="tf-action-btn-del">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <div class="tf-grid-2 tf-section" x-show="invoiceId">
            <div class="tf-total-box">
                <label class="tf-label">إجمالي المرتجع</label>
                <div class="tf-total-value" x-text="total.toFixed(2) + ' ج.م'"></div>
            </div>
            <div>
                <label class="tf-label"><i class="fas fa-comment-alt" style="color: var(--tf-amber);"></i> سبب المرتجع / ملاحظات</label>
                <textarea name="notes" rows="3" class="tf-input" placeholder="مثال: عيب في المنتج / غير مطابق للمواصفات"></textarea>
                @error('notes')
                    <p style="color: var(--tf-red); font-size: 12px; margin-top: 4px;">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="tf-card tf-section" x-show="invoiceId">
            <div class="tf-card-body">
                <label class="tf-label"><i class="fas fa-image" style="color: var(--tf-violet);"></i> صور داعمة (اختياري)</label>
                <input type="file" name="images[]" multiple accept="image/*" class="tf-input" style="padding: 10px;">
                <p style="font-size: 12px; color: var(--tf-text-m); margin-top: 4px;">يمكنك رفع صور توضيح سبب المرتجع (الحد الأقصى 2MB لكل صورة)</p>
            </div>
        </div>

        <div class="tf-card tf-section" x-show="invoiceId">
            <div class="tf-card-body" style="display: flex; gap: 12px;">
                <button type="submit" class="tf-btn tf-btn-primary" style="flex: 1;">
                    <i class="fas fa-save"></i> حفظ المرتجع
                </button>
                <a href="{{ route('invoices.sales-returns.index') }}" class="tf-btn tf-btn-secondary">
                    <i class="fas fa-times"></i> إلغاء
                </a>
            </div>
        </div>
    </form>
</div>
@endsection