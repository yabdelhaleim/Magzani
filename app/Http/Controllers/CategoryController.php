<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CategoryController extends Controller
{
    /**
     * عرض قائمة التصنيفات
     */
    public function index()
    {
        $categories = Category::withCount('products')
            ->ordered()
            ->get();

        $colors     = Category::availableColors();
        $icons      = Category::availableIcons();
        $totalProducts = $categories->sum('products_count');

        return view('categories.index', compact('categories', 'colors', 'icons', 'totalProducts'));
    }

    /**
     * حفظ تصنيف جديد (AJAX)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:100|unique:categories,name',
            'description'=> 'nullable|string|max:255',
            'color'      => 'required|string|max:20',
            'icon'       => 'required|string|max:60',
            'sort_order' => 'nullable|integer|min:0',
            'is_active'  => 'nullable|boolean',
        ], [
            'name.required' => 'اسم التصنيف مطلوب.',
            'name.unique'   => 'هذا الاسم مستخدم بالفعل، اختر اسماً آخر.',
            'name.max'      => 'اسم التصنيف لا يتجاوز 100 حرف.',
        ]);

        try {
            $category = Category::create([
                'name'        => $validated['name'],
                'description' => $validated['description'] ?? null,
                'color'       => $validated['color'],
                'icon'        => $validated['icon'],
                'sort_order'  => $validated['sort_order'] ?? 0,
                'is_active'   => isset($validated['is_active']) ? (bool)$validated['is_active'] : true,
                'parent_id'   => null,
            ]);

            return response()->json([
                'success'  => true,
                'message'  => "✅ تم إضافة التصنيف \"{$category->name}\" بنجاح!",
                'category' => $this->formatCategory($category->loadCount('products')),
            ]);

        } catch (\Exception $e) {
            Log::error('Category store failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => '❌ حدث خطأ أثناء الحفظ: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * تحديث تصنيف موجود (AJAX)
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:100|unique:categories,name,' . $category->id,
            'description'=> 'nullable|string|max:255',
            'color'      => 'required|string|max:20',
            'icon'       => 'required|string|max:60',
            'sort_order' => 'nullable|integer|min:0',
            'is_active'  => 'nullable|boolean',
        ], [
            'name.required' => 'اسم التصنيف مطلوب.',
            'name.unique'   => 'هذا الاسم مستخدم بالفعل، اختر اسماً آخر.',
        ]);

        try {
            $category->update([
                'name'        => $validated['name'],
                'description' => $validated['description'] ?? null,
                'color'       => $validated['color'],
                'icon'        => $validated['icon'],
                'sort_order'  => $validated['sort_order'] ?? 0,
                'is_active'   => isset($validated['is_active']) ? (bool)$validated['is_active'] : true,
            ]);

            return response()->json([
                'success'  => true,
                'message'  => "✅ تم تحديث التصنيف \"{$category->name}\" بنجاح!",
                'category' => $this->formatCategory($category->fresh()->loadCount('products')),
            ]);

        } catch (\Exception $e) {
            Log::error('Category update failed', ['id' => $category->id, 'error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => '❌ حدث خطأ أثناء التحديث: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * حذف تصنيف (AJAX) — مع التحقق من عدم وجود منتجات مربوطة
     */
    public function destroy(Category $category)
    {
        try {
            // التحقق: لا يمكن حذف تصنيف به منتجات
            $productsCount = $category->products()->count();
            if ($productsCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "❌ لا يمكن حذف التصنيف \"{$category->name}\" لأنه مرتبط بـ {$productsCount} منتج. أزل المنتجات أو غيّر تصنيفها أولاً.",
                ], 422);
            }

            $name = $category->name;
            $category->delete();

            return response()->json([
                'success' => true,
                'message' => "✅ تم حذف التصنيف \"{$name}\" بنجاح.",
            ]);

        } catch (\Exception $e) {
            Log::error('Category destroy failed', ['id' => $category->id, 'error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => '❌ حدث خطأ أثناء الحذف: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * تبديل حالة النشاط (AJAX)
     */
    public function toggleStatus(Category $category)
    {
        try {
            $category->update(['is_active' => !$category->is_active]);
            $status = $category->is_active ? 'نشط' : 'غير نشط';

            return response()->json([
                'success'   => true,
                'message'   => "✅ تم تغيير حالة التصنيف إلى: {$status}",
                'is_active' => $category->is_active,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * AJAX: قائمة التصنيفات للاستخدام في Select المنتجات
     */
    public function list()
    {
        $categories = Category::active()->ordered()->get(['id', 'name', 'color', 'icon']);
        return response()->json(['success' => true, 'categories' => $categories]);
    }

    // ─── Private Helpers ─────────────────────────────────────────────────────

    private function formatCategory(Category $category): array
    {
        return [
            'id'             => $category->id,
            'name'           => $category->name,
            'description'    => $category->description,
            'color'          => $category->color,
            'icon'           => $category->icon,
            'sort_order'     => $category->sort_order,
            'is_active'      => $category->is_active,
            'products_count' => $category->products_count ?? 0,
        ];
    }
}
