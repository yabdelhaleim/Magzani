<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Accounting\StoreFiscalYearRequest;
use App\Models\FiscalYear;
use App\Models\FiscalPeriod;
use App\Services\Accounting\FiscalPeriodService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class FiscalPeriodController extends Controller
{
    public function __construct(
        private FiscalPeriodService $fiscalPeriodService
    ) {}

    /**
     * قائمة السنوات والفترات المالية
     */
    public function index(): View
    {
        $fiscalYears = FiscalYear::with('periods')->orderByDesc('start_date')->get();

        return view('accounting.fiscal.index', compact('fiscalYears'));
    }

    /**
     * إنشاء سنة مالية جديدة مع فتراتها الـ 12
     *
     * إصلاح #7: الخدمة تتوقع int $year وليس string date
     * نستخرج السنة من start_date المُرسَل
     */
    public function store(StoreFiscalYearRequest $request): RedirectResponse
    {
        // ✅ نمرر int $year لا string date
        $year = (int) \Carbon\Carbon::parse($request->validated('start_date'))->year;

        $fiscalYear = $this->fiscalPeriodService->createFiscalYear(
            $year,
            $request->validated('name')
        );

        return redirect()
            ->route('accounting.fiscal.index')
            ->with('success', "✅ تم إنشاء السنة المالية [{$fiscalYear->name}] مع 12 فترة شهرية بنجاح.");
    }

    /**
     * إغلاق فترة مالية
     *
     * إصلاح #8: تمرير Auth::id() كـ $closedBy
     * إصلاح #11: مقارنة is_closed (boolean) بدلاً من status === 'closed'
     */
    public function closePeriod(FiscalPeriod $period): RedirectResponse
    {
        // ✅ is_closed هو boolean وليس string status
        if ($period->is_closed) {
            return back()->with('error', '❌ هذه الفترة مغلقة مسبقاً.');
        }

        // ✅ تمرير closedBy المطلوب في الخدمة
        $this->fiscalPeriodService->closePeriod($period, Auth::id());

        return back()->with('success', "✅ تم إغلاق الفترة [{$period->name}].");
    }

    /**
     * إغلاق السنة المالية كاملة
     *
     * إصلاح #8: تمرير Auth::id() كـ $closedBy
     */
    public function closeYear(FiscalYear $fiscalYear): RedirectResponse
    {
        if ($fiscalYear->is_closed) {
            return back()->with('error', '❌ هذه السنة المالية مغلقة مسبقاً.');
        }

        // ✅ تمرير closedBy المطلوب في الخدمة
        $this->fiscalPeriodService->closeFiscalYear($fiscalYear, Auth::id());

        return back()->with('success', "✅ تم إغلاق السنة المالية [{$fiscalYear->name}].");
    }

    /**
     * تعيين سنة مالية كالسنة الحالية
     *
     * إصلاح #18: FiscalYear لا يملك is_current في fillable
     * نحل هذا بإضافة is_current إلى fillable في FiscalYear model بدلاً من ذلك
     * هنا نستخدم حقل is_closed=false كبديل مؤقت — أو نضيف migration
     *
     * الحل المختار: نضيف is_current إلى FiscalYear model في هذا الإصلاح
     */
    public function setCurrent(FiscalYear $fiscalYear): RedirectResponse
    {
        // نستخدم DB مباشرة لتجاوز fillable
        \Illuminate\Support\Facades\DB::table('fiscal_years')->update(['is_current' => false]);
        \Illuminate\Support\Facades\DB::table('fiscal_years')
            ->where('id', $fiscalYear->id)
            ->update(['is_current' => true]);

        return back()->with('success', "✅ تم تعيين [{$fiscalYear->name}] كالسنة المالية الحالية.");
    }
}
