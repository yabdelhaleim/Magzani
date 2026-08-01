<?php

namespace App\Http\Controllers\Landlord;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\PermissionAndRoleSeeder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SuperAdminController extends Controller
{
    /**
     * لوحة الإحصائيات العامة للمنصة
     */
    public function dashboard()
    {
        $tenantsCount = Tenant::count();
        $plansCount = Plan::where('is_active', true)->count();

        $tenants = Tenant::all();
        $plans = Plan::all()->keyBy('slug');

        $estimatedRevenue = 0;
        foreach ($tenants as $tenant) {
            $planId = $tenant->plan_id ?? ($tenant->data['plan_id'] ?? null);
            if ($planId && $planId !== 'custom') {
                $plan = $plans->get($planId);
                if ($plan) {
                    $estimatedRevenue += $plan->price;
                }
            }
        }

        $recentTenants = Tenant::with('domains')->latest()->take(5)->get();

        return view('landlord.dashboard', compact('tenantsCount', 'plansCount', 'estimatedRevenue', 'recentTenants'));
    }

    /**
     * ==================== إدارة الباقات (Plans) ====================
     */
    public function plansIndex()
    {
        $plans = Plan::all();

        return view('landlord.plans.index', compact('plans'));
    }

    public function plansCreate()
    {
        return view('landlord.plans.create');
    }

    public function plansStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:plans,slug',
            'price' => 'required|numeric|min:0',
            'billing_period' => 'required|in:monthly,yearly',
            'description' => 'nullable|string',
            'features' => 'nullable|array',
            'features.*' => ['string', Rule::in(Tenant::acceptedFeatureKeys())],
            'value_props' => 'nullable|array',
            'value_props.*' => 'nullable|string|max:255',
            'display_label' => 'nullable|string|max:255',
            'is_featured' => 'sometimes|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $features = Tenant::normalizeFeatureKeys($request->input('features', []));
        $plan = Plan::create([
            'name' => $request->name,
            'slug' => $request->slug,
            'price' => $request->price,
            'billing_period' => $request->billing_period,
            'description' => $request->description,
            'features' => $features,
            'value_props' => array_values(array_filter($request->value_props ?? [], fn ($v) => filled($v))),
            'display_label' => $request->display_label,
            'is_featured' => $request->boolean('is_featured'),
            'sort_order' => (int) ($request->sort_order ?? 0),
            'is_active' => $request->has('is_active'),
        ]);
        $this->syncPlanFeatures($plan, $features);

        return redirect()->route('super-admin.plans.index')->with('success', 'تم إنشاء الباقة بنجاح!');
    }

    public function plansEdit(Plan $plan)
    {
        return view('landlord.plans.edit', compact('plan'));
    }

    public function plansUpdate(Request $request, Plan $plan)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:plans,slug,'.$plan->id,
            'price' => 'required|numeric|min:0',
            'billing_period' => 'required|in:monthly,yearly',
            'description' => 'nullable|string',
            'features' => 'nullable|array',
            'features.*' => ['string', Rule::in(Tenant::acceptedFeatureKeys())],
            'value_props' => 'nullable|array',
            'value_props.*' => 'nullable|string|max:255',
            'display_label' => 'nullable|string|max:255',
            'is_featured' => 'sometimes|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $oldSlug = $plan->slug;
        $features = Tenant::normalizeFeatureKeys($request->input('features', []));

        $plan->update([
            'name' => $request->name,
            'slug' => $request->slug,
            'price' => $request->price,
            'billing_period' => $request->billing_period,
            'description' => $request->description,
            'features' => $features,
            'value_props' => array_values(array_filter($request->value_props ?? [], fn ($v) => filled($v))),
            'display_label' => $request->display_label,
            'is_featured' => $request->boolean('is_featured'),
            'sort_order' => (int) ($request->sort_order ?? 0),
            'is_active' => $request->has('is_active'),
        ]);
        $this->syncPlanFeatures($plan, $features);
        $this->invalidatePlanTenantCaches([$oldSlug, $plan->slug]);

        return redirect()->route('super-admin.plans.index')->with('success', 'تم تحديث الباقة بنجاح!');
    }

    public function plansDestroy(Plan $plan)
    {
        $plan->delete();

        return redirect()->route('super-admin.plans.index')->with('success', 'تم حذف الباقة بنجاح!');
    }

    /**
     * ==================== إدارة الشركات المشتركة (Tenants) ====================
     */
    public function tenantsIndex()
    {
        $tenants = Tenant::with('domains')->get();

        return view('landlord.tenants.index', compact('tenants'));
    }

    public function tenantsCreate()
    {
        $plans = Plan::where('is_active', true)->get();

        return view('landlord.tenants.create', compact('plans'));
    }

    public function tenantsStore(Request $request)
    {
        $request->validate([
            'tenant_id' => 'required|string|alpha_dash|lowercase|max:255|unique:tenants,id',
            'plan_id' => 'required|string|max:255',
            'custom_features' => 'nullable|array',
            'custom_features.*' => ['string', Rule::in(Tenant::acceptedFeatureKeys())],
        ]);

        if ($request->plan_id !== 'custom') {
            $planExists = Plan::where('slug', $request->plan_id)->exists();
            if (! $planExists) {
                return back()->with('error', 'الباقة المحددة غير صالحة.')->withInput();
            }
        }

        try {
            // إنشاء المستأجر (هذا ينشئ قاعدة البيانات تلقائياً ويهجرها)
            $customFeatures = Tenant::normalizeFeatureKeys($request->input('custom_features', []));
            $tenant = Tenant::create([
                'id' => $request->tenant_id,
                'plan_id' => $request->plan_id,
                'custom_features' => $request->plan_id === 'custom' ? $customFeatures : [],
                'is_suspended' => false,
            ]);

            // إنشاء الدومين
            $tenantDomainSuffix = (string) config('tenancy.tenant_domain_suffix', 'localhost');
            $tenant->domains()->create([
                'domain' => $request->tenant_id.'.'.$tenantDomainSuffix,
            ]);

            // تشغيل تهيئة وتلقيم قاعدة بيانات المستأجر
            $tenant->run(function () use ($request) {
                // 1. تشغيل Seeder الصلاحيات والأدوار
                $seeder = new PermissionAndRoleSeeder;
                $seeder->run();

                // 2. إنشاء حساب المدير
                $user = User::create([
                    'name' => 'مدير النظام',
                    'email' => 'admin@'.$request->tenant_id.'.com',
                    'password' => bcrypt('password'),
                    'phone' => '',
                    'is_active' => true,
                    'role' => 'admin',
                ]);

                // إرفاق دور المدير
                $adminRole = Role::where('name', 'admin')->first();
                if ($adminRole) {
                    $user->assignRole($adminRole);
                }
            });

            return redirect()->route('super-admin.tenants.index')->with('success', 'تم تسجيل الشركة وتجهيز قاعدة البيانات بنجاح!');
        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ أثناء تسجيل الشركة: '.$e->getMessage())->withInput();
        }
    }

    public function tenantsEdit($id)
    {
        $tenant = Tenant::findOrFail($id);
        $plans = Plan::where('is_active', true)->get();

        return view('landlord.tenants.edit', compact('tenant', 'plans'));
    }

    public function tenantsUpdate(Request $request, $id)
    {
        $request->validate([
            'plan_id' => 'required|string|max:255',
            'custom_features' => 'nullable|array',
            'custom_features.*' => ['string', Rule::in(Tenant::acceptedFeatureKeys())],
        ]);

        if ($request->plan_id !== 'custom') {
            $planExists = Plan::where('slug', $request->plan_id)->exists();
            if (! $planExists) {
                return back()->with('error', 'الباقة المحددة غير صالحة.')->withInput();
            }
        }

        try {
            $tenant = Tenant::findOrFail($id);

            $rawFeatures = $request->plan_id === 'custom'
                ? Tenant::normalizeFeatureKeys($request->input('custom_features', []))
                : [];

            $tenant->update([
                'plan_id' => $request->plan_id,
                'custom_features' => $request->plan_id === 'custom' ? $rawFeatures : [],
            ]);

            // 🐛 Bug #4 Fix: إبطال كاش المميزات عند تغيير الخطة
            $tenant->invalidateFeatureCache();

            return redirect()->route('super-admin.tenants.index')->with('success', 'تم تحديث إعدادات باقة العميل بنجاح!');
        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ أثناء التحديث: '.$e->getMessage());
        }
    }

    public function tenantsToggleStatus($id)
    {
        try {
            $tenant = Tenant::findOrFail($id);
            $isSuspended = isset($tenant->is_suspended) ? $tenant->is_suspended : false;

            $tenant->update([
                'is_suspended' => ! $isSuspended,
            ]);

            // إبطال الكاش عند تغيير الحالة
            $tenant->invalidateFeatureCache();

            $message = ! $isSuspended ? 'تم إيقاف حساب الشركة بنجاح!' : 'تم إعادة تنشيط حساب الشركة بنجاح!';

            return redirect()->route('super-admin.tenants.index')->with('success', $message);
        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ أثناء تعديل حالة الحساب: '.$e->getMessage());
        }
    }

    public function tenantsDestroy($id)
    {
        try {
            $tenant = Tenant::findOrFail($id);

            // حذف الدومين أولاً
            $tenant->domains()->delete();

            // حذف المستأجر (سيقوم تلقائياً بحذف قاعدة البيانات عبر Stancl Tenancy)
            $tenant->delete();

            return redirect()->route('super-admin.tenants.index')->with('success', 'تم حذف الشركة وكافة قواعد بياناتها بنجاح!');
        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ أثناء حذف الشركة: '.$e->getMessage());
        }
    }

    /**
     * Normalize feature input and persist it in the canonical plan_features
     * representation used by tenant middleware.
     */
    private function syncPlanFeatures(Plan $plan, array $features): void
    {
        $features = Tenant::normalizeFeatureKeys($features);

        $plan->featuresList()->delete();

        foreach ($features as $feature) {
            $plan->featuresList()->create([
                'feature_key' => $feature,
                'is_enabled' => true,
                'limit_value' => null,
            ]);
        }
    }

    /**
     * Invalidate feature caches for tenants using one of the supplied slugs.
     * The tenant subscription fields live in the JSON data column, so this is
     * intentionally evaluated against the already-loaded central tenant list.
     *
     * @param  array<int, string|null>  $slugs
     */
    private function invalidatePlanTenantCaches(array $slugs): void
    {
        $slugs = array_values(array_filter(array_unique($slugs)));

        if ($slugs === []) {
            return;
        }

        Tenant::all()
            ->filter(function (Tenant $tenant) use ($slugs): bool {
                $planId = $tenant->plan_id ?? ($tenant->data['plan_id'] ?? null);

                return in_array($planId, $slugs, true);
            })
            ->each(fn (Tenant $tenant) => $tenant->invalidateFeatureCache());
    }
}
