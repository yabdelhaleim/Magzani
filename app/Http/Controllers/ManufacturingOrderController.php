<?php

namespace App\Http\Controllers;

use App\Services\ManufacturingOrderService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ManufacturingOrderController extends Controller
{
    public function __construct(
        private ManufacturingOrderService $orderService
    ) {}

    /**
     * Display a listing of manufacturing orders (Web View)
     */
    public function index(Request $request)
    {
        $orders = \App\Models\ManufacturingOrder::with(['product', 'components', 'creator'])
            ->latest()
            ->paginate(20);

        return view('manufacturing-orders.index', compact('orders'));
    }

    /**
     * Display a listing of manufacturing orders (API)
     */
    public function indexApi(Request $request): JsonResponse
    {
        $query = \App\Models\ManufacturingOrder::with(['product', 'components', 'creator'])
            ->latest();

        // Search filters
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('product_name', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($productId = $request->input('product_id')) {
            $query->where('product_id', $productId);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $orders = $query->paginate($request->input('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    }

    /**
     * Show the form for creating a new manufacturing order (Web View)
     */
    public function create()
    {
        $warehouses = \App\Models\Warehouse::where('is_active', true)->get();
        return view('manufacturing-orders.create', compact('warehouses'));
    }

    /**
     * Store a newly created manufacturing order (Web)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'nullable|exists:products,id',
            'product_name' => 'required|string|max:255',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'quantity_produced' => 'required|numeric|min:0.01',
            'waste_cost' => 'required|numeric|min:0',
            'labor_cost' => 'required|numeric|min:0',
            'nails_cost' => 'required|numeric|min:0',
            'tips_cost' => 'required|numeric|min:0',
            'transport_cost' => 'required|numeric|min:0',
            'fumigation_cost' => 'required|numeric|min:0',
            'profit_margin' => 'required|numeric|min:0',
            // cost_per_unit, total_cost, selling_price_per_unit, profit_amount will be auto-calculated
            'notes' => 'nullable|string',
            'components' => 'required|array|min:1',
            'components.*.component_type' => 'required|string|max:50',
            'components.*.quantity' => 'required|numeric|min:0.0001',
            'components.*.thickness_cm' => 'required|numeric|min:0',
            'components.*.width_cm' => 'required|numeric|min:0',
            'components.*.length_cm' => 'required|numeric|min:0',
            'components.*.price_per_cubic_meter' => 'required|numeric|min:0',
        ]);

        try {
            $order = $this->orderService->createOrder($validated);

            return redirect()
                ->route('manufacturing-orders.show', $order->id)
                ->with('success', '✅ تم إنشاء أمر التصنيع بنجاح');
        } catch (\Exception $e) {
            Log::error('Manufacturing order creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()
                ->with('error', 'فشل إنشاء أمر التصنيع: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Store a newly created manufacturing order (API)
     */
    public function storeApi(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'nullable|exists:products,id',
            'product_name' => 'required|string|max:255',
            'quantity_produced' => 'required|numeric|min:0.01',
            'waste_cost' => 'required|numeric|min:0',
            'labor_cost' => 'required|numeric|min:0',
            'nails_cost' => 'required|numeric|min:0',
            'tips_cost' => 'required|numeric|min:0',
            'transport_cost' => 'required|numeric|min:0',
            'fumigation_cost' => 'required|numeric|min:0',
            'profit_margin' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'components' => 'required|array|min:1',
            'components.*.component_type' => 'required|string|max:50',
            'components.*.quantity' => 'required|numeric|min:0.0001',
            'components.*.thickness_cm' => 'required|numeric|min:0',
            'components.*.width_cm' => 'required|numeric|min:0',
            'components.*.length_cm' => 'required|numeric|min:0',
            'components.*.price_per_cubic_meter' => 'required|numeric|min:0',
        ]);

        try {
            $order = $this->orderService->createOrder($validated);

            return response()->json([
                'success' => true,
                'message' => 'Manufacturing order created successfully',
                'data' => $order->load(['components', 'product', 'creator'])
            ], 201);
        } catch (\Exception $e) {
            Log::error('Manufacturing order creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create manufacturing order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified manufacturing order (Web View)
     */
    public function show(string $id)
    {
        $order = \App\Models\ManufacturingOrder::with([
            'components',
            'product',
            'creator',
            'updater',
            'completer',
            'inventoryMovements'
        ])->findOrFail($id);

        $warehouses = \App\Models\Warehouse::where('is_active', true)->get();

        return view('manufacturing-orders.show', compact('order', 'warehouses'));
    }

    /**
     * Display the specified manufacturing order (API)
     */
    public function showApi(string $id): JsonResponse
    {
        $order = \App\Models\ManufacturingOrder::with(['components', 'product', 'creator', 'updater', 'completer'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $order
        ]);
    }

    /**
     * Show the form for editing the specified manufacturing order (Web View)
     */
    public function edit(string $id)
    {
        $order = \App\Models\ManufacturingOrder::with(['components', 'product'])->findOrFail($id);
        return view('manufacturing-orders.edit', compact('order'));
    }

    /**
     * Update the specified manufacturing order (Web)
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'product_id' => 'nullable|exists:products,id',
            'product_name' => 'sometimes|required|string|max:255',
            'quantity_produced' => 'sometimes|required|numeric|min:0.01',
            'waste_cost' => 'sometimes|required|numeric|min:0',
            'labor_cost' => 'sometimes|required|numeric|min:0',
            'nails_cost' => 'sometimes|required|numeric|min:0',
            'tips_cost' => 'sometimes|required|numeric|min:0',
            'transport_cost' => 'sometimes|required|numeric|min:0',
            'fumigation_cost' => 'sometimes|required|numeric|min:0',
            'profit_margin' => 'sometimes|required|numeric|min:0',
            'notes' => 'nullable|string',
            'components' => 'sometimes|array|min:1',
            'components.*.component_type' => 'required|string|max:50',
            'components.*.quantity' => 'required|numeric|min:0.0001',
            'components.*.thickness_cm' => 'required|numeric|min:0',
            'components.*.width_cm' => 'required|numeric|min:0',
            'components.*.length_cm' => 'required|numeric|min:0',
            'components.*.price_per_cubic_meter' => 'required|numeric|min:0',
        ]);

        try {
            $order = \App\Models\ManufacturingOrder::findOrFail($id);
            $updatedOrder = $this->orderService->updateOrder($order, $validated);

            return redirect()
                ->route('manufacturing-orders.show', $updatedOrder->id)
                ->with('success', '✅ تم تحديث أمر التصنيع بنجاح');
        } catch (\Exception $e) {
            Log::error('Manufacturing order update failed', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return back()
                ->with('error', 'فشل تحديث أمر التصنيع: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Update the specified manufacturing order (API)
     */
    public function updateApi(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'nullable|exists:products,id',
            'product_name' => 'sometimes|required|string|max:255',
            'quantity_produced' => 'sometimes|required|numeric|min:0.01',
            'waste_cost' => 'sometimes|required|numeric|min:0',
            'labor_cost' => 'sometimes|required|numeric|min:0',
            'nails_cost' => 'sometimes|required|numeric|min:0',
            'tips_cost' => 'sometimes|required|numeric|min:0',
            'transport_cost' => 'sometimes|required|numeric|min:0',
            'fumigation_cost' => 'sometimes|required|numeric|min:0',
            'profit_margin' => 'sometimes|required|numeric|min:0',
            'notes' => 'nullable|string',
            'components' => 'sometimes|array|min:1',
            'components.*.component_type' => 'required|string|max:50',
            'components.*.quantity' => 'required|numeric|min:0.0001',
            'components.*.thickness_cm' => 'required|numeric|min:0',
            'components.*.width_cm' => 'required|numeric|min:0',
            'components.*.length_cm' => 'required|numeric|min:0',
            'components.*.price_per_cubic_meter' => 'required|numeric|min:0',
        ]);

        try {
            $order = \App\Models\ManufacturingOrder::findOrFail($id);
            $updatedOrder = $this->orderService->updateOrder($order, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Manufacturing order updated successfully',
                'data' => $updatedOrder->load(['components', 'product', 'creator'])
            ]);
        } catch (\Exception $e) {
            Log::error('Manufacturing order update failed', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update manufacturing order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Confirm the manufacturing order (Web)
     */
    public function confirm(string $id)
    {
        try {
            $order = \App\Models\ManufacturingOrder::findOrFail($id);
            $confirmedOrder = $this->orderService->confirmOrder($order);

            return redirect()
                ->route('manufacturing-orders.show', $confirmedOrder->id)
                ->with('success', '✅ تم تأكيد أمر التصنيع بنجاح');
        } catch (\Exception $e) {
            Log::error('Manufacturing order confirmation failed', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return back()
                ->with('error', 'فشل تأكيد الأمر: ' . $e->getMessage());
        }
    }

    /**
     * Confirm the manufacturing order (API)
     */
    public function confirmApi(string $id): JsonResponse
    {
        try {
            $order = \App\Models\ManufacturingOrder::findOrFail($id);
            $confirmedOrder = $this->orderService->confirmOrder($order);

            return response()->json([
                'success' => true,
                'message' => 'Manufacturing order confirmed successfully',
                'data' => $confirmedOrder
            ]);
        } catch (\Exception $e) {
            Log::error('Manufacturing order confirmation failed', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to confirm order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Complete the manufacturing order and add to inventory (Web)
     */
    public function complete(Request $request, string $id)
    {
        $validated = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'product_id' => 'nullable|exists:products,id',
        ]);

        try {
            $order = \App\Models\ManufacturingOrder::findOrFail($id);
            $completedOrder = $this->orderService->completeOrder(
                $order,
                $validated['warehouse_id'],
                $validated['product_id'] ?? null
            );

            return redirect()
                ->route('manufacturing-orders.show', $completedOrder->id)
                ->with('success', '✅ تم إكمال أمر التصنيع وإضافة المنتج للمخزون بنجاح');
        } catch (\Exception $e) {
            Log::error('Manufacturing order completion failed', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return back()
                ->with('error', 'فشل إكمال الأمر: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Complete the manufacturing order and add to inventory (API)
     */
    public function completeApi(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'product_id' => 'nullable|exists:products,id',
        ]);

        try {
            $order = \App\Models\ManufacturingOrder::findOrFail($id);
            $completedOrder = $this->orderService->completeOrder(
                $order,
                $validated['warehouse_id'],
                $validated['product_id'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Manufacturing order completed and inventory updated successfully',
                'data' => $completedOrder->load(['components', 'product', 'inventoryMovements'])
            ]);
        } catch (\Exception $e) {
            Log::error('Manufacturing order completion failed', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to complete order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel the manufacturing order (Web)
     */
    public function cancel(Request $request, string $id)
    {
        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            $order = \App\Models\ManufacturingOrder::findOrFail($id);
            $cancelledOrder = $this->orderService->cancelOrder($order, $validated['reason']);

            return redirect()
                ->route('manufacturing-orders.show', $cancelledOrder->id)
                ->with('success', '✅ تم إلغاء أمر التصنيع بنجاح');
        } catch (\Exception $e) {
            Log::error('Manufacturing order cancellation failed', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return back()
                ->with('error', 'فشل إلغاء الأمر: ' . $e->getMessage());
        }
    }

    /**
     * Cancel the manufacturing order (API)
     */
    public function cancelApi(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            $order = \App\Models\ManufacturingOrder::findOrFail($id);
            $cancelledOrder = $this->orderService->cancelOrder($order, $validated['reason']);

            return response()->json([
                'success' => true,
                'message' => 'Manufacturing order cancelled successfully',
                'data' => $cancelledOrder
            ]);
        } catch (\Exception $e) {
            Log::error('Manufacturing order cancellation failed', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified manufacturing order (Web)
     */
    public function destroy(string $id)
    {
        try {
            $order = \App\Models\ManufacturingOrder::findOrFail($id);
            $this->orderService->deleteOrder($order);

            return redirect()
                ->route('manufacturing-orders.index')
                ->with('success', '✅ تم حذف أمر التصنيع بنجاح');
        } catch (\Exception $e) {
            Log::error('Manufacturing order deletion failed', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return back()
                ->with('error', 'فشل حذف الأمر: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified manufacturing order (API)
     */
    public function destroyApi(string $id): JsonResponse
    {
        try {
            $order = \App\Models\ManufacturingOrder::findOrFail($id);
            $this->orderService->deleteOrder($order);

            return response()->json([
                'success' => true,
                'message' => 'Manufacturing order deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Manufacturing order deletion failed', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get manufacturing order statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        $filters = [
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
        ];

        $stats = $this->orderService->getStatistics($filters);

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Calculate costs from components (AJAX endpoint)
     */
    public function calculateCosts(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'components' => 'required|array|min:1',
            'components.*.quantity' => 'required|numeric|min:0.0001',
            'components.*.thickness_cm' => 'required|numeric|min:0',
            'components.*.width_cm' => 'required|numeric|min:0',
            'components.*.length_cm' => 'required|numeric|min:0',
            'components.*.price_per_cubic_meter' => 'required|numeric|min:0',
        ]);

        try {
            $result = $this->orderService->calculateCosts($validated['components']);

            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to calculate costs: ' . $e->getMessage()
            ], 500);
        }
    }
}