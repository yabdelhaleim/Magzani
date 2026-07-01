@section('title', 'مرتجعات الكاشير')
@section('page-title', 'مرتجعات الـ POS')

@push('styles')
<style>
    :root {
        --tf-bg:          transparent;
        --tf-surface:     rgba(22, 33, 56, 0.6);
        --tf-surface2:    rgba(10, 16, 28, 0.55);
        --tf-border:      rgba(255, 255, 255, 0.06);
        --tf-indigo:      #6366f1;
        --tf-indigo-soft: rgba(99, 102, 241, 0.15);
        --tf-green:       #10b981;
        --tf-green-soft:  rgba(16, 185, 129, 0.15);
        --tf-red:         #ef4444;
        --tf-red-soft:    rgba(239, 68, 68, 0.15);
        --tf-amber:       #f59e0b;
        --tf-amber-soft:  rgba(245, 158, 11, 0.15);
        --tf-text-h:      #f1f5f9;
        --tf-text-b:      #cbd5e1;
        --tf-text-m:      #94a3b8;
        --tf-shadow-card: 0 8px 32px 0 rgba(0, 0, 0, 0.25);
        --radius-lg:      24px;
        --radius-md:      16px;
    }

    /* Scoped Dark Mode Overrides for Immersive Cashier Experience */
    body, .main-content, #mainContent {
        background: radial-gradient(circle at top right, #131e35, #080d1a) !important;
        color: #e2e8f0 !important;
    }
    .sidebar {
        background: #070b14 !important;
        border-left: 1px solid rgba(255, 255, 255, 0.03) !important;
    }
    .sidebar * {
        color: rgba(226, 232, 240, 0.65) !important;
    }
    .sidebar .nav-item.active, .sidebar .nav-item.active * {
        background: rgba(16, 185, 129, 0.1) !important;
        color: #10b981 !important;
        border-right: 3px solid #10b981 !important;
    }
    .sidebar .nav-section-label {
        color: rgba(226, 232, 240, 0.3) !important;
    }
    .sidebar .nav-divider {
        border-color: rgba(255, 255, 255, 0.03) !important;
    }
    .main-header {
        background: #070b14 !important;
        border-bottom: 1px solid rgba(255, 255, 255, 0.03) !important;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25) !important;
    }
    .main-header * {
        color: #e2e8f0 !important;
    }
    .main-footer {
        background: #070b14 !important;
        border-top: 1px solid rgba(255, 255, 255, 0.03) !important;
        color: rgba(226, 232, 240, 0.35) !important;
    }

    .pos-returns-page {
        min-height: 100vh;
        background: var(--tf-bg);
        padding: 24px;
        backdrop-filter: blur(12px) !important;
        -webkit-backdrop-filter: blur(12px) !important;
    }

    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(15px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .animated { animation: fadeUp 0.3s ease both; }

    /* Header */
    .page-header { max-width: 900px; margin: 0 auto 24px; }
    .page-header h1 { font-size: 22px; font-weight: 900; color: var(--tf-text-h); display: flex; align-items: center; gap: 10px; }
    .page-header h1 i { color: var(--tf-red); }
    .page-header p { font-size: 13px; color: var(--tf-text-m); font-weight: 600; margin: 4px 0 0 28px; }

    /* Search Box */
    .search-card {
        background: var(--tf-surface);
        border: 1px solid var(--tf-border);
        border-radius: var(--radius-md);
        box-shadow: var(--tf-shadow-card);
        padding: 24px; max-width: 900px; margin: 0 auto 24px;
        display: flex; gap: 12px; align-items: flex-end;
    }
    .search-input-group { flex: 1; }
    .search-input-group label { display: block; font-size: 12px; font-weight: 800; color: var(--tf-text-b); margin-bottom: 8px; }
    .search-input-group input {
        width: 100%; border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 12px;
        padding: 12px 16px; font-size: 16px; font-weight: 700; color: #f1f5f9;
        background: rgba(10, 16, 28, 0.65) !important;
        outline: none; transition: all 0.2s ease-in-out;
    }
    .search-input-group input:focus {
        border-color: var(--tf-indigo);
        box-shadow: 0 0 12px rgba(99, 102, 241, 0.3) !important;
    }
    .btn-search {
        padding: 13px 24px; background: var(--tf-indigo); color: white; border: none; border-radius: 12px;
        font-size: 14px; font-weight: 900; cursor: pointer; transition: all 0.2s; white-space: nowrap;
    }
    .btn-search:hover { background: #3d51c5; }

    /* Invoice Details */
    .invoice-card {
        background: var(--tf-surface);
        border: 1px solid var(--tf-border);
        border-radius: var(--radius-md);
        box-shadow: var(--tf-shadow-card);
        max-width: 900px; margin: 0 auto 24px; overflow: hidden;
    }
    .invoice-header {
        background: var(--tf-surface2); padding: 16px 20px; border-bottom: 1px solid var(--tf-border);
        display: flex; justify-content: space-between; align-items: center;
    }
    .invoice-header h2 { font-size: 15px; font-weight: 900; color: var(--tf-text-h); margin: 0; }
    .invoice-meta { display: flex; gap: 16px; font-size: 12px; font-weight: 700; color: var(--tf-text-m); }
    .invoice-meta span { background: rgba(255, 255, 255, 0.04); padding: 4px 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.06); color: #cbd5e1; }

    table { width: 100%; border-collapse: collapse; }
    thead th { background: var(--tf-surface2); padding: 12px 16px; text-align: right; font-size: 11px; font-weight: 800; color: var(--tf-text-m); border-bottom: 2px solid var(--tf-border); }
    tbody td { padding: 16px; border-bottom: 1px solid var(--tf-border); font-size: 13px; font-weight: 700; color: var(--tf-text-b); }
    
    .invoice-card input[type="text"] {
        background: rgba(10, 16, 28, 0.65) !important;
        border: 1px solid rgba(255, 255, 255, 0.08) !important;
        color: #f1f5f9 !important;
        outline: none;
    }
    .invoice-card input[type="text"]:focus {
        border-color: var(--tf-indigo) !important;
        box-shadow: 0 0 12px rgba(99, 102, 241, 0.3) !important;
    }

    .qty-input {
        width: 80px; border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 8px; padding: 8px;
        background: rgba(10, 16, 28, 0.65) !important;
        text-align: center; font-size: 14px; font-weight: 900; color: #f1f5f9; outline: none;
    }
    .qty-input:focus {
        border-color: var(--tf-red);
        box-shadow: 0 0 12px rgba(239, 68, 68, 0.3) !important;
    }
    .btn-confirm {
        background: linear-gradient(135deg, #dc2626, #b91c1c); color: white; border: none; border-radius: 12px;
        padding: 16px 32px; font-size: 15px; font-weight: 900; cursor: pointer; transition: all 0.2s; width: 100%;
        display: flex; align-items: center; justify-content: center; gap: 10px; margin-top: 16px;
    }
    .btn-confirm:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(220,38,38,0.3); }

    /* Alert overlay */
    .toast-alert {
        position: fixed; bottom: 30px; left: 30px; z-index: 9999;
        padding: 16px 24px; border-radius: 12px; font-size: 14px; font-weight: 800;
        display: flex; align-items: center; gap: 12px; box-shadow: 0 12px 40px rgba(0,0,0,0.35);
        animation: fadeUp 0.3s ease both;
    }
    .toast-alert.error { background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3); color: #f87171; }
    .toast-alert.success { background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.3); color: #34d399; }
</style>
@endpush

<div class="pos-returns-page">
    <div class="page-header animated">
        <h1><i class="fas fa-undo-alt"></i> مرتجعات نقطة البيع (POS)</h1>
        <p>ابحث عن الفاتورة برقمها المرجعي ثم اختر الكميات المراد إرجاعها.</p>
    </div>

    <!-- Search Box -->
    <div class="search-card animated" style="animation-delay: 0.1s;">
        <div class="search-input-group">
            <label>رقم الفاتورة أو المرجع</label>
            <input type="text" wire:model="searchQuery" wire:keydown.enter="searchInvoice" placeholder="مثال: INV-202406... أو مرجع الإيصال" autofocus>
        </div>
        <button wire:click="searchInvoice" class="btn-search">
            <i class="fas fa-search ml-1"></i> بحث الفاتورة
        </button>
    </div>

    <!-- Invoice Details -->
    @if($invoiceDetails)
    <div class="invoice-card animated" style="animation-delay: 0.2s;">
        <div class="invoice-header">
            <h2>تفاصيل الفاتورة: {{ $invoiceDetails['invoice_number'] }}</h2>
            <div class="invoice-meta">
                <span><i class="fas fa-user ml-1 text-indigo-500"></i> {{ $invoiceDetails['customer_name'] }}</span>
                <span><i class="fas fa-calendar-alt ml-1 text-indigo-500"></i> {{ \Carbon\Carbon::parse($invoiceDetails['date'])->format('d/m/Y') }}</span>
                <span><i class="fas fa-money-bill-wave ml-1 text-green-500"></i> الإجمالي: {{ number_format($invoiceDetails['total'], 2) }} ج.م</span>
            </div>
        </div>
        <div style="padding: 20px;">
            <table>
                <thead>
                    <tr>
                        <th>المنتج</th>
                        <th>السعر</th>
                        <th>الكمية المباعة</th>
                        <th>مرتجع مسبقاً</th>
                        <th>المتاح للإرجاع</th>
                        <th>الكمية المراد إرجاعها الآن</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoiceDetails['items'] as $item)
                    <tr>
                        <td style="color:var(--tf-text-h); font-weight:900;">{{ $item['product_name'] }}</td>
                        <td>{{ number_format($item['unit_price'], 2) }} ج.م</td>
                        <td>{{ $item['quantity_sold'] }}</td>
                        <td style="color:var(--tf-red);">{{ $item['quantity_returned_previously'] }}</td>
                        <td style="color:var(--tf-green); font-weight:900;">{{ $item['available_qty'] }}</td>
                        <td>
                            @if($item['available_qty'] > 0)
                                <input type="number" wire:model="returnQuantities.{{ $item['id'] }}" min="0" max="{{ $item['available_qty'] }}" step="any" class="qty-input">
                            @else
                                <span style="color:var(--tf-text-m); font-size:11px;">تم استرجاع كامل الكمية</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div style="margin-top: 24px; padding-top: 20px; border-top: 1px solid var(--tf-border);">
                <label style="display:block; font-size:12px; font-weight:800; color:var(--tf-text-b); margin-bottom:8px;">سبب المرتجع / ملاحظات</label>
                <input type="text" wire:model="returnNotes" style="width:100%; border:2px solid var(--tf-border); border-radius:12px; padding:12px 16px; font-size:14px; font-weight:700; color:var(--tf-text-h); outline:none;" placeholder="مثال: العميل لم يعجبه المنتج، منتج تالف...">
                
                <button wire:click="confirmReturn" wire:loading.attr="disabled" class="btn-confirm">
                    <span wire:loading.remove wire:target="confirmReturn">
                        <i class="fas fa-check-circle"></i> تأكيد إرجاع الكميات المحددة (سحب من الوردية)
                    </span>
                    <span wire:loading wire:target="confirmReturn">
                        <i class="fas fa-circle-notch fa-spin"></i> جاري التنفيذ...
                    </span>
                </button>
            </div>
        </div>
    </div>
    @endif

</div>

@push('scripts')
<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('pos-alert', (event) => {
            const data = event[0];
            const div = document.createElement('div');
            div.className = `toast-alert ${data.type}`;
            div.innerHTML = `<i class="fas ${data.type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'}"></i> ${data.message}`;
            document.body.appendChild(div);
            
            setTimeout(() => {
                div.style.opacity = '0';
                div.style.transform = 'translateY(20px)';
                div.style.transition = 'all 0.3s ease';
                setTimeout(() => div.remove(), 300);
            }, 3000);
        });
    });
</script>
@endpush
