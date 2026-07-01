<div class="flex flex-col gap-0" x-data="{
    showPaymentModal: false,
    showHeldSales: false,
    showAddCustomerModal: false,
    init() {
        const searchInput = document.getElementById('pos-search-input');
        if (searchInput) {
            searchInput.focus();
            
            // Keep input focused unless we are actively using other inputs
            document.addEventListener('click', (e) => {
                const target = e.target;
                if (!['INPUT', 'SELECT', 'BUTTON', 'A', 'OPTION', 'TEXTAREA'].includes(target.tagName) && 
                    !target.closest('.cart-area') && 
                    !target.closest('.modal-content') &&
                    !this.showPaymentModal && 
                    !this.showAddCustomerModal) {
                    setTimeout(() => searchInput.focus(), 50);
                }
            });

            // Refocus after Livewire roundtrips
            document.addEventListener('livewire:initialized', () => {
                Livewire.hook('request', ({ respond }) => {
                    respond(() => {
                        setTimeout(() => {
                            if (!this.showPaymentModal && !this.showAddCustomerModal) {
                                searchInput.focus();
                            }
                        }, 100);
                    });
                });
            });

            window.addEventListener('keydown', (e) => {
                const active = document.activeElement;
                const isInputField = active && (active.tagName === 'INPUT' || active.tagName === 'TEXTAREA' || active.tagName === 'SELECT');
                
                // F1 = open payment modal
                if (e.key === 'F1') { e.preventDefault(); if ({{ count($cart) }} > 0) { this.showPaymentModal = true; @this.updateTotals(); } }
                // F2 = hold current sale
                if (e.key === 'F2') { e.preventDefault(); @this.holdSale(); }
                // F3 = show held sales panel
                if (e.key === 'F3') { e.preventDefault(); this.showHeldSales = !this.showHeldSales; }
                // Esc = close modals
                if (e.key === 'Escape') { this.showPaymentModal = false; this.showHeldSales = false; }
                
                // Quick barcode entry
                if (!isInputField && e.key.length === 1 && /^[a-zA-Z0-9]$/.test(e.key)) {
                    e.preventDefault();
                    searchInput.focus();
                    searchInput.value = e.key;
                    searchInput.dispatchEvent(new Event('input'));
                }
            });
        }
    }
}">

    <style>
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

        /* Glassmorphic Components */
        .glass-card {
            background: rgba(22, 33, 56, 0.6) !important;
            backdrop-filter: blur(12px) !important;
            -webkit-backdrop-filter: blur(12px) !important;
            border: 1px solid rgba(255, 255, 255, 0.05) !important;
            border-radius: 1.25rem !important;
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.2) !important;
        }
        .glass-input {
            background: rgba(10, 16, 28, 0.65) !important;
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
            color: #f1f5f9 !important;
            border-radius: 0.75rem !important;
            outline: none;
            transition: all 0.2s ease-in-out;
        }
        .glass-input:focus {
            border-color: #10b981 !important;
            box-shadow: 0 0 12px rgba(16, 185, 129, 0.3) !important;
        }
        .glass-bg {
            background: rgba(10, 16, 28, 0.4) !important;
            border: 1px solid rgba(255, 255, 255, 0.04) !important;
        }
        .product-card {
            background: rgba(22, 33, 56, 0.4) !important;
            backdrop-filter: blur(8px) !important;
            border: 1px solid rgba(255, 255, 255, 0.03) !important;
            border-radius: 1.25rem !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
        }
        .product-card:hover {
            transform: translateY(-4px) !important;
            border-color: rgba(16, 185, 129, 0.35) !important;
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.3) !important;
        }

        /* Scrollbar styles */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.01);
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.08);
            border-radius: 3px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.18);
        }
    </style>

    {{-- ==================== SHIFT STATUS BAR ==================== --}}
    @if($activeShift)
        <div style="background: linear-gradient(135deg,#0d9488,#0f766e); color:white; padding:12px 24px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:8px; font-size:12px; font-weight:800; border-bottom:1px solid rgba(255,255,255,0.05);">
            <div style="display:flex; align-items:center; gap:16px;">
                <span style="display:flex; align-items:center; gap:6px;">
                    <span style="width:8px;height:8px;background:#10b981;border-radius:50%;animation:pulse 1.5s infinite;display:inline-block;box-shadow:0 0 8px #10b981;"></span>
                    وردية مفتوحة — {{ $activeShift->user->name }}
                </span>
                <span style="opacity:0.85; border-right:1px solid rgba(255,255,255,0.2); padding-right:12px;">فتحت: {{ $activeShift->opened_at->format('H:i') }} | {{ $activeShift->duration }}</span>
                <span style="opacity:0.85; border-right:1px solid rgba(255,255,255,0.2); padding-right:12px;">مبيعات: {{ number_format($activeShift->total_sales, 2) }} ج.م ({{ $activeShift->sales_count }} فاتورة)</span>
            </div>
            <div style="display:flex; align-items:center; gap:8px;">
                @if($lastReceiptUrl)
                    <a href="#" onclick="window.open('{{ $lastReceiptUrl }}', '_blank', 'width=800,height=600'); return false;" style="background:rgba(255,255,255,0.08); color:white; padding:6px 14px; border-radius:20px; font-size:11px; font-weight:900; text-decoration:none; border:1px solid rgba(255,255,255,0.15); transition:all 0.2s; display:inline-flex; align-items:center; gap:4px;">
                        <i class="fas fa-print"></i> إعادة طباعة
                    </a>
                @endif
                <a href="{{ route('pos.xreport') }}" style="background:rgba(255,255,255,0.08); color:white; padding:6px 14px; border-radius:20px; font-size:11px; font-weight:900; text-decoration:none; border:1px solid rgba(255,255,255,0.15); transition:all 0.2s; display:inline-flex; align-items:center; gap:4px;">
                    <i class="fas fa-chart-line"></i> تقرير X
                </a>
                <a href="{{ route('pos.returns') }}" style="background:rgba(255,255,255,0.08); color:white; padding:6px 14px; border-radius:20px; font-size:11px; font-weight:900; text-decoration:none; border:1px solid rgba(255,255,255,0.15); transition:all 0.2s; display:inline-flex; align-items:center; gap:4px;">
                    <i class="fas fa-undo-alt"></i> المرتجعات
                </a>
                <a href="{{ route('pos.shift.close-view') }}" style="background:rgba(239,68,68,0.2); color:#f87171; padding:6px 14px; border-radius:20px; font-size:11px; font-weight:900; text-decoration:none; border:1px solid rgba(239,68,68,0.3); transition:all 0.2s; display:inline-flex; align-items:center; gap:4px;">
                    <i class="fas fa-lock"></i> إغلاق الوردية
                </a>
            </div>
        </div>
    @elseif($requireShift)
        <div style="background: linear-gradient(135deg,#dc2626,#991b1b); color:white; padding:12px 24px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:8px; font-size:12px; font-weight:800; border-bottom:1px solid rgba(255,255,255,0.05);">
            <span style="display:flex; align-items:center; gap:8px;">
                <i class="fas fa-exclamation-triangle" style="animation:pulse 1.2s infinite;"></i>
                لا توجد وردية مفتوحة! يجب فتح وردية قبل البدء في عمليات البيع.
            </span>
            <a href="{{ route('pos.shift.create') }}" style="background:white; color:#dc2626; padding:6px 16px; border-radius:20px; font-size:11px; font-weight:900; text-decoration:none; box-shadow:0 4px 12px rgba(0,0,0,0.15); display:inline-flex; align-items:center; gap:4px;">
                <i class="fas fa-play-circle"></i> فتح وردية جديدة
            </a>
        </div>
    @endif

    {{-- ==================== HELD SALES BAR ==================== --}}
    @if(count($heldSales) > 0)
    <div style="background:#0a101d; color:white; padding:10px 20px; display:flex; align-items:center; gap:10px; font-size:11px; font-weight:800; flex-wrap:wrap; border-bottom:1px solid rgba(255,255,255,0.03);">
        <span style="color:#fbbf24; display:flex; align-items:center; gap:5px; margin-left:8px;">
            <i class="fas fa-pause-circle"></i> الفواتير المعلقة ({{ count($heldSales) }}):
        </span>
        @foreach($heldSales as $held)
        <div style="display:flex; align-items:center; gap:6px; background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.07); border-radius:20px; padding:5px 12px; cursor:pointer; transition:all 0.2s;"
             onmouseover="this.style.background='rgba(255,255,255,0.08)'"
             onmouseout="this.style.background='rgba(255,255,255,0.04)'"
             wire:click="restoreHeldSale('{{ $held['id'] }}')">
            <i class="fas fa-shopping-cart" style="color:#38bdf8;"></i>
            <span style="color:#f1f5f9;">{{ $held['items_count'] }} أصناف — {{ number_format($held['total'], 2) }} ج.م</span>
            <span style="color:#64748b;">{{ $held['held_at'] }}</span>
            <span wire:click.stop="removeHeldSale('{{ $held['id'] }}')" style="color:#f87171; padding:0 4px; font-size:12px; display:inline-flex; align-items:center;" title="حذف الفاتورة المعلقة">
                <i class="fas fa-times"></i>
            </span>
        </div>
        @endforeach
        <span style="color:#475569; margin-right:auto; font-size:10px; font-weight:normal;">F2 تعليق | F3 السجل المعلق</span>
    </div>
    @endif

    {{-- ==================== MAIN POS LAYOUT ==================== --}}
    <div class="flex flex-col lg:flex-row gap-6 p-4 min-h-[calc(100vh-120px)]">

        {{-- ==================== RIGHT SIDE: PRODUCTS ==================== --}}
        <div class="flex-1 flex flex-col gap-4">

            {{-- Search Bar --}}
            <div class="glass-card p-4 flex flex-col sm:flex-row gap-4 items-center justify-between">
                <div class="relative flex-grow w-full">
                    <i class="fa-solid fa-barcode absolute right-4 top-1/2 -translate-y-1/2 text-emerald-500 text-lg"></i>
                    <input type="text"
                           id="pos-search-input"
                           wire:model.live.debounce.300ms="searchQuery"
                           wire:keydown.enter="scanBarcodeImmediate"
                           placeholder="امسح الباركود، أو اكتب اسم المنتج للبحث..."
                           class="w-full glass-input pr-12 pl-4 py-3 text-slate-100 outline-none transition font-semibold text-sm">
                </div>
                <div class="flex items-center gap-2 flex-shrink-0 text-xs bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 font-bold px-4 py-2.5 rounded-xl">
                    <i class="fa-solid fa-keyboard animate-pulse"></i>
                    <span>قارئ الباركود نشط</span>
                </div>
            </div>

            {{-- Categories --}}
            <div class="flex gap-2 overflow-x-auto pb-2 scrollbar-thin">
                <button wire:click="toggleFeaturedOnly"
                        class="px-5 py-2.5 rounded-xl font-bold text-xs transition border cursor-pointer flex items-center gap-1.5 {{ $onlyFeatured ? 'bg-amber-500 text-white border-amber-500 shadow-lg shadow-amber-500/20' : 'category-btn-inactive' }}">
                    <i class="fas fa-star text-amber-400"></i> المفضلة
                </button>
                <button wire:click="selectCategory(null)"
                        class="px-5 py-2.5 rounded-xl font-bold text-xs transition border cursor-pointer {{ is_null($selectedCategoryId) ? 'category-btn-active' : 'category-btn-inactive' }}">
                    الكل
                </button>
                @foreach($categories as $category)
                    <button wire:click="selectCategory({{ $category->id }})"
                            class="px-5 py-2.5 rounded-xl font-bold text-xs transition border cursor-pointer whitespace-nowrap {{ $selectedCategoryId == $category->id ? 'category-btn-active' : 'category-btn-inactive' }}"
                            style="{{ $selectedCategoryId == $category->id && $category->color ? 'background:'.$category->color.';box-shadow: 0 4px 14px 0 '.$category->color.'4D;border:none;' : '' }}">
                        @if($category->icon)
                            <i class="fas {{ $category->icon }} mr-1"></i>
                        @endif
                        {{ $category->name }}
                    </button>
                @endforeach
            </div>

            {{-- Products Grid --}}
            <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4 overflow-y-auto max-h-[64vh] pr-1">
                @foreach($products as $product)
                    <div wire:click="addToCart({{ $product->id }})"
                         class="product-card cursor-pointer overflow-hidden flex flex-col justify-between group relative select-none {{ $product->out_of_stock ? 'opacity-50' : '' }}">

                        {{-- Stock Badge --}}
                        <div class="absolute top-2 left-2 z-10">
                            @if($product->available_stock > 10)
                                <span class="bg-emerald-500/20 text-emerald-400 text-[10px] font-extrabold px-2 py-0.5 rounded-md border border-emerald-500/10">{{ $product->available_stock }} قطعة</span>
                            @elseif($product->available_stock > 0)
                                <span class="bg-amber-500/20 text-amber-400 text-[10px] font-extrabold px-2 py-0.5 rounded-md border border-amber-500/10">منخفض: {{ $product->available_stock }}</span>
                            @else
                                <span class="bg-rose-500/20 text-rose-400 text-[10px] font-extrabold px-2 py-0.5 rounded-md border border-rose-500/10">{{ $allowNegStock ? 'غير محدود' : 'نفذ' }}</span>
                            @endif
                        </div>

                        {{-- Image / Icon Header --}}
                        <div class="h-28 bg-gradient-to-br from-slate-800 to-slate-900/60 flex items-center justify-center relative overflow-hidden">
                            <i class="fa-solid fa-cube text-slate-600 text-4xl group-hover:scale-110 transition duration-300"></i>
                            @if($product->barcode)
                                <span class="absolute bottom-1 right-2 text-[9px] font-mono text-slate-400 bg-slate-900/80 border border-white/5 px-1 rounded">{{ $product->barcode }}</span>
                            @endif
                        </div>

                        {{-- Details --}}
                        <div class="p-3.5 flex flex-col gap-2 flex-grow justify-between">
                            <h4 class="font-bold text-slate-200 text-xs leading-relaxed line-clamp-2 h-8">{{ $product->name }}</h4>
                            <div class="flex justify-between items-center border-t border-white/5 pt-2 mt-1">
                                <span class="text-emerald-400 font-extrabold text-xs">{{ number_format($product->base_selling_price, 2) }} <span class="text-[9px] text-slate-400">ج.م</span></span>
                                <span class="text-[10px] text-slate-400 font-semibold bg-white/5 px-1.5 py-0.5 rounded">{{ $product->base_unit_label ?? 'قطعة' }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- ==================== LEFT SIDE: CART & CHECKOUT ==================== --}}
        <div class="w-full lg:w-[420px] xl:w-[460px] flex flex-col gap-4 flex-shrink-0 cart-area">

            {{-- Warehouse & Customer Selects --}}
            <div class="glass-card p-4 flex flex-col gap-3">
                <div class="flex gap-3">
                    <div class="flex-1 space-y-1">
                        <label class="text-[11px] font-extrabold text-slate-400 flex items-center gap-1">
                            <i class="fa-solid fa-warehouse text-indigo-400"></i> المستودع
                        </label>
                        <select wire:model.live="selectedWarehouseId" class="w-full glass-input px-2.5 py-2 text-xs font-bold text-slate-200 outline-none transition">
                            @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}" class="bg-slate-900">{{ $wh->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-1 space-y-1">
                        <label class="text-[11px] font-extrabold text-slate-400 flex items-center gap-1">
                            <i class="fa-solid fa-user text-indigo-400"></i> العميل
                        </label>
                        <div class="flex gap-1.5">
                            <select wire:model.live="selectedCustomerId" class="flex-grow glass-input px-2.5 py-2 text-xs font-bold text-slate-200 outline-none transition">
                                @foreach($customers as $cust)
                                    <option value="{{ $cust->id }}" class="bg-slate-900">{{ $cust->name }}</option>
                                @endforeach
                            </select>
                            <button type="button" @click="showAddCustomerModal = true" class="bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 rounded-xl px-3 py-2 text-xs font-extrabold hover:bg-indigo-500/25 transition cursor-pointer" title="إضافة عميل سريع">
                                <i class="fas fa-user-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>

                @if($selectedCustomer && $selectedCustomer->code !== 'CUST-CASH')
                    <div class="p-2.5 glass-bg rounded-xl flex justify-between items-center text-[10px] font-bold">
                        <div class="flex items-center gap-1">
                            <span class="text-slate-400">الرصيد الحالي:</span>
                            <span class="{{ $selectedCustomer->balance > 0 ? 'text-rose-400' : 'text-emerald-400' }}">
                                {{ number_format($selectedCustomer->balance, 2) }} ج.م
                            </span>
                        </div>
                        @if($selectedCustomer->credit_limit)
                            <div class="flex items-center gap-1">
                                <span class="text-slate-500">| الحد الائتماني:</span>
                                <span class="text-slate-300">{{ number_format($selectedCustomer->credit_limit, 2) }} ج.م</span>
                            </div>
                            @if($selectedCustomer->balance >= $selectedCustomer->credit_limit)
                                <span class="text-rose-400 animate-pulse"><i class="fas fa-exclamation-triangle"></i> تجاوز الحد!</span>
                            @endif
                        @endif
                    </div>
                @endif

                {{-- Payment Method Tabs --}}
                <div>
                    <label class="text-[11px] font-extrabold text-slate-400 mb-2 block">طريقة الدفع</label>
                    <div class="flex gap-2 flex-wrap">
                        @foreach([
                            'cash'     => ['label' => 'نقدي',    'icon' => 'fa-money-bill', 'color' => '#10b981'],
                            'card'     => ['label' => 'شبكة',    'icon' => 'fa-credit-card','color' => '#3b82f6'],
                            'credit'   => ['label' => 'آجل',     'icon' => 'fa-file-invoice','color' => '#f59e0b'],
                            'multiple' => ['label' => 'متعدد',   'icon' => 'fa-layer-group', 'color' => '#6366f1'],
                        ] as $method => $info)
                            <button wire:click="$set('paymentMethod','{{ $method }}')"
                                    class="flex-1 py-2 px-2 rounded-xl text-[11px] font-bold transition border flex items-center justify-center gap-1 cursor-pointer"
                                    style="{{ $paymentMethod === $method ? 'background:'.$info['color'].';color:white;border-color:'.$info['color'].';box-shadow:0 4px 12px '.$info['color'].'40;' : 'background:rgba(255,255,255,0.02);color:#94a3b8;border-color:rgba(255,255,255,0.05);' }}">
                                <i class="fas {{ $info['icon'] }}"></i> {{ $info['label'] }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Cart List --}}
            <div class="glass-card flex-grow flex flex-col overflow-hidden min-h-[280px]">
                <div class="p-4 border-b border-white/5 flex justify-between items-center bg-slate-900/30">
                    <span class="font-extrabold text-slate-200 text-xs flex items-center gap-2">
                        <i class="fa-solid fa-cart-shopping text-emerald-400"></i> سلة المبيعات
                        <span class="bg-emerald-500/10 text-emerald-400 text-[10px] font-bold px-2.5 py-0.5 rounded-full border border-emerald-500/20">{{ count($cart) }} أصناف</span>
                    </span>
                    <div class="flex items-center gap-2">
                        {{-- Hold Sale Button --}}
                        <button wire:click="holdSale"
                                title="F2 — تعليق الفاتورة"
                                class="flex items-center gap-1 text-[10px] text-amber-400 font-bold hover:text-amber-300 bg-amber-500/10 border border-amber-500/20 px-2.5 py-1.5 rounded-xl cursor-pointer transition">
                            <i class="fas fa-pause"></i> تعليق
                        </button>
                        <button wire:click="clearCart" class="text-[10px] text-rose-400 hover:text-rose-300 font-bold cursor-pointer bg-rose-500/10 border border-rose-500/20 px-2.5 py-1.5 rounded-xl transition">
                            تفريغ
                        </button>
                    </div>
                </div>

                <div class="flex-grow overflow-y-auto divide-y divide-white/5 px-4">
                    @forelse($cart as $index => $item)
                        <div class="py-3.5 flex flex-col gap-2">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h5 class="font-extrabold text-slate-100 text-xs leading-normal">{{ $item['name'] }}</h5>
                                    <span class="text-[10px] text-slate-500 font-mono bg-white/5 px-1.5 rounded">{{ $item['code'] }}</span>
                                </div>
                                <button wire:click="removeFromCart({{ $index }})" class="text-slate-500 hover:text-rose-400 transition cursor-pointer">
                                    <i class="fa-regular fa-trash-can text-sm"></i>
                                </button>
                            </div>
                            <div class="flex justify-between items-center gap-2 mt-1">
                                <div class="flex-shrink-0">
                                    @if(!empty($item['units']))
                                        <select wire:change="selectUnit({{ $index }}, $event.target.value)" class="glass-input text-[10px] font-bold text-slate-300 px-2 py-1 outline-none">
                                            <option value="" class="bg-slate-900">{{ $item['unit_code'] }} (أساسية)</option>
                                            @foreach($item['units'] as $unit)
                                                <option value="{{ $unit['id'] }}" {{ $item['selling_unit_id'] == $unit['id'] ? 'selected' : '' }} class="bg-slate-900">{{ $unit['unit_name'] }}</option>
                                            @endforeach
                                        </select>
                                    @else
                                        <span class="text-[11px] text-slate-400 font-bold bg-white/5 border border-white/5 px-2.5 py-1.5 rounded-lg">{{ $item['unit_code'] }}</span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-1.5 bg-slate-900/50 border border-white/5 rounded-xl px-2 py-0.5">
                                    <button wire:click="decrementQuantity({{ $index }})" class="w-6 h-6 flex items-center justify-center text-slate-400 hover:text-slate-100 font-bold text-xs bg-slate-800 rounded-lg border border-white/5 cursor-pointer shadow-sm">-</button>
                                    <input type="number"
                                           value="{{ $item['quantity'] }}"
                                           wire:change="updateQuantity({{ $index }}, $event.target.value)"
                                           class="w-10 text-center font-extrabold text-xs bg-transparent border-none outline-none text-slate-100">
                                    <button wire:click="incrementQuantity({{ $index }})" class="w-6 h-6 flex items-center justify-center text-slate-400 hover:text-slate-100 font-bold text-xs bg-slate-800 rounded-lg border border-white/5 cursor-pointer shadow-sm">+</button>
                                </div>
                                <div class="text-left min-w-[70px]">
                                    <span class="font-black text-slate-100 text-xs">{{ number_format($item['price'] * $item['quantity'], 2) }}</span>
                                    <span class="text-[9px] text-slate-500 block">{{ number_format($item['price'], 2) }}</span>
                                </div>
                            </div>
                            <div class="mt-1 flex items-center gap-1.5">
                                <i class="fas fa-edit text-slate-600 text-[10px]"></i>
                                <input type="text"
                                       placeholder="ملاحظة للصنف (مثال: بدون سكر)..."
                                       value="{{ $item['notes'] ?? '' }}"
                                       wire:change="updateItemNotes({{ $index }}, $event.target.value)"
                                       class="flex-grow glass-input px-2.5 py-1 text-[10px] text-slate-300 outline-none transition">
                            </div>
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center gap-3 py-14 text-center text-slate-500">
                            <i class="fa-solid fa-basket-shopping text-3xl text-slate-600"></i>
                            <span class="font-bold text-xs">سلة الكاشير فارغة</span>
                        </div>
                    @endforelse
                </div>

                {{-- Totals Footer --}}
                <div class="p-4 border-t border-white/5 bg-slate-900/30 flex flex-col gap-2">
                    <div class="flex justify-between items-center text-xs font-bold text-slate-400">
                        <span>المجموع الفرعي:</span>
                        <span class="text-slate-200">{{ number_format($this->subtotal, 2) }} ج.م</span>
                    </div>
                    <div class="flex justify-between items-center text-xs font-bold text-slate-400">
                        <span>الخصم العام:</span>
                        <div class="flex items-center gap-1">
                            <input type="number" wire:model.live="discount_value" class="w-12 glass-input px-1.5 py-0.5 text-center text-xs font-extrabold text-slate-200">
                            <select wire:model.live="discount_type" class="glass-input py-0.5 text-[10px] font-bold text-slate-300 outline-none">
                                <option value="fixed" class="bg-slate-900">ج.م</option>
                                <option value="percentage" class="bg-slate-900">%</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex justify-between items-center text-xs font-bold text-slate-400">
                        <span>الضريبة (%):</span>
                        <input type="number" wire:model.live="tax_rate" class="w-12 glass-input px-1.5 py-0.5 text-center text-xs font-extrabold text-slate-200">
                    </div>
                    <div class="flex justify-between items-center border-t border-white/5 pt-3 mt-1">
                        <span class="font-extrabold text-slate-200 text-sm">الإجمالي:</span>
                        <span class="font-black text-emerald-400 text-lg">{{ number_format($this->grand_total, 2) }} ج.م</span>
                    </div>
                </div>
            </div>

            {{-- Checkout Button --}}
            <button x-on:click="showPaymentModal = true; @this.updateTotals()"
                    {{ empty($cart) ? 'disabled' : '' }}
                    class="w-full py-4 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-sm transition shadow-lg shadow-emerald-600/20 cursor-pointer flex items-center justify-center gap-2 {{ empty($cart) ? 'opacity-40 cursor-not-allowed shadow-none' : '' }}">
                <i class="fa-solid fa-cash-register"></i>
                إتمام البيع
            </button>

            {{-- Quick Links --}}
            <div class="flex gap-2">
                @if(!$activeShift && $requireShift)
                    <a href="{{ route('pos.shift.create') }}" class="flex-1 py-2.5 bg-green-500/10 border border-green-500/20 text-green-400 rounded-xl text-xs font-bold text-center hover:bg-green-500/20 transition">
                        <i class="fas fa-play-circle ml-1"></i> فتح وردية
                    </a>
                @elseif($activeShift)
                    <a href="{{ route('pos.shift.close-view') }}" class="flex-1 py-2.5 bg-red-500/10 border border-red-500/20 text-red-400 rounded-xl text-xs font-bold text-center hover:bg-red-100/25 transition">
                        <i class="fas fa-lock ml-1"></i> إغلاق وردية
                    </a>
                @endif
                <a href="{{ route('pos.history') }}" class="flex-1 py-2.5 bg-slate-800/60 border border-white/5 text-slate-300 rounded-xl text-xs font-bold text-center hover:bg-slate-800 transition">
                    <i class="fas fa-history ml-1"></i> السجل
                </a>
                <a href="{{ route('pos.settings.index') }}" class="flex-1 py-2.5 bg-slate-800/60 border border-white/5 text-slate-300 rounded-xl text-xs font-bold text-center hover:bg-slate-800 transition">
                    <i class="fas fa-cog ml-1"></i> الإعدادات
                </a>
            </div>

            {{-- Shortcuts Legend --}}
            <div class="glass-bg rounded-xl p-2 px-3 flex items-center justify-between text-[10px] text-slate-400 font-bold">
                <span class="flex items-center gap-1"><kbd class="bg-slate-800 border border-white/5 px-1.5 rounded shadow-sm text-slate-300">F1</kbd> الدفع</span>
                <span class="flex items-center gap-1"><kbd class="bg-slate-800 border border-white/5 px-1.5 rounded shadow-sm text-slate-300">F2</kbd> تعليق</span>
                <span class="flex items-center gap-1"><kbd class="bg-slate-800 border border-white/5 px-1.5 rounded shadow-sm text-slate-300">F3</kbd> المعلقة</span>
                <span class="flex items-center gap-1"><kbd class="bg-slate-800 border border-white/5 px-1.5 rounded shadow-sm text-slate-300">Esc</kbd> إغلاق</span>
            </div>
        </div>

    </div>{{-- end main layout --}}

    {{-- ==================== PAYMENT MODAL ==================== --}}
    <div x-show="showPaymentModal"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/75 backdrop-blur-sm"
         x-transition
         style="display: none;">

        <div class="bg-slate-900 rounded-3xl p-6 w-full max-w-md shadow-2xl space-y-5 mx-4 border border-white/5"
             x-on:click.away="showPaymentModal = false">

            <div class="flex justify-between items-center border-b border-white/5 pb-3">
                <h3 class="font-extrabold text-slate-200 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-circle-dollar-to-slot text-emerald-400"></i> تفاصيل الدفع
                </h3>
                <button x-on:click="showPaymentModal = false" class="text-slate-400 hover:text-slate-200 cursor-pointer">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <div class="space-y-4">
                {{-- Grand Total Recap --}}
                <div class="p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-2xl flex justify-between items-center">
                    <span class="text-emerald-400 font-extrabold text-xs">الإجمالي المطلوب:</span>
                    <span class="text-emerald-400 font-black text-xl">{{ number_format($this->grand_total, 2) }} ج.م</span>
                </div>

                {{-- Cash Payment Details --}}
                @if($paymentMethod === 'cash')
                    <div class="space-y-3">
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-400">المبلغ المستلم من العميل</label>
                            <input type="number" wire:model.live="cashReceived" placeholder="0.00"
                                   class="w-full glass-input px-4 py-3 text-slate-100 font-extrabold text-sm transition">
                        </div>

                        {{-- Quick Cash Buttons --}}
                        <div class="grid grid-cols-5 gap-1.5">
                            @foreach([50, 100, 200, 500, 'exact'] as $amount)
                            <button type="button"
                                    wire:click="$set('cashReceived', {{ $amount === 'exact' ? $this->grand_total : $amount }})"
                                    class="py-2 rounded-xl text-xs font-extrabold border transition cursor-pointer
                                           {{ $amount === 'exact' ? 'bg-emerald-600 text-white border-emerald-600 hover:bg-emerald-700' : 'bg-slate-800 text-slate-300 border-white/5 hover:bg-slate-700' }}">
                                {{ $amount === 'exact' ? 'بالضبط' : $amount }}
                            </button>
                            @endforeach
                        </div>

                        @if(floatval($cashReceived) >= $this->grand_total && floatval($cashReceived) > 0)
                            <div class="p-3 bg-emerald-500/10 border border-emerald-500/20 rounded-xl flex justify-between items-center">
                                <span class="text-emerald-400 font-bold text-xs">الباقي للعميل:</span>
                                <span class="text-emerald-400 font-black text-lg">{{ number_format(floatval($cashReceived) - $this->grand_total, 2) }} ج.م</span>
                            </div>
                        @elseif(floatval($cashReceived) > 0)
                            <div class="p-3 bg-rose-500/10 border border-rose-500/20 rounded-xl flex justify-between items-center">
                                <span class="text-rose-400 font-bold text-xs">المبلغ ناقص:</span>
                                <span class="text-rose-400 font-black text-lg">{{ number_format($this->grand_total - floatval($cashReceived), 2) }} ج.م</span>
                            </div>
                        @endif
                    </div>
                @elseif($paymentMethod === 'card')
                    <div class="p-3 bg-blue-500/10 border border-blue-500/20 rounded-xl text-blue-400 text-xs font-bold">
                        <i class="fas fa-credit-card ml-2"></i>
                        سيتم خصم المبلغ كاملاً عبر البطاقة: {{ number_format($this->grand_total, 2) }} ج.م
                    </div>
                @elseif($paymentMethod === 'credit')
                    <div class="p-3 bg-amber-500/10 border border-amber-500/20 rounded-xl text-amber-400 text-xs font-bold">
                        <i class="fas fa-file-invoice ml-2"></i>
                        سيتم إضافة المبلغ لرصيد العميل الآجل.
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-400">الدفعة المقدمة (اختياري)</label>
                        <input type="number" wire:model.live="paid_amount" placeholder="0.00"
                               class="w-full glass-input px-4 py-2.5 text-slate-100 font-extrabold text-sm transition">
                    </div>
                @elseif($paymentMethod === 'multiple')
                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-400">نقدي</label>
                            <input type="number" wire:model.live="cashReceived" placeholder="0.00"
                                   class="w-full glass-input px-3 py-2.5 text-slate-100 font-extrabold text-sm transition">
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-400">شبكة</label>
                            <input type="number" wire:model.live="cardAmount" placeholder="0.00"
                                   class="w-full glass-input px-3 py-2.5 text-slate-100 font-extrabold text-sm transition">
                        </div>
                    </div>
                    @php $multiTotal = floatval($cashReceived) + floatval($cardAmount); @endphp
                    <div class="p-3 rounded-xl flex justify-between items-center {{ abs($multiTotal - $this->grand_total) < 0.01 ? 'bg-emerald-500/10 border border-emerald-500/20' : 'bg-rose-500/10 border border-rose-500/20' }}">
                        <span class="text-xs font-bold {{ abs($multiTotal - $this->grand_total) < 0.01 ? 'text-emerald-400' : 'text-rose-400' }}">المجموع المدفوع:</span>
                        <span class="font-black text-sm {{ abs($multiTotal - $this->grand_total) < 0.01 ? 'text-emerald-400' : 'text-rose-400' }}">{{ number_format($multiTotal, 2) }} ج.م</span>
                    </div>
                @endif

                {{-- Shipping & Other Charges --}}
                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="text-[11px] font-bold text-slate-400">تكلفة الشحن</label>
                        <input type="number" wire:model.live="shipping_cost" class="w-full glass-input px-3 py-2 text-xs font-bold transition">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[11px] font-bold text-slate-400">رسوم أخرى</label>
                        <input type="number" wire:model.live="other_charges" class="w-full glass-input px-3 py-2 text-xs font-bold transition">
                    </div>
                </div>

                {{-- Notes --}}
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-400">ملاحظات الفاتورة</label>
                    <textarea wire:model.live="notes" rows="2" placeholder="ملاحظات..." class="w-full glass-input px-4 py-2.5 text-slate-100 text-xs transition"></textarea>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex gap-3 pt-3 border-t border-white/5">
                <button wire:click="submitInvoice"
                        x-on:click="showPaymentModal = false"
                        class="flex-1 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-extrabold text-xs transition shadow-lg shadow-emerald-600/20 cursor-pointer flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-circle-check"></i> تأكيد وحفظ الفاتورة
                </button>
                <button x-on:click="showPaymentModal = false"
                        class="flex-1 py-3 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl font-bold text-xs transition cursor-pointer">
                    إلغاء
                </button>
            </div>
        </div>
    </div>

    {{-- ==================== QUICK CUSTOMER ADD MODAL ==================== --}}
    <div x-show="showAddCustomerModal"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/75 backdrop-blur-sm"
         x-transition
         style="display: none;">

        <div class="bg-slate-900 rounded-3xl p-6 w-full max-sm shadow-2xl space-y-5 mx-4 border border-white/5"
             x-on:click.away="showAddCustomerModal = false"
             @close-customer-modal.window="showAddCustomerModal = false">

            <div class="flex justify-between items-center border-b border-white/5 pb-3">
                <h3 class="font-extrabold text-slate-200 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-user-plus text-emerald-400"></i> إضافة عميل سريع
                </h3>
                <button x-on:click="showAddCustomerModal = false" class="text-slate-400 hover:text-slate-200 cursor-pointer">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <div class="space-y-4">
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-400">اسم العميل <span class="text-rose-400">*</span></label>
                    <input type="text" wire:model="newCustomerName" placeholder="الاسم الكامل"
                           class="w-full glass-input px-4 py-2.5 text-slate-100 font-bold text-sm transition">
                    @error('newCustomerName') <span class="text-rose-400 text-[10px] font-bold">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-400">رقم الهاتف</label>
                    <input type="text" wire:model="newCustomerPhone" placeholder="رقم الجوال"
                           class="w-full glass-input px-4 py-2.5 text-slate-100 font-bold text-sm transition">
                    @error('newCustomerPhone') <span class="text-rose-400 text-[10px] font-bold">{{ $message }}</span> @enderror
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex gap-3 pt-3 border-t border-white/5">
                <button wire:click="quickAddCustomer"
                        class="flex-1 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-extrabold text-xs transition shadow-lg shadow-emerald-600/20 cursor-pointer flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-save"></i> حفظ وتحديد العميل
                </button>
                <button x-on:click="showAddCustomerModal = false"
                        class="flex-1 py-3 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl font-bold text-xs transition cursor-pointer">
                    إلغاء
                </button>
            </div>
        </div>
    </div>

    {{-- ==================== MANAGER PIN MODAL ==================== --}}
    @if($showManagerPinModal)
        <div class="fixed inset-0 z-[100] flex items-center justify-center bg-black/75 backdrop-blur-sm" x-transition>
            <div class="bg-slate-900 rounded-3xl p-6 w-full max-w-sm shadow-2xl space-y-4 mx-4 border border-white/5">
                <div class="flex justify-between items-center border-b border-white/5 pb-3">
                    <h3 class="font-extrabold text-slate-200 text-sm flex items-center gap-2">
                        <i class="fa-solid fa-user-shield text-amber-400"></i> تصريح المدير للخصم
                    </h3>
                    <button wire:click="$set('showManagerPinModal', false)" class="text-slate-400 hover:text-slate-200 cursor-pointer">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>
                <div class="space-y-3">
                    <p class="text-xs text-slate-400 font-medium">الخصم المدخل يتجاوز 10%، يرجى إدخل الرمز السري للمدير للموافقة على العملية (الرمز الافتراضي: 1234):</p>
                    <input type="password"
                           wire:model="managerPinInput"
                           placeholder="رمز PIN المدير"
                           class="w-full glass-input px-4 py-2.5 text-center tracking-widest text-slate-100 font-extrabold text-sm transition {{ $managerPinError ? 'border-rose-400 focus:border-rose-500' : '' }}">
                    @if($managerPinError)
                        <span class="text-rose-400 text-[10px] font-bold block text-center">{{ $managerPinError }}</span>
                    @endif
                </div>
                <div class="flex gap-3 pt-3 border-t border-white/5">
                    <button wire:click="verifyManagerPin"
                            class="flex-1 py-3 bg-amber-500 hover:bg-amber-600 text-white rounded-xl font-extrabold text-xs transition shadow-lg shadow-amber-500/20 cursor-pointer">
                        تأكيد التصريح
                    </button>
                    <button wire:click="$set('showManagerPinModal', false)"
                            class="flex-1 py-3 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl font-bold text-xs transition cursor-pointer">
                        إلغاء
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>

@push('scripts')
<script>
    document.addEventListener('livewire:init', () => {
        // Sound player using Web Audio API (No files needed!)
        const playBeep = (type) => {
            try {
                const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                const oscillator = audioCtx.createOscillator();
                const gainNode = audioCtx.createGain();

                oscillator.connect(gainNode);
                gainNode.connect(audioCtx.destination);

                if (type === 'success') {
                    // Quick high beep for successful scan
                    oscillator.type = 'sine';
                    oscillator.frequency.setValueAtTime(1200, audioCtx.currentTime); // 1200Hz
                    gainNode.gain.setValueAtTime(0.15, audioCtx.currentTime);
                    oscillator.start();
                    oscillator.stop(audioCtx.currentTime + 0.08); // 80ms duration
                } else if (type === 'error') {
                    // Double low buzz for error
                    oscillator.type = 'sawtooth';
                    oscillator.frequency.setValueAtTime(220, audioCtx.currentTime); // 220Hz (Low buzz)
                    gainNode.gain.setValueAtTime(0.15, audioCtx.currentTime);
                    oscillator.start();
                    oscillator.stop(audioCtx.currentTime + 0.25); // 250ms duration
                }
            } catch (e) {
                console.error("Web Audio failed:", e);
            }
        };

        Livewire.on('play-sound', (event) => {
            const data = event[0] || event;
            playBeep(data.type);
        });

        Livewire.on('alert', (event) => {
            const data = event[0] || event;
            if (window.Swal) {
                Swal.fire({ icon: data.type, title: data.message, timer: 3000, showConfirmButton: false });
            } else {
                alert(data.message);
            }
        });

        Livewire.on('print-receipt', (event) => {
            const data = event[0] || event;
            const printUrl = `/invoices/sales/${data.invoiceId}/print`;
            window.open(printUrl, '_blank', 'width=800,height=600');
        });
    });
</script>
@endpush
