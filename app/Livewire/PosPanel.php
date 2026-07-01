<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Customer;
use App\Models\PosShift;
use App\Models\PosSetting;
use App\Models\Product;
use App\Models\Warehouse;
use App\Services\InvoiceService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class PosPanel extends Component
{
    // ==================== Cart ====================
    public $cart = [];
    public $searchQuery = '';
    public $selectedCategoryId = null;

    // ==================== Hold Sales ====================
    public $heldSales = [];   // [{id, label, cart, customer_id, discount_value, ...}]

    // ==================== Favorites & Options ====================
    public $onlyFeatured = false;
    public $showManagerPinModal = false;
    public $managerPinInput = '';
    public $pendingDiscountValue = 0;
    public $pendingDiscountType = 'fixed';
    public $managerPinError = '';

    // ==================== Session / Shift ====================
    public $currentShiftId = null;
    public $requireShift    = false;
    public $allowNegStock   = false;
    public $autoPrint       = false;
    public $posName         = '';

    // ==================== Selects ====================
    public $selectedWarehouseId = null;
    public $selectedCustomerId  = null;

    // ==================== Payment ====================
    public $paymentMethod  = 'cash';   // cash | card | credit | multiple
    public $cashReceived   = 0;
    public $cardAmount     = 0;
    public $change         = 0;

    // ==================== Financials ====================
    public $discount_value = 0;
    public $discount_type  = 'fixed';
    public $tax_rate       = 0;
    public $shipping_cost  = 0;
    public $other_charges  = 0;
    public $paid_amount    = 0;
    public $notes          = '';
    public $auto_match_paid = true;

    // ==================== Customer Quick Add ====================
    public $newCustomerName  = '';
    public $newCustomerPhone = '';

    // ==================== UI State ====================
    public $lastInvoiceId    = null;
    public $lastReceiptUrl   = null;
    public $showPaymentModal = false;

    // ==================== Mount ====================

    public function mount()
    {
        // 1. Load POS Settings
        $settings = PosSetting::getSolo();
        $this->requireShift  = $settings->require_shift;
        $this->allowNegStock = $settings->allow_negative_stock;
        $this->autoPrint     = $settings->auto_print_receipt;
        $this->posName       = $settings->pos_name;
        $this->paymentMethod = $settings->default_payment_method;

        // 2. Check for active shift
        $activeShift = PosShift::getActiveShift();
        if ($activeShift) {
            $this->currentShiftId = $activeShift->id;

            // Find last invoice for this shift to support immediate reprint
            $lastInvoice = \App\Models\SalesInvoice::where('shift_id', $activeShift->id)
                ->where('source', 'pos')
                ->whereNull('deleted_at')
                ->latest('id')
                ->first();
            if ($lastInvoice) {
                $this->lastReceiptUrl = route('invoices.sales.print', $lastInvoice->id);
            }
        }

        // 3. Get default warehouse — prefer POS settings, else first active
        $warehouseId = $settings->default_warehouse_id;
        if ($warehouseId && Warehouse::where('id', $warehouseId)->where('is_active', true)->exists()) {
            $this->selectedWarehouseId = $warehouseId;
        } else {
            $defaultWarehouse = Warehouse::where('is_active', true)
                ->where('status', 'active')
                ->first();

            if (!$defaultWarehouse) {
                $defaultWarehouse = Warehouse::firstOrCreate(
                    ['code' => 'WH-DEFAULT'],
                    [
                        'name'      => 'المستودع الافتراضي',
                        'status'    => 'active',
                        'is_active' => true,
                    ]
                );
            }
            $this->selectedWarehouseId = $defaultWarehouse->id;
        }

        // 4. Get or create default cash customer
        $defaultCustomer = Customer::firstOrCreate(
            ['code' => 'CUST-CASH'],
            [
                'name'      => 'عميل نقدي',
                'phone'     => '0000000000',
                'is_active' => true,
                'balance'   => 0,
            ]
        );
        $this->selectedCustomerId = $defaultCustomer->id;
    }

    // ==================== Shift Guards ====================

    /**
     * التحقق إذا كان المستخدم يحتاج لوردية وليس لديه وردية مفتوحة.
     */
    public function needsShift(): bool
    {
        return $this->requireShift && !$this->currentShiftId;
    }

    // ==================== Search / Barcode ====================

    public function updatedSearchQuery()
    {
        if (empty($this->searchQuery)) {
            return;
        }

        $product = Product::active()
            ->where(function ($q) {
                $q->where('barcode', $this->searchQuery)
                  ->orWhere('code',    $this->searchQuery)
                  ->orWhere('sku',     $this->searchQuery);
            })
            ->first();

        if ($product) {
            $this->addToCart($product->id);
            $this->searchQuery = '';
            $this->dispatch('play-sound', ['type' => 'success']);
        }
    }

    public function scanBarcodeImmediate()
    {
        if (empty($this->searchQuery)) {
            return;
        }

        $product = Product::active()
            ->where(function ($q) {
                $q->where('barcode', $this->searchQuery)
                  ->orWhere('code',    $this->searchQuery)
                  ->orWhere('sku',     $this->searchQuery);
            })
            ->first();

        if ($product) {
            $this->addToCart($product->id);
            $this->searchQuery = '';
            $this->dispatch('play-sound', ['type' => 'success']);
        } else {
            $this->dispatch('play-sound', ['type' => 'error']);
            $this->dispatch('alert', [
                'type'    => 'error',
                'message' => 'المنتج غير موجود: ' . $this->searchQuery,
            ]);
            $this->searchQuery = '';
        }
    }

    // ==================== Category ====================

    public function selectCategory($categoryId)
    {
        $this->selectedCategoryId = $categoryId;
    }

    // ==================== Cart Operations ====================

    public function addToCart($productId)
    {
        // Guard: لازم وردية مفتوحة
        if ($this->needsShift()) {
            $this->dispatch('play-sound', ['type' => 'error']);
            $this->dispatch('alert', [
                'type'    => 'warning',
                'message' => '⚠️ يجب فتح وردية أولاً قبل البيع. اضغط على "فتح وردية جديدة".',
            ]);
            return;
        }

        $product = Product::active()
            ->with(['activeSellingUnits'])
            ->findOrFail($productId);

        $availableQty = $product->getAvailableInWarehouse($this->selectedWarehouseId);

        // المنتج موجود بالسلة؟
        $cartIndex = -1;
        foreach ($this->cart as $index => $item) {
            if ($item['product_id'] == $productId && $item['selling_unit_id'] === null) {
                $cartIndex = $index;
                break;
            }
        }

        if ($cartIndex !== -1) {
            $newQty = $this->cart[$cartIndex]['quantity'] + 1;

            if (!$this->allowNegStock && $newQty > $availableQty) {
                $this->dispatch('play-sound', ['type' => 'error']);
                $this->dispatch('alert', [
                    'type'    => 'error',
                    'message' => 'الكمية المطلوبة غير متوفرة. المتوفر: ' . $availableQty,
                ]);
                return;
            }
            $this->cart[$cartIndex]['quantity'] = $newQty;

        } else {
            if (!$this->allowNegStock && $availableQty < 1) {
                $this->dispatch('play-sound', ['type' => 'error']);
                $this->dispatch('alert', [
                    'type'    => 'error',
                    'message' => 'المنتج نفذ من المستودع المحدد.',
                ]);
                return;
            }

            $units = $product->activeSellingUnits->map(function ($unit) use ($product) {
                return [
                    'id'                => $unit->id,
                    'unit_name'         => $unit->unit_name,
                    'unit_code'         => $unit->unit_code,
                    'conversion_factor' => $unit->quantity_in_base_unit,
                    'selling_price'     => round($product->base_selling_price * $unit->quantity_in_base_unit, 2),
                ];
            })->toArray();

            $this->cart[] = [
                'product_id'      => $product->id,
                'name'            => $product->name,
                'code'            => $product->code ?? $product->sku,
                'price'           => $product->base_selling_price,
                'quantity'        => 1,
                'selling_unit_id' => null,
                'unit_code'       => $product->base_unit_label ?? 'قطعة',
                'units'           => $units,
                'discount'        => 0,
                'tax_rate'        => $product->tax_rate ?? 0,
                'max_stock'       => $this->allowNegStock ? 999999 : $availableQty,
            ];
        }

        $this->updateTotals();
    }

    public function selectUnit($index, $unitId)
    {
        if (!isset($this->cart[$index])) return;

        $item    = $this->cart[$index];
        $product = Product::findOrFail($item['product_id']);

        $availableQty = $product->getAvailableInWarehouse($this->selectedWarehouseId);

        if (empty($unitId)) {
            $this->cart[$index]['selling_unit_id'] = null;
            $this->cart[$index]['price']            = $product->base_selling_price;
            $this->cart[$index]['unit_code']        = $product->base_unit_label ?? 'قطعة';
            $this->cart[$index]['max_stock']        = $this->allowNegStock ? 999999 : $availableQty;
        } else {
            $unit = $product->activeSellingUnits->firstWhere('id', $unitId);
            if ($unit) {
                $this->cart[$index]['selling_unit_id'] = $unit->id;
                $this->cart[$index]['price']           = round($product->base_selling_price * $unit->quantity_in_base_unit, 2);
                $this->cart[$index]['unit_code']       = $unit->unit_name;
                $factor = $unit->quantity_in_base_unit > 0 ? $unit->quantity_in_base_unit : 1;
                $this->cart[$index]['max_stock']       = $this->allowNegStock ? 999999 : floor($availableQty / $factor);
            }
        }

        if ($this->cart[$index]['quantity'] > $this->cart[$index]['max_stock']) {
            $this->cart[$index]['quantity'] = max(1, $this->cart[$index]['max_stock']);
        }

        $this->updateTotals();
    }

    public function incrementQuantity($index)
    {
        if (!isset($this->cart[$index])) return;

        $newQty = $this->cart[$index]['quantity'] + 1;
        if (!$this->allowNegStock && $newQty > $this->cart[$index]['max_stock']) {
            $this->dispatch('alert', [
                'type'    => 'error',
                'message' => 'تجاوزت الحد الأقصى للمخزون. المتوفر: ' . $this->cart[$index]['max_stock'],
            ]);
            return;
        }
        $this->cart[$index]['quantity'] = $newQty;
        $this->updateTotals();
    }

    public function decrementQuantity($index)
    {
        if (!isset($this->cart[$index])) return;

        $newQty = $this->cart[$index]['quantity'] - 1;
        if ($newQty >= 1) {
            $this->cart[$index]['quantity'] = $newQty;
            $this->updateTotals();
        }
    }

    public function updateQuantity($index, $qty)
    {
        if (!isset($this->cart[$index])) return;

        $qty = floatval($qty);
        if ($qty <= 0) $qty = 1;

        if (!$this->allowNegStock && $qty > $this->cart[$index]['max_stock']) {
            $this->dispatch('alert', [
                'type'    => 'error',
                'message' => 'الكمية المطلوبة غير متوفرة. المتوفر: ' . $this->cart[$index]['max_stock'],
            ]);
            $this->cart[$index]['quantity'] = $this->cart[$index]['max_stock'];
        } else {
            $this->cart[$index]['quantity'] = $qty;
        }

        $this->updateTotals();
    }

    public function removeFromCart($index)
    {
        if (isset($this->cart[$index])) {
            array_splice($this->cart, $index, 1);
            $this->updateTotals();
        }
    }

    public function clearCart()
    {
        $this->cart          = [];
        $this->discount_value = 0;
        $this->tax_rate      = 0;
        $this->shipping_cost = 0;
        $this->other_charges = 0;
        $this->notes         = '';
        $this->cashReceived  = 0;
        $this->cardAmount    = 0;
        $this->change        = 0;
        $this->updateTotals();
    }

    public function toggleFeaturedOnly()
    {
        $this->onlyFeatured = !$this->onlyFeatured;
    }

    public function updateItemNotes($index, $notes)
    {
        if (isset($this->cart[$index])) {
            $this->cart[$index]['notes'] = $notes;
        }
    }

    public function updatedDiscountValue()
    {
        $subtotal = $this->getSubtotalProperty();
        if ($subtotal <= 0) return;

        // Calculate discount percentage
        $pct = 0;
        if ($this->discount_type === 'percentage') {
            $pct = floatval($this->discount_value);
        } else {
            $pct = (floatval($this->discount_value) / $subtotal) * 100;
        }

        // If discount exceeds 10%, intercept and require Manager PIN (e.g., PIN: 1234 or 9999)
        if ($pct > 10) {
            $this->pendingDiscountValue = floatval($this->discount_value);
            $this->pendingDiscountType = $this->discount_type;
            
            // Revert discount temporarily
            $this->discount_value = 0;
            $this->showManagerPinModal = true;
            $this->managerPinInput = '';
            $this->managerPinError = '';
            $this->updateTotals();
            
            $this->dispatch('alert', [
                'type' => 'warning',
                'message' => '⚠️ الخصم يتجاوز 10% ويحتاج إلى رمز PIN الخاص بالمدير لتأكيده.',
            ]);
        } else {
            $this->updateTotals();
        }
    }

    public function verifyManagerPin()
    {
        // Simple mock manager PIN for POS: '1234'
        if ($this->managerPinInput === '1234' || $this->managerPinInput === '9999') {
            $this->discount_value = $this->pendingDiscountValue;
            $this->discount_type = $this->pendingDiscountType;
            $this->showManagerPinModal = false;
            $this->managerPinInput = '';
            $this->managerPinError = '';
            $this->updateTotals();
            $this->dispatch('alert', [
                'type' => 'success',
                'message' => '✅ تم التحقق من رمز المدير وتطبيق الخصم بنجاح.',
            ]);
        } else {
            $this->managerPinError = 'رمز PIN غير صحيح! يرجى المحاولة مرة أخرى.';
        }
    }

    // ==================== Hold Sales ====================

    /**
     * تعليق الفاتورة الحالية وحفظها مؤقتاً
     */
    public function holdSale()
    {
        if (empty($this->cart)) {
            $this->dispatch('alert', ['type' => 'warning', 'message' => 'السلة فارغة — لا يوجد شيء لتعليقه!']);
            return;
        }

        if (count($this->heldSales) >= 5) {
            $this->dispatch('alert', ['type' => 'error', 'message' => 'الحد الأقصى 5 فواتير معلقة. أتمم أو احذف فاتورة معلقة أولاً.']);
            return;
        }

        $this->heldSales[] = [
            'id'            => uniqid('hold_'),
            'held_at'       => now()->format('H:i'),
            'cart'          => $this->cart,
            'customer_id'   => $this->selectedCustomerId,
            'discount_value'=> $this->discount_value,
            'discount_type' => $this->discount_type,
            'tax_rate'      => $this->tax_rate,
            'shipping_cost' => $this->shipping_cost,
            'other_charges' => $this->other_charges,
            'notes'         => $this->notes,
            'items_count'   => count($this->cart),
            'total'         => $this->getGrandTotalProperty(),
        ];

        $this->clearCart();
        $this->dispatch('alert', ['type' => 'success', 'message' => '⏸ تم تعليق الفاتورة. يمكنك استعادتها في أي وقت.']);
    }

    /**
     * استعادة فاتورة معلقة بالـ ID
     */
    public function restoreHeldSale(string $heldId)
    {
        $index = null;
        foreach ($this->heldSales as $i => $held) {
            if ($held['id'] === $heldId) {
                $index = $i;
                break;
            }
        }

        if ($index === null) return;

        $held = $this->heldSales[$index];

        // إذا كانت هناك سلة حالية → علّقها أولاً
        if (!empty($this->cart)) {
            $this->holdSale();
        }

        // استعادة بيانات الفاتورة المعلقة
        $this->cart            = $held['cart'];
        $this->selectedCustomerId = $held['customer_id'];
        $this->discount_value  = $held['discount_value'];
        $this->discount_type   = $held['discount_type'];
        $this->tax_rate        = $held['tax_rate'];
        $this->shipping_cost   = $held['shipping_cost'];
        $this->other_charges   = $held['other_charges'];
        $this->notes           = $held['notes'];

        // حذفها من القائمة المعلقة
        array_splice($this->heldSales, $index, 1);

        $this->updateTotals();
        $this->dispatch('alert', ['type' => 'success', 'message' => '▶ تم استعادة الفاتورة المعلقة.']);
    }

    /**
     * حذف فاتورة معلقة بدون استعادة
     */
    public function removeHeldSale(string $heldId)
    {
        $this->heldSales = array_values(
            array_filter($this->heldSales, fn($h) => $h['id'] !== $heldId)
        );
        $this->dispatch('alert', ['type' => 'warning', 'message' => 'تم حذف الفاتورة المعلقة.']);
    }

    // ==================== Totals ====================

    public function updateTotals()
    {
        if ($this->auto_match_paid) {
            $this->paid_amount = $this->getGrandTotalProperty();
        }
        // Update change calculation
        $this->change = max(0, floatval($this->cashReceived) - $this->getGrandTotalProperty());
    }

    public function updatedCashReceived()
    {
        $this->change = max(0, floatval($this->cashReceived) - $this->getGrandTotalProperty());
    }

    public function getSubtotalProperty()
    {
        $subtotal = 0;
        foreach ($this->cart as $item) {
            $itemTotal    = $item['quantity'] * $item['price'];
            $itemDiscount = $itemTotal * ($item['discount'] / 100);
            $subtotal    += ($itemTotal - $itemDiscount);
        }
        return round($subtotal, 2);
    }

    public function getGrandTotalProperty()
    {
        $subtotal = $this->subtotal;

        $discountAmount = $this->discount_type === 'percentage'
            ? $subtotal * ($this->discount_value / 100)
            : floatval($this->discount_value);

        $taxableAmount = max(0, $subtotal - $discountAmount);
        $taxAmount     = $taxableAmount * ($this->tax_rate / 100);

        $total = $taxableAmount + $taxAmount
               + floatval($this->shipping_cost)
               + floatval($this->other_charges);

        return round(max(0, $total), 2);
    }

    // ==================== Payment ====================

    public function updatedPaymentMethod()
    {
        $this->cashReceived = 0;
        $this->cardAmount   = 0;
        $this->change       = 0;

        if ($this->paymentMethod === 'cash') {
            $this->paid_amount    = $this->getGrandTotalProperty();
            $this->auto_match_paid = true;
        } elseif ($this->paymentMethod === 'card') {
            $this->paid_amount    = $this->getGrandTotalProperty();
            $this->auto_match_paid = true;
        } elseif ($this->paymentMethod === 'credit') {
            $this->paid_amount    = 0;
            $this->auto_match_paid = false;
        } else {
            $this->auto_match_paid = false;
        }
    }

    // ==================== Submit Invoice ====================

    public function submitInvoice(InvoiceService $invoiceService)
    {
        // ✅ الحالة الأولى: تحقّق من وردية مفتوحة اليوم على مستوى قاعدة البيانات (DB check وليس Livewire state فقط)
        $activeShift = PosShift::where('user_id', auth()->id())
            ->where('status', PosShift::STATUS_OPEN)
            ->whereDate('opened_at', today())
            ->first();

        if (! $activeShift) {
            $this->currentShiftId = null; // تحديث الحالة المحلية
            $this->dispatch('alert', [
                'type'    => 'error',
                'message' => '⚠️ لا توجد وردية مفتوحة. افتح وردية أولاً قبل تسجيل البيع.',
            ]);
            return;
        }

        // تحديث currentShiftId لو كان مختلفاً (حالة تجديد الوردية)
        $this->currentShiftId = $activeShift->id;

        if (empty($this->cart)) {
            $this->dispatch('alert', [
                'type'    => 'error',
                'message' => 'سلة البيع فارغة!',
            ]);
            return;
        }

        $grandTotal = $this->getGrandTotalProperty();
        $paid       = floatval($this->paid_amount);

        if ($paid > $grandTotal + 0.01) {
            $this->dispatch('alert', [
                'type'    => 'error',
                'message' => 'المبلغ المدفوع أكبر من إجمالي الفاتورة!',
            ]);
            return;
        }

        $items = array_map(fn($item) => [
            'product_id'      => $item['product_id'],
            'selling_unit_id' => $item['selling_unit_id'],
            'quantity'        => $item['quantity'],
            'price'           => $item['price'],
            'discount'        => $item['discount'],
            'tax_rate'        => $item['tax_rate'],
            'notes'           => $item['notes'] ?? null,
        ], $this->cart);

        $payload = [
            'customer_id'    => $this->selectedCustomerId,
            'warehouse_id'   => $this->selectedWarehouseId,
            'invoice_date'   => now(),
            'discount_type'  => $this->discount_type,
            'discount_value' => floatval($this->discount_value),
            'tax_amount'     => 0,
            'tax_rate'       => floatval($this->tax_rate),
            'shipping_cost'  => floatval($this->shipping_cost),
            'other_charges'  => floatval($this->other_charges),
            'paid'           => $paid,
            'notes'          => $this->notes,
            'items'          => $items,
            // POS-specific
            'source'         => 'pos',
            'shift_id'       => $this->currentShiftId,
            'payment_method' => $this->paymentMethod,
        ];

        try {
            DB::beginTransaction();

            $invoice = $invoiceService->createSalesInvoice($payload);

            // Update shift totals
            if ($this->currentShiftId) {
                $shift = PosShift::find($this->currentShiftId);
                if ($shift) {
                    $shift->increment('total_sales', $grandTotal);
                    $shift->increment('sales_count');
                    $shift->increment('net_sales', $grandTotal);
                }
            }

            DB::commit();

            $this->lastInvoiceId = $invoice->id;
            $this->lastReceiptUrl = route('invoices.sales.print', $invoice->id);

            $this->dispatch('alert', [
                'type'    => 'success',
                'message' => '✅ تم تسجيل فاتورة البيع بنجاح!',
            ]);

            if ($this->autoPrint) {
                $this->dispatch('print-receipt', ['invoiceId' => $invoice->id]);
            }

            $this->clearCart();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('alert', [
                'type'    => 'error',
                'message' => 'خطأ أثناء الحفظ: ' . $e->getMessage(),
            ]);
        }
    }

    // ==================== Customer Quick Add ====================

    public function quickAddCustomer()
    {
        $this->validate([
            'newCustomerName'  => 'required|string|max:255',
            'newCustomerPhone' => 'nullable|string|max:20',
        ], [
            'newCustomerName.required' => 'اسم العميل مطلوب.',
        ]);

        try {
            // Generate unique customer code
            $code = 'CUST-' . strtoupper(uniqid());

            $customer = Customer::create([
                'name'      => $this->newCustomerName,
                'phone'     => $this->newCustomerPhone,
                'code'      => $code,
                'is_active' => true,
                'balance'   => 0,
            ]);

            $this->selectedCustomerId = $customer->id;
            $this->newCustomerName = '';
            $this->newCustomerPhone = '';

            $this->dispatch('alert', [
                'type'    => 'success',
                'message' => '✅ تم إضافة العميل وتحديده بنجاح!',
            ]);

            // Dispatch event to close Alpine modal
            $this->dispatch('close-customer-modal');

        } catch (\Exception $e) {
            $this->dispatch('alert', [
                'type'    => 'error',
                'message' => 'حدث خطأ: ' . $e->getMessage(),
            ]);
        }
    }

    // ==================== Render ====================

    public function render()
    {
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();

        $warehouses = Warehouse::where('is_active', true)
            ->where('status', 'active')
            ->get();

        $customers = Customer::where('is_active', true)->get();

        $productsQuery = Product::active()
            ->with([
                'basePricing',
                'activeSellingUnits',
                'warehouses' => fn($q) => $q->where('warehouses.is_active', true),
            ]);

        if ($this->onlyFeatured) {
            $productsQuery->where('is_featured', true);
        }

        if ($this->selectedCategoryId) {
            $productsQuery->where('category_id', $this->selectedCategoryId);
        }

        if (!empty($this->searchQuery)) {
            $search = $this->searchQuery;
            $productsQuery->where(function ($q) use ($search) {
                $q->where('name',    'like', "%{$search}%")
                  ->orWhere('code',    'like', "%{$search}%")
                  ->orWhere('sku',     'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        $products = $productsQuery->take(24)->get();

        foreach ($products as $p) {
            $stock = $p->getAvailableInWarehouse($this->selectedWarehouseId);
            $p->available_stock = $stock;
            $p->out_of_stock    = !$this->allowNegStock && $stock <= 0;
        }

        // Active shift data for header display
        $activeShift = $this->currentShiftId
            ? PosShift::with('user')->find($this->currentShiftId)
            : null;

        $selectedCustomer = $this->selectedCustomerId
            ? Customer::find($this->selectedCustomerId)
            : null;

        return view('livewire.pos-panel', compact(
            'categories', 'warehouses', 'customers', 'products', 'activeShift', 'selectedCustomer'
        ))
            ->extends('layouts.app')
            ->section('content');
    }
}
