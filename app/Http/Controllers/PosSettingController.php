<?php

namespace App\Http\Controllers;

use App\Models\PosSetting;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PosSettingController extends Controller
{
    /**
     * عرض صفحة إعدادات الكاشير
     */
    public function index()
    {
        $settings = PosSetting::getSolo();
        
        $warehouses = Warehouse::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('pos.settings', compact('settings', 'warehouses'));
    }

    /**
     * تحديث إعدادات الكاشير
     */
    public function update(Request $request)
    {
        $settings = PosSetting::getSolo();

        $validated = $request->validate([
            'pos_name'               => 'required|string|max:100',
            'default_warehouse_id'   => 'nullable|integer|exists:warehouses,id',
            'default_payment_method' => 'required|string|in:cash,card,credit,multiple',
            'require_shift'          => 'nullable|boolean',
            'auto_print_receipt'     => 'nullable|boolean',
            'allow_negative_stock'   => 'nullable|boolean',
            'receipt_header_text'    => 'nullable|string|max:255',
            'receipt_footer_text'    => 'nullable|string|max:255',
        ], [
            'pos_name.required' => 'اسم نقطة البيع مطلوب.',
            'default_payment_method.in' => 'طريقة الدفع الافتراضية المحددة غير صالحة.',
        ]);

        try {
            $settings->update([
                'pos_name'               => $validated['pos_name'],
                'default_warehouse_id'   => $validated['default_warehouse_id'] ?? null,
                'default_payment_method' => $validated['default_payment_method'],
                'require_shift'          => $request->has('require_shift'),
                'auto_print_receipt'     => $request->has('auto_print_receipt'),
                'allow_negative_stock'   => $request->has('allow_negative_stock'),
                'receipt_header_text'    => $validated['receipt_header_text'] ?? null,
                'receipt_footer_text'    => $validated['receipt_footer_text'] ?? null,
            ]);

            return redirect()
                ->route('pos.settings.index')
                ->with('success', '✅ تم حفظ إعدادات الكاشير بنجاح!');

        } catch (\Exception $e) {
            Log::error('POS settings update failed', ['error' => $e->getMessage()]);
            return back()
                ->withInput()
                ->with('error', '❌ حدث خطأ غير متوقع أثناء حفظ الإعدادات: ' . $e->getMessage());
        }
    }
}
