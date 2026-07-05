<?php

namespace App\Http\Controllers;

use App\Models\PosShift;
use App\Models\PosSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PosShiftController extends Controller
{
    /**
     * عرض صفحة فتح وردية جديدة
     */
    public function create()
    {
        // هل يوجد وردية مفتوحة بالفعل لهذا المستخدم؟
        $activeShift = PosShift::getActiveShift();

        if ($activeShift) {
            return redirect()->route('pos.index')
                ->with('info', 'لديك وردية مفتوحة بالفعل. يمكنك البيع مباشرة.');
        }

        return view('pos.shift-open');
    }

    /**
     * فتح وردية جديدة
     */
    public function open(Request $request)
    {
        // التحقق من أنه لا توجد وردية مفتوحة
        $activeShift = PosShift::getActiveShift();
        if ($activeShift) {
            return redirect()->route('pos.index')
                ->with('info', 'لديك وردية مفتوحة بالفعل.');
        }

        $validated = $request->validate([
            'opening_balance' => 'required|numeric|min:0|max:9999999',
            'notes'           => 'nullable|string|max:500',
        ], [
            'opening_balance.required' => 'رصيد افتتاح الصندوق مطلوب.',
            'opening_balance.numeric'  => 'رصيد الصندوق يجب أن يكون رقماً.',
            'opening_balance.min'      => 'رصيد الصندوق لا يمكن أن يكون سالباً.',
        ]);

        try {
            // ✅ الحالة الثانية: إغلاق تلقائي لأي وردية مفتوحة من يوم سابق قبل الإنشاء
            PosShift::autoCloseStaleShifts();

            $shift = PosShift::create([
                'user_id'         => auth()->id(),
                'opened_at'       => now(),
                'opening_balance' => $validated['opening_balance'],
                'status'          => PosShift::STATUS_OPEN,
                'total_sales'     => 0,
                'total_returns'   => 0,
                'sales_count'     => 0,
                'returns_count'   => 0,
                'notes'           => $validated['notes'] ?? null,
            ]);

            return redirect()->route('pos.index')
                ->with('success', '✅ تم فتح الوردية بنجاح! يمكنك البدء بالبيع الآن.');

        } catch (\Exception $e) {
            Log::error('Failed to open POS shift', ['error' => $e->getMessage()]);
            return back()->withInput()
                ->with('error', '❌ حدث خطأ أثناء فتح الوردية: ' . $e->getMessage());
        }
    }

    /**
     * عرض صفحة إغلاق الوردية الحالية
     */
    public function closeView()
    {
        $shift = PosShift::getActiveShift();

        if (!$shift) {
            return redirect()->route('pos.index')
                ->with('error', 'لا توجد وردية مفتوحة لإغلاقها.');
        }

        // Load user relationship
        $shift->load('user');

        // حساب الرصيد المتوقع (للمبيعات النقدية فقط)
        $cashSales = DB::table('sales_invoices')
            ->where('shift_id', $shift->id)
            ->where('status', 'confirmed')
            ->where('source', 'pos')
            ->where('payment_method', 'cash')
            ->whereNull('deleted_at')
            ->sum('total') ?? 0;

        $expectedBalance = (float) $shift->opening_balance + (float) $cashSales;

        return view('pos.shift-close', compact('shift', 'expectedBalance'));
    }

    /**
     * إغلاق الوردية وتسجيل التسليم
     */
    public function close(Request $request)
    {
        $shift = PosShift::getActiveShift();

        if (!$shift) {
            return redirect()->route('pos.index')
                ->with('error', 'لا توجد وردية مفتوحة.');
        }

        $validated = $request->validate([
            'closing_balance_actual' => 'required|numeric|min:0|max:9999999',
            'notes'                  => 'nullable|string|max:1000',
        ], [
            'closing_balance_actual.required' => 'الرصيد الفعلي للصندوق مطلوب.',
            'closing_balance_actual.numeric'  => 'الرصيد الفعلي يجب أن يكون رقماً.',
        ]);

        try {
            DB::beginTransaction();

            // 2. total_returns
            $totalReturns = DB::table('sales_returns')
                ->where('shift_id', $shift->id)
                ->where('status', 'confirmed')
                ->whereNull('deleted_at')
                ->sum('total') ?? 0;

            // 3. net_sales (mutated sum)
            $netSales = DB::table('sales_invoices')
                ->where('shift_id', $shift->id)
                ->where('status', 'confirmed')
                ->where('source', 'pos')
                ->whereNull('deleted_at')
                ->sum('total') ?? 0;

            // 1. total_sales (original sum = mutated sum + returns)
            $totalSales = $netSales + $totalReturns;

            // 4. expected_cash (cash transactions only = opening + mutated cash sales)
            $cashSales = DB::table('sales_invoices')
                ->where('shift_id', $shift->id)
                ->where('status', 'confirmed')
                ->where('source', 'pos')
                ->where('payment_method', 'cash')
                ->whereNull('deleted_at')
                ->sum('total') ?? 0;

            $expectedCash = (float) $shift->opening_balance + (float) $cashSales;
            $actualCash   = (float) $validated['closing_balance_actual'];
            $cashDifference = $actualCash - $expectedCash;

            $shift->update([
                'closed_at'                => now(),
                'closing_balance_actual'   => $actualCash,
                'closing_balance_expected' => $expectedCash,
                'difference'               => $cashDifference,
                'status'                   => PosShift::STATUS_CLOSED,
                'notes'                    => $validated['notes'] ?? $shift->notes,

                'total_sales'              => $totalSales,
                'total_returns'            => $totalReturns,
                'net_sales'                => $netSales,
                'expected_cash'            => $expectedCash,
                'actual_cash'              => $actualCash,
                'cash_difference'          => $cashDifference,
            ]);

            // ✅ الحالة الثالثة: حساب فرق الصندوق وحفظه صراحة
            $shift->computeAndSaveDifference();

            // ترحيل فرق الصندوق للأستاذ العام
            if (abs($cashDifference) >= 0.01) {
                app(\App\Services\Accounting\PostingService::class)->postPosShiftVariance($shift->fresh());
            }

            DB::commit();

            return redirect()->route('pos.shift.zreport', $shift->id)
                ->with('success', '✅ تم إغلاق الوردية وتسجيل التسليم بنجاح! تم توليد تقرير Z النهائي.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to close POS shift', ['error' => $e->getMessage()]);
            return back()->withInput()
                ->with('error', '❌ حدث خطأ أثناء إغلاق الوردية: ' . $e->getMessage());
        }
    }

    /**
     * سجل جميع الورديات (للمدير)
     */
    public function history(Request $request)
    {
        $shiftsQuery = PosShift::with('user')
            ->latest('opened_at');

        // فلترة بالتاريخ
        if ($request->filled('date_from')) {
            $shiftsQuery->whereDate('opened_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $shiftsQuery->whereDate('opened_at', '<=', $request->date_to);
        }
        // فلترة بالحالة
        if ($request->filled('status')) {
            $shiftsQuery->where('status', $request->status);
        }

        $shifts = $shiftsQuery->paginate(15)->withQueryString();

        // إحصائيات سريعة
        $stats = [
            'total_shifts'  => PosShift::count(),
            'open_shifts'   => PosShift::open()->count(),
            'today_sales'   => PosShift::whereDate('opened_at', today())->sum('total_sales'),
            'today_shifts'  => PosShift::whereDate('opened_at', today())->count(),
        ];

        return view('pos.history', compact('shifts', 'stats'));
    }

    /**
     * X Report — تقرير الوردية الحالية بدون إغلاق (للكاشير والمدير)
     */
    public function xReport()
    {
        $shift = PosShift::getActiveShift();

        if (!$shift) {
            return redirect()->route('pos.index')
                ->with('error', 'لا توجد وردية مفتوحة حالياً.');
        }

        $shift->load('user');

        // توزيع المبيعات حسب طريقة الدفع
        $salesByMethod = DB::table('sales_invoices')
            ->where('shift_id', $shift->id)
            ->where('source', 'pos')
            ->whereNull('deleted_at')
            ->select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(total) as total'))
            ->groupBy('payment_method')
            ->get()
            ->keyBy('payment_method');

        // حساب المبيعات النقدية للوردية
        $cashSales = DB::table('sales_invoices')
            ->where('shift_id', $shift->id)
            ->where('status', 'confirmed')
            ->where('source', 'pos')
            ->where('payment_method', 'cash')
            ->whereNull('deleted_at')
            ->sum('total') ?? 0;

        $expectedBalance = (float) $shift->opening_balance + (float) $cashSales;

        return view('pos.x-report', compact('shift', 'salesByMethod', 'expectedBalance'));
    }

    /**
     * Z Report — تقرير إغلاق الوردية النهائي (الطباعة والملخص للمحاسب/المدير)
     */
    public function zReport($id)
    {
        $shift = PosShift::with('user')->findOrFail($id);

        // توزيع المبيعات حسب طريقة الدفع
        $salesByMethod = DB::table('sales_invoices')
            ->where('shift_id', $shift->id)
            ->where('source', 'pos')
            ->whereNull('deleted_at')
            ->select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(total) as total'))
            ->groupBy('payment_method')
            ->get()
            ->keyBy('payment_method');

        $expectedBalance = $shift->expected_cash;

        return view('pos.z-report', compact('shift', 'salesByMethod', 'expectedBalance'));
    }
}

