<?php

namespace App\Http\Controllers;

use App\Models\RawMaterialTemplate;
use App\Models\Warehouse;
use App\Services\RawMaterialTemplateInventoryService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class RawMaterialTemplateController extends Controller
{
    public function __construct(
        private RawMaterialTemplateInventoryService $rawMaterialInventory
    ) {}

    public function index()
    {
        try {
            $templates = RawMaterialTemplate::with('warehouse:id,name,code')
                ->latest()
                ->paginate(20);

            return view('manufacturing-orders.raw-materials.index', compact('templates'));
        } catch (\Exception $e) {
            Log::error('Failed to load raw materials', ['error' => $e->getMessage()]);
            return view('manufacturing-orders.raw-materials.index', ['templates' => collect()])
                ->with('error', 'حدث خطأ أثناء تحميل قائمة الخامات.');
        }
    }

    public function create()
    {
        try {
            $warehouses = Warehouse::query()
                ->orderBy('name')
                ->get(['id', 'name', 'code']);

            return view('manufacturing-orders.raw-materials.create', compact('warehouses'));
        } catch (\Exception $e) {
            Log::error('Failed to load create form', ['error' => $e->getMessage()]);
            return back()->with('error', 'حدث خطأ أثناء تحميل نموذج الإنشاء.');
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'warehouse_id' => 'required|exists:warehouses,id',
                'quantity' => 'required|numeric|min:0',
                'sale_price' => 'required|numeric|min:0',
                'buy_price' => 'required|numeric|min:0',
            ]);

            DB::transaction(function () use ($validated) {
                $template = RawMaterialTemplate::create([
                    'name' => $validated['name'],
                    'warehouse_id' => $validated['warehouse_id'],
                    'quantity' => $validated['quantity'],
                    'sale_price' => $validated['sale_price'],
                    'buy_price' => $validated['buy_price'],
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                ]);
                $this->rawMaterialInventory->sync($template->fresh());
            });

            return redirect()->route('manufacturing-orders.raw-materials.index')
                ->with('success', 'تم إنشاء الخامة بنجاح وربطها بالمخزن');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('DB error creating raw material', ['error' => $e->getMessage()]);
            return back()->withInput()
                ->with('error', 'حدث خطأ في قاعدة البيانات أثناء إنشاء الخامة.');
        } catch (\Exception $e) {
            Log::error('Unexpected error creating raw material', ['error' => $e->getMessage()]);
            return back()->withInput()
                ->with('error', 'حدث خطأ غير متوقع أثناء إنشاء الخامة.');
        }
    }

    public function show(string $id)
    {
        try {
            $template = RawMaterialTemplate::with('warehouse:id,name,code')->findOrFail($id);

            return view('manufacturing-orders.raw-materials.show', compact('template'));
        } catch (ModelNotFoundException $e) {
            abort(404, 'الخامة غير موجودة');
        } catch (\Exception $e) {
            Log::error('Failed to show raw material', [
                'id'    => $id,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'حدث خطأ أثناء عرض الخامة.');
        }
    }

    public function edit(string $id)
    {
        try {
            $template = RawMaterialTemplate::findOrFail($id);
            $warehouses = Warehouse::query()
                ->orderBy('name')
                ->get(['id', 'name', 'code']);

            return view('manufacturing-orders.raw-materials.edit', compact('template', 'warehouses'));
        } catch (ModelNotFoundException $e) {
            abort(404, 'الخامة غير موجودة');
        } catch (\Exception $e) {
            Log::error('Failed to load edit form', [
                'id'    => $id,
                'error' => $e->getMessage(),
            ]);
            return redirect()->route('manufacturing-orders.raw-materials.index')
                ->with('error', 'حدث خطأ أثناء تحميل نموذج التعديل.');
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            $template = RawMaterialTemplate::findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'warehouse_id' => 'required|exists:warehouses,id',
                'quantity' => 'required|numeric|min:0',
                'sale_price' => 'required|numeric|min:0',
                'buy_price' => 'required|numeric|min:0',
            ]);

            $previousWarehouseId = (int) $template->warehouse_id;

            DB::transaction(function () use ($template, $validated, $previousWarehouseId) {
                $template->update([
                    'name' => $validated['name'],
                    'warehouse_id' => $validated['warehouse_id'],
                    'quantity' => $validated['quantity'],
                    'sale_price' => $validated['sale_price'],
                    'buy_price' => $validated['buy_price'],
                    'updated_by' => Auth::id(),
                ]);
                $newWarehouseId = (int) $validated['warehouse_id'];
                $this->rawMaterialInventory->sync(
                    $template->fresh(),
                    $previousWarehouseId !== $newWarehouseId ? $previousWarehouseId : null
                );
            });

            return redirect()->route('manufacturing-orders.raw-materials.index')
                ->with('success', 'تم تحديث الخامة بنجاح وتحديث المخزن');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (ModelNotFoundException $e) {
            abort(404, 'الخامة غير موجودة');
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('DB error updating raw material', [
                'id'    => $id,
                'error' => $e->getMessage(),
            ]);
            return back()->withInput()
                ->with('error', 'حدث خطأ في قاعدة البيانات أثناء تحديث الخامة.');
        } catch (\Exception $e) {
            Log::error('Unexpected error updating raw material', [
                'id'    => $id,
                'error' => $e->getMessage(),
            ]);
            return back()->withInput()
                ->with('error', 'حدث خطأ غير متوقع أثناء تحديث الخامة.');
        }
    }

    public function destroy(string $id)
    {
        try {
            $template = RawMaterialTemplate::findOrFail($id);
            if ($template->warehouse_id) {
                $this->rawMaterialInventory->forgetWarehouseCache((int) $template->warehouse_id);
            }
            $template->delete();

            return redirect()->route('manufacturing-orders.raw-materials.index')
                ->with('success', 'تم حذف الخامة بنجاح');
        } catch (ModelNotFoundException $e) {
            abort(404, 'الخامة غير موجودة');
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('DB error deleting raw material', [
                'id'    => $id,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'لا يمكن حذف الخامة. قد تكون مرتبطة بأوامر تصنيع.');
        } catch (\Exception $e) {
            Log::error('Unexpected error deleting raw material', [
                'id'    => $id,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'حدث خطأ غير متوقع أثناء حذف الخامة.');
        }
    }
}
