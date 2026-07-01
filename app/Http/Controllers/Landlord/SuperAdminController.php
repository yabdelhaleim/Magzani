<?php

namespace App\Http\Controllers\Landlord;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\Plan;
use Illuminate\Http\Request;

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
        ]);

        Plan::create([
            'name' => $request->name,
            'slug' => $request->slug,
            'price' => $request->price,
            'billing_period' => $request->billing_period,
            'description' => $request->description,
            'features' => $request->features ?? [],
            'is_active' => $request->has('is_active'),
        ]);

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
            'slug' => 'required|string|max:255|unique:plans,slug,' . $plan->id,
            'price' => 'required|numeric|min:0',
            'billing_period' => 'required|in:monthly,yearly',
            'description' => 'nullable|string',
            'features' => 'nullable|array',
        ]);

        $plan->update([
            'name' => $request->name,
            'slug' => $request->slug,
            'price' => $request->price,
            'billing_period' => $request->billing_period,
            'description' => $request->description,
            'features' => $request->features ?? [],
            'is_active' => $request->has('is_active'),
        ]);

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
        ]);

        if ($request->plan_id !== 'custom') {
            $planExists = Plan::where('slug', $request->plan_id)->exists();
            if (!$planExists) {
                return back()->with('error', 'الباقة المحددة غير صالحة.')->withInput();
            }
        }

        try {
            // إنشاء المستأجر (هذا ينشئ قاعدة البيانات تلقائياً ويهجرها)
            $tenant = Tenant::create([
                'id' => $request->tenant_id,
                'plan_id' => $request->plan_id,
                'custom_features' => $request->plan_id === 'custom' ? ($request->custom_features ?? []) : [],
                'is_suspended' => false,
            ]);

            // إنشاء الدومين
            $tenant->domains()->create([
                'domain' => $request->tenant_id . '.localhost',
            ]);

            // تشغيل تهيئة وتلقيم قاعدة بيانات المستأجر
            $tenant->run(function () use ($request) {
                // 1. تشغيل Seeder الصلاحيات والأدوار
                $seeder = new \Database\Seeders\PermissionAndRoleSeeder();
                $seeder->run();

                // 2. إنشاء حساب المدير
                $user = \App\Models\User::create([
                    'name' => 'مدير النظام',
                    'email' => 'admin@' . $request->tenant_id . '.com',
                    'password' => bcrypt('password'),
                    'phone' => '',
                    'is_active' => true,
                    'role' => 'admin',
                ]);

                // إرفاق دور المدير
                $adminRole = \App\Models\Role::where('name', 'admin')->first();
                if ($adminRole) {
                    $user->assignRole($adminRole);
                }
            });

            return redirect()->route('super-admin.tenants.index')->with('success', 'تم تسجيل الشركة وتجهيز قاعدة البيانات بنجاح!');
        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ أثناء تسجيل الشركة: ' . $e->getMessage())->withInput();
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
        ]);

        if ($request->plan_id !== 'custom') {
            $planExists = Plan::where('slug', $request->plan_id)->exists();
            if (!$planExists) {
                return back()->with('error', 'الباقة المحددة غير صالحة.')->withInput();
            }
        }

        try {
            $tenant = Tenant::findOrFail($id);
            $tenant->update([
                'plan_id' => $request->plan_id,
                'custom_features' => $request->plan_id === 'custom' ? ($request->custom_features ?? []) : [],
            ]);

            return redirect()->route('super-admin.tenants.index')->with('success', 'تم تحديث إعدادات باقة العميل بنجاح!');
        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ أثناء التحديث: ' . $e->getMessage());
        }
    }

    public function tenantsToggleStatus($id)
    {
        try {
            $tenant = Tenant::findOrFail($id);
            $isSuspended = isset($tenant->is_suspended) ? $tenant->is_suspended : false;
            
            $tenant->update([
                'is_suspended' => !$isSuspended,
            ]);

            $message = !$isSuspended ? 'تم إيقاف حساب الشركة بنجاح!' : 'تم إعادة تنشيط حساب الشركة بنجاح!';
            return redirect()->route('super-admin.tenants.index')->with('success', $message);
        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ أثناء تعديل حالة الحساب: ' . $e->getMessage());
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
            return back()->with('error', 'حدث خطأ أثناء حذف الشركة: ' . $e->getMessage());
        }
    }
}
