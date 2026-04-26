<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\WarehouseInboundOrder;
use App\Models\WarehouseInboundOrderItem;
use App\Models\WarehouseOutboundOrder;
use App\Models\WarehouseOutboundOrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WarehouseOrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // ==================== Inbound Orders ====================

    public function inboundIndex(Request $request)
    {
        $this->authorize('warehouse.transfers.read');

        $query = WarehouseInboundOrder::with(['warehouse', 'creator', 'items']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        $orders = $query->orderBy('created_at', 'desc')
            ->paginate(20)
            ->appends($request->query());

        $warehouses = Warehouse::active()->get();

        return view('warehouse-orders.inbound.index', compact('orders', 'warehouses'));
    }

    public function inboundCreate()
    {
        $this->authorize('warehouse.transfers.create');

        $warehouses = Warehouse::active()->get();
        $products = Product::active()->get();

        return view('warehouse-orders.inbound.create', compact('warehouses', 'products'));
    }

    public function inboundStore(Request $request)
    {
        $this->authorize('warehouse.transfers.create');

        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'order_date' => 'required|date',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.unit' => 'required|string',
            'items.*.unit_cost' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $order = WarehouseInboundOrder::create([
                'warehouse_id' => $request->warehouse_id,
                'order_date' => $request->order_date,
                'reference_number' => $request->reference_number,
                'notes' => $request->notes,
                'status' => 'completed',
                'created_by' => auth()->id(),
                'completed_at' => now(),
            ]);

            foreach ($request->items as $item) {
                $totalCost = ($item['unit_cost'] ?? 0) * $item['quantity'];

                WarehouseInboundOrderItem::create([
                    'inbound_order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'],
                    'unit_cost' => $item['unit_cost'] ?? null,
                    'total_cost' => $totalCost > 0 ? $totalCost : null,
                    'notes' => $item['notes'] ?? null,
                ]);

                // تحديث كمية المنتج في المخزن
                $warehouseProduct = \App\Models\ProductWarehouse::firstOrCreate(
                    [
                        'warehouse_id' => $request->warehouse_id,
                        'product_id' => $item['product_id'],
                    ],
                    ['quantity' => 0]
                );

                $warehouseProduct->quantity += $item['quantity'];
                $warehouseProduct->save();
            }

            DB::commit();

            return redirect()
                ->route('warehouse-orders.inbound.show', $order)
                ->with('success', 'تم إنشاء أذن الإدخال بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }

    public function inboundShow(WarehouseInboundOrder $order)
    {
        $this->authorize('warehouse.transfers.read');

        $order->load(['warehouse', 'creator', 'items.product']);

        return view('warehouse-orders.inbound.show', compact('order'));
    }

    public function inboundPrint(WarehouseInboundOrder $order)
    {
        $this->authorize('warehouse.transfers.read');

        $order->load(['warehouse', 'creator', 'items.product']);

        return view('warehouse-orders.inbound.print', compact('order'));
    }

    // ==================== Outbound Orders ====================

    public function outboundIndex(Request $request)
    {
        $this->authorize('warehouse.transfers.read');

        $query = WarehouseOutboundOrder::with(['warehouse', 'creator', 'items']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%")
                  ->orWhere('recipient_name', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        $orders = $query->orderBy('created_at', 'desc')
            ->paginate(20)
            ->appends($request->query());

        $warehouses = Warehouse::active()->get();

        return view('warehouse-orders.outbound.index', compact('orders', 'warehouses'));
    }

    public function outboundCreate()
    {
        $this->authorize('warehouse.transfers.create');

        $warehouses = Warehouse::active()->get();
        $products = Product::active()->get();

        return view('warehouse-orders.outbound.create', compact('warehouses', 'products'));
    }

    public function outboundStore(Request $request)
    {
        $this->authorize('warehouse.transfers.create');

        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'order_date' => 'required|date',
            'reference_number' => 'nullable|string|max:255',
            'purpose' => 'required|in:sale,transfer,return,damage,sample,other',
            'recipient_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.requested_quantity' => 'required|numeric|min:0.001',
            'items.*.unit' => 'required|string',
        ]);

        DB::beginTransaction();

        try {
            $order = WarehouseOutboundOrder::create([
                'warehouse_id' => $request->warehouse_id,
                'order_date' => $request->order_date,
                'reference_number' => $request->reference_number,
                'purpose' => $request->purpose,
                'recipient_name' => $request->recipient_name,
                'notes' => $request->notes,
                'status' => 'pending',
                'created_by' => auth()->id(),
            ]);

            foreach ($request->items as $item) {
                WarehouseOutboundOrderItem::create([
                    'outbound_order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'requested_quantity' => $item['requested_quantity'],
                    'unit' => $item['unit'],
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('warehouse-orders.outbound.show', $order)
                ->with('success', 'تم إنشاء أذن الإخراج بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }

    public function outboundShow(WarehouseOutboundOrder $order)
    {
        $this->authorize('warehouse.transfers.read');

        $order->load(['warehouse', 'creator', 'items.product']);

        return view('warehouse-orders.outbound.show', compact('order'));
    }

    public function outboundPrint(WarehouseOutboundOrder $order)
    {
        $this->authorize('warehouse.transfers.read');

        $order->load(['warehouse', 'creator', 'items.product']);

        return view('warehouse-orders.outbound.print', compact('order'));
    }

    public function outboundApprove(Request $request, WarehouseOutboundOrder $order)
    {
        $this->authorize('warehouse.transfers.update');

        $request->validate([
            'items' => 'required|array',
            'items.*.approved_quantity' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            foreach ($request->items as $itemId => $data) {
                $item = $order->items()->findOrFail($itemId);
                $item->approved_quantity = $data['approved_quantity'];
                $item->save();
            }

            $order->status = 'completed';
            $order->save();

            DB::commit();

            return redirect()
                ->route('warehouse-orders.outbound.show', $order)
                ->with('success', 'تم اعتماد الأذن وتنفيذه بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }

    public function outboundCancel(WarehouseOutboundOrder $order)
    {
        $this->authorize('warehouse.transfers.delete');

        if ($order->status === 'completed') {
            return back()->with('error', 'لا يمكن إلغاء أذن منفذ');
        }

        $order->status = 'cancelled';
        $order->save();

        return back()->with('success', 'تم إلغاء الأذن بنجاح');
    }
}
