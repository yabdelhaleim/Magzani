# Raw Materials Templates Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add a reusable raw materials template system to the manufacturing orders module, with CRUD pages and the ability to load templates into order creation.

**Architecture:** Two new Eloquent models (`RawMaterialTemplate`, `RawMaterialTemplateItem`) with a parent-child relationship. A new controller handles CRUD + AJAX load. Blade views follow the existing `manufacturing-orders` visual patterns (`.mfg-card`, `.mfg-table`, CSS variables). The order create page gets a "Load from template" button that populates the wood components table via AJAX.

**Tech Stack:** Laravel, Blade, Alpine.js (for template selector dropdown), vanilla JS (for dynamic table rows)

---

### Task 1: Database Migration — `raw_material_templates` table

**Files:**
- Create: `database/migrations/2026_05_04_000001_create_raw_material_templates_table.php`

- [ ] **Step 1: Create the migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('raw_material_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('raw_material_templates');
    }
};
```

- [ ] **Step 2: Run the migration**

Run: `php artisan migrate`
Expected: Table created successfully.

- [ ] **Step 3: Commit**

```bash
git add database/migrations/2026_05_04_000001_create_raw_material_templates_table.php
git commit -m "add raw_material_templates migration"
```

---

### Task 2: Database Migration — `raw_material_template_items` table

**Files:**
- Create: `database/migrations/2026_05_04_000002_create_raw_material_template_items_table.php`

- [ ] **Step 1: Create the migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('raw_material_template_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('raw_material_templates')->cascadeOnDelete();
            $table->enum('component_type', ['فرش', 'روباط', 'شاسية', 'دكم']);
            $table->decimal('thickness_cm', 10, 4)->default(0);
            $table->decimal('width_cm', 10, 4)->default(0);
            $table->decimal('length_cm', 10, 4)->default(0);
            $table->decimal('quantity', 10, 4)->default(1);
            $table->decimal('price_per_meter', 10, 4)->default(0);
            $table->decimal('total_cost', 12, 4)->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('raw_material_template_items');
    }
};
```

- [ ] **Step 2: Run the migration**

Run: `php artisan migrate`
Expected: Table created successfully.

- [ ] **Step 3: Commit**

```bash
git add database/migrations/2026_05_04_000002_create_raw_material_template_items_table.php
git commit -m "add raw_material_template_items migration"
```

---

### Task 3: Eloquent Models

**Files:**
- Create: `app/Models/RawMaterialTemplate.php`
- Create: `app/Models/RawMaterialTemplateItem.php`

- [ ] **Step 1: Create `RawMaterialTemplate` model**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RawMaterialTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'notes',
        'created_by',
        'updated_by',
    ];

    public function items()
    {
        return $this->hasMany(RawMaterialTemplateItem::class, 'template_id')->orderBy('sort_order');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getTotalCostAttribute(): float
    {
        return $this->items->sum('total_cost');
    }

    public function getItemsCountAttribute(): int
    {
        return $this->items->count();
    }
}
```

- [ ] **Step 2: Create `RawMaterialTemplateItem` model**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RawMaterialTemplateItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_id',
        'component_type',
        'thickness_cm',
        'width_cm',
        'length_cm',
        'quantity',
        'price_per_meter',
        'total_cost',
        'sort_order',
    ];

    protected $casts = [
        'thickness_cm' => 'decimal:4',
        'width_cm' => 'decimal:4',
        'length_cm' => 'decimal:4',
        'quantity' => 'decimal:4',
        'price_per_meter' => 'decimal:4',
        'total_cost' => 'decimal:4',
        'sort_order' => 'integer',
    ];

    public function template()
    {
        return $this->belongsTo(RawMaterialTemplate::class, 'template_id');
    }

    public function recalculate(): void
    {
        $this->total_cost = (float) $this->length_cm * (float) $this->quantity * (float) $this->price_per_meter;
        $this->saveQuietly();
    }
}
```

- [ ] **Step 3: Commit**

```bash
git add app/Models/RawMaterialTemplate.php app/Models/RawMaterialTemplateItem.php
git commit -m "add RawMaterialTemplate and RawMaterialTemplateItem models"
```

---

### Task 4: Controller — RawMaterialTemplateController

**Files:**
- Create: `app/Http/Controllers/RawMaterialTemplateController.php`

- [ ] **Step 1: Create the controller**

Follow the same patterns as `ManufacturingOrderController`: constructor-injected service, try/catch with Log, Arabic flash messages, `back()->withInput()` on error.

```php
<?php

namespace App\Http\Controllers;

use App\Models\RawMaterialTemplate;
use App\Models\RawMaterialTemplateItem;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RawMaterialTemplateController extends Controller
{
    public function index(Request $request)
    {
        $templates = RawMaterialTemplate::with('items')
            ->latest()
            ->paginate(20);

        return view('manufacturing-orders.raw-materials.index', compact('templates'));
    }

    public function create()
    {
        return view('manufacturing-orders.raw-materials.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.component_type' => 'required|string|in:فرش,روباط,شاسية,دكم',
            'items.*.thickness_cm' => 'required|numeric|min:0',
            'items.*.width_cm' => 'required|numeric|min:0',
            'items.*.length_cm' => 'required|numeric|min:0',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'items.*.price_per_meter' => 'required|numeric|min:0',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $template = RawMaterialTemplate::create([
                    'name' => $validated['name'],
                    'notes' => $validated['notes'] ?? null,
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                ]);

                foreach ($validated['items'] as $index => $item) {
                    $totalCost = (float) $item['length_cm'] * (float) $item['quantity'] * (float) $item['price_per_meter'];

                    $template->items()->create([
                        'component_type' => $item['component_type'],
                        'thickness_cm' => $item['thickness_cm'],
                        'width_cm' => $item['width_cm'],
                        'length_cm' => $item['length_cm'],
                        'quantity' => $item['quantity'],
                        'price_per_meter' => $item['price_per_meter'],
                        'total_cost' => round($totalCost, 4),
                        'sort_order' => $index,
                    ]);
                }
            });

            return redirect()->route('manufacturing-orders.raw-materials.index')
                ->with('success', 'تم إنشاء قالب الخامات بنجاح');
        } catch (\Exception $e) {
            Log::error('Failed to create raw material template', ['error' => $e->getMessage()]);
            return back()->with('error', 'حدث خطأ أثناء إنشاء القالب')->withInput();
        }
    }

    public function show(string $id)
    {
        $template = RawMaterialTemplate::with('items')->findOrFail($id);

        return view('manufacturing-orders.raw-materials.show', compact('template'));
    }

    public function edit(string $id)
    {
        $template = RawMaterialTemplate::with('items')->findOrFail($id);

        return view('manufacturing-orders.raw-materials.edit', compact('template'));
    }

    public function update(Request $request, string $id)
    {
        $template = RawMaterialTemplate::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.component_type' => 'required|string|in:فرش,روباط,شاسية,دكم',
            'items.*.thickness_cm' => 'required|numeric|min:0',
            'items.*.width_cm' => 'required|numeric|min:0',
            'items.*.length_cm' => 'required|numeric|min:0',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'items.*.price_per_meter' => 'required|numeric|min:0',
        ]);

        try {
            DB::transaction(function () use ($template, $validated) {
                $template->update([
                    'name' => $validated['name'],
                    'notes' => $validated['notes'] ?? null,
                    'updated_by' => Auth::id(),
                ]);

                $template->items()->delete();

                foreach ($validated['items'] as $index => $item) {
                    $totalCost = (float) $item['length_cm'] * (float) $item['quantity'] * (float) $item['price_per_meter'];

                    $template->items()->create([
                        'component_type' => $item['component_type'],
                        'thickness_cm' => $item['thickness_cm'],
                        'width_cm' => $item['width_cm'],
                        'length_cm' => $item['length_cm'],
                        'quantity' => $item['quantity'],
                        'price_per_meter' => $item['price_per_meter'],
                        'total_cost' => round($totalCost, 4),
                        'sort_order' => $index,
                    ]);
                }
            });

            return redirect()->route('manufacturing-orders.raw-materials.index')
                ->with('success', 'تم تحديث قالب الخامات بنجاح');
        } catch (\Exception $e) {
            Log::error('Failed to update raw material template', ['error' => $e->getMessage()]);
            return back()->with('error', 'حدث خطأ أثناء تحديث القالب')->withInput();
        }
    }

    public function destroy(string $id)
    {
        $template = RawMaterialTemplate::findOrFail($id);

        try {
            $template->items()->delete();
            $template->delete();

            return redirect()->route('manufacturing-orders.raw-materials.index')
                ->with('success', 'تم حذف قالب الخامات بنجاح');
        } catch (\Exception $e) {
            Log::error('Failed to delete raw material template', ['error' => $e->getMessage()]);
            return back()->with('error', 'حدث خطأ أثناء حذف القالب');
        }
    }

    public function load(string $id): JsonResponse
    {
        $template = RawMaterialTemplate::with('items')->findOrFail($id);

        return response()->json([
            'success' => true,
            'template' => [
                'id' => $template->id,
                'name' => $template->name,
                'items' => $template->items->map(fn ($item) => [
                    'component_type' => $item->component_type,
                    'thickness_cm' => $item->thickness_cm,
                    'width_cm' => $item->width_cm,
                    'length_cm' => $item->length_cm,
                    'quantity' => $item->quantity,
                    'price_per_cubic_meter' => $item->price_per_meter,
                ])->values()->all(),
            ],
        ]);
    }

    public function listAll(): JsonResponse
    {
        $templates = RawMaterialTemplate::select('id', 'name')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'templates' => $templates,
        ]);
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add app/Http/Controllers/RawMaterialTemplateController.php
git commit -m "add RawMaterialTemplateController with CRUD and load endpoints"
```

---

### Task 5: Routes

**Files:**
- Modify: `routes/web.php`

- [ ] **Step 1: Add routes inside the existing `manufacturing-orders` route group**

The `manufacturing-orders` route group is at approximately line 211 of `routes/web.php`. Add these routes **before** the `/{manufacturingOrder}` wildcard line (which is `Route::get('/{manufacturingOrder}', ...)`). Place them after the `Route::post('/calculate', ...)` line.

```php
// Raw material templates
Route::get('/raw-materials', [RawMaterialTemplateController::class, 'index'])->name('raw-materials.index');
Route::get('/raw-materials/create', [RawMaterialTemplateController::class, 'create'])->name('raw-materials.create');
Route::post('/raw-materials', [RawMaterialTemplateController::class, 'store'])->name('raw-materials.store');
Route::get('/raw-materials/{rawMaterialTemplate}', [RawMaterialTemplateController::class, 'show'])->name('raw-materials.show');
Route::get('/raw-materials/{rawMaterialTemplate}/edit', [RawMaterialTemplateController::class, 'edit'])->name('raw-materials.edit');
Route::put('/raw-materials/{rawMaterialTemplate}', [RawMaterialTemplateController::class, 'update'])->name('raw-materials.update');
Route::delete('/raw-materials/{rawMaterialTemplate}', [RawMaterialTemplateController::class, 'destroy'])->name('raw-materials.destroy');
Route::post('/raw-materials/{rawMaterialTemplate}/load', [RawMaterialTemplateController::class, 'load'])->name('raw-materials.load');
Route::post('/raw-materials/list', [RawMaterialTemplateController::class, 'listAll'])->name('raw-materials.list');
```

Also add the import at the top of `routes/web.php`:

```php
use App\Http\Controllers\RawMaterialTemplateController;
```

- [ ] **Step 2: Verify routes are registered**

Run: `php artisan route:list --name=raw-materials`
Expected: All 9 routes listed with correct URIs and names.

- [ ] **Step 3: Commit**

```bash
git add routes/web.php
git commit -m "add raw material template routes"
```

---

### Task 6: Sidebar Menu Item

**Files:**
- Modify: `resources/views/layouts/app.blade.php`

- [ ] **Step 1: Add "انشاء الخامات" link inside the التصنيع submenu**

In the sidebar file at line ~939-948, inside the `التصنيع` submenu `<div class="sub-menu">`, add a divider and link **after** the "سجل الصرف" item (the last item). The full submenu should become:

```html
<div class="sub-menu" :class="open ? 'open' : ''">
    <a href="{{ route('manufacturing-orders.index') }}"  class="sub-item {{ request()->routeIs('manufacturing-orders.index')  ? 'active' : '' }}"><span class="dot"></span>أوامر التصنيع</a>
    <a href="{{ route('manufacturing-orders.create') }}" class="sub-item {{ request()->routeIs('manufacturing-orders.create') ? 'active' : '' }}"><span class="dot"></span>إنشاء أمر تصنيع</a>
    <div style="height:1px;background:rgba(255,255,255,0.08);margin:6px 0;"></div>
    <a href="{{ route('manufacturing.index') }}"  class="sub-item {{ request()->routeIs('manufacturing.index')  ? 'active' : '' }}"><span class="dot"></span>حسابات التكلفة</a>
    <a href="{{ route('manufacturing.create') }}" class="sub-item {{ request()->routeIs('manufacturing.create') ? 'active' : '' }}"><span class="dot"></span>حساب جديد</a>
    <div style="height:1px;background:rgba(255,255,255,0.08);margin:6px 0;"></div>
    <a href="{{ route('manufacturing.wood-stocks.index') }}" class="sub-item {{ request()->routeIs('manufacturing.wood-stocks.*') ? 'active' : '' }}"><span class="dot"></span>مخزون الخشب الخام</a>
    <a href="{{ route('manufacturing.wood-dispensings.index') }}" class="sub-item {{ request()->routeIs('manufacturing.wood-dispensings.*') ? 'active' : '' }}"><span class="dot"></span>سجل الصرف</a>
    <div style="height:1px;background:rgba(255,255,255,0.08);margin:6px 0;"></div>
    <a href="{{ route('manufacturing-orders.raw-materials.index') }}" class="sub-item {{ request()->routeIs('manufacturing-orders.raw-materials.*') ? 'active' : '' }}"><span class="dot"></span>انشاء الخامات</a>
</div>
```

- [ ] **Step 2: Commit**

```bash
git add resources/views/layouts/app.blade.php
git commit -m "add raw materials template link to sidebar"
```

---

### Task 7: Index View — Template List

**Files:**
- Create: `resources/views/manufacturing-orders/raw-materials/index.blade.php`

- [ ] **Step 1: Create the index view**

This view lists all templates in a responsive table matching the existing `manufacturing-orders/index.blade.php` style. Use the same CSS variables, `.mfg-page`, `.mfg-card`, `.mfg-table`, `.badge`, `.btn` classes. Include a mobile card layout using `data-label` attributes.

```blade
@extends('layouts.app')

@section('title', 'قوالب الخامات')
@section('page-title', 'قوالب الخامات')

@push('styles')
<style>
    :root {
        --tf-bg: #f4f7fe; --tf-surface: #ffffff; --tf-border: #e4eaf7;
        --tf-indigo: #4f63d2; --tf-blue: #3a8ef0; --tf-green: #0faa7e;
        --tf-red: #dc2626; --tf-amber: #e8930a;
        --tf-text-h: #1a2140; --tf-text-b: #3d4f72; --tf-text-m: #7e90b0;
    }

    .mfg-page { background: var(--tf-bg); min-height: 100vh; padding: 16px; }
    @media (min-width: 1024px) { .mfg-page { padding: 26px 22px; } }

    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .mfg-section { animation: fadeUp 0.55s cubic-bezier(0.22,1,0.36,1) both; }

    .mfg-header {
        display: flex; justify-content: space-between; align-items: center;
        margin-bottom: 20px; flex-wrap: wrap; gap: 12px;
    }
    @media (min-width: 768px) { .mfg-header { margin-bottom: 24px; gap: 16px; } }

    .mfg-title {
        font-size: 18px; font-weight: 900; color: var(--tf-text-h);
        display: flex; align-items: center; gap: 10px;
    }
    @media (min-width: 768px) { .mfg-title { font-size: 24px; gap: 12px; } }
    .mfg-title i { color: var(--tf-indigo); }

    .mfg-card {
        background: var(--tf-surface); border-radius: 16px;
        border: 1px solid var(--tf-border); overflow: hidden; margin-bottom: 16px;
    }
    @media (min-width: 768px) { .mfg-card { border-radius: 18px; margin-bottom: 20px; } }

    .mfg-card-header {
        padding: 12px 16px; border-bottom: 1px solid var(--tf-border);
        display: flex; align-items: center; justify-content: space-between;
        flex-wrap: wrap; gap: 10px;
    }
    @media (min-width: 768px) { .mfg-card-header { padding: 16px 22px; gap: 12px; } }

    .mfg-card-title { font-size: 14px; font-weight: 800; margin: 0; }
    @media (min-width: 768px) { .mfg-card-title { font-size: 16px; } }

    .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }

    .mfg-table { width: 100%; border-collapse: collapse; font-size: 11px; }
    @media (min-width: 640px) { .mfg-table { font-size: 12px; } }
    @media (min-width: 1024px) { .mfg-table { font-size: 14px; } }

    .mfg-table th {
        padding: 10px 12px; font-size: 10px; font-weight: 700;
        color: var(--tf-text-m); background: #f8faff; text-align: right;
        border-bottom: 1px solid var(--tf-border); white-space: nowrap;
    }
    @media (min-width: 640px) { .mfg-table th { padding: 12px 16px; font-size: 12px; } }

    .mfg-table td {
        padding: 10px 12px; font-size: 12px; color: var(--tf-text-b);
        border-bottom: 1px solid #f0f4f8;
    }
    @media (min-width: 640px) { .mfg-table td { padding: 12px 16px; font-size: 14px; } }

    .btn {
        display: inline-flex; align-items: center; justify-content: center;
        gap: 8px; padding: 8px 16px; border-radius: 10px; font-weight: 700;
        font-size: 13px; border: none; cursor: pointer; transition: all .3s;
        text-decoration: none;
    }
    @media (min-width: 768px) { .btn { padding: 10px 20px; font-size: 14px; } }
    .btn-primary { background: var(--tf-indigo); color: #fff; }
    .btn-green { background: var(--tf-green); color: #fff; }
    .btn-amber { background: var(--tf-amber); color: #fff; }
    .btn-red { background: var(--tf-red); color: #fff; }
    .btn-sm { padding: 6px 12px; font-size: 12px; }

    /* Mobile card layout */
    @media (max-width: 767px) {
        .mfg-table thead { display: none; }
        .mfg-table tbody tr {
            display: block; background: #f8faff; border-radius: 12px;
            padding: 12px; margin-bottom: 12px; border: 1px solid var(--tf-border);
        }
        .mfg-table tbody td {
            display: flex; align-items: center; gap: 8px;
            padding: 6px 0; border-top: none; text-align: right;
        }
        .mfg-table tbody td::before {
            content: attr(data-label); font-weight: 700; font-size: 11px;
            color: var(--tf-text-h); white-space: nowrap; flex-shrink: 0; min-width: 70px;
        }
        .mfg-table tbody td:last-child {
            justify-content: flex-end; padding-top: 8px;
            border-top: 1px solid var(--tf-border); margin-top: 4px;
        }
        .mfg-table tbody td:last-child::before { display: none; }
    }
</style>
@endpush

@section('content')
<div class="mfg-page">
    <div class="mfg-section">
        <div class="mfg-header">
            <div class="mfg-title">
                <i class="fas fa-boxes-stacked"></i>
                قوالب الخامات
            </div>
            <a href="{{ route('manufacturing-orders.raw-materials.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> إنشاء قالب جديد
            </a>
        </div>

        @if(session('success'))
        <div style="background:#ecfdf5; color:#047857; padding:14px 20px; border-radius:12px; margin-bottom:16px; font-weight:700;">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
        @endif

        @if(session('error'))
        <div style="background:#fee2e2; color:#dc2626; padding:14px 20px; border-radius:12px; margin-bottom:16px; font-weight:700;">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        </div>
        @endif

        <div class="mfg-card">
            <div class="mfg-card-header">
                <h3 class="mfg-card-title">جميع القوالب ({{ $templates->total() }})</h3>
            </div>
            <div class="table-responsive">
                <table class="mfg-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>اسم القالب</th>
                            <th>عدد المكونات</th>
                            <th>إجمالي التكلفة</th>
                            <th>تاريخ الإنشاء</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($templates as $template)
                        <tr>
                            <td data-label="#">{{ $template->id }}</td>
                            <td data-label="اسم القالب">
                                <a href="{{ route('manufacturing-orders.raw-materials.show', $template->id) }}"
                                   style="color:var(--tf-indigo); font-weight:700; text-decoration:none;">
                                    {{ $template->name }}
                                </a>
                            </td>
                            <td data-label="عدد المكونات">{{ $template->items_count }}</td>
                            <td data-label="إجمالي التكلفة">{{ number_format($template->total_cost, 2) }} ج.م</td>
                            <td data-label="تاريخ الإنشاء">{{ $template->created_at->format('Y-m-d') }}</td>
                            <td data-label="إجراءات">
                                <a href="{{ route('manufacturing-orders.raw-materials.show', $template->id) }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('manufacturing-orders.raw-materials.edit', $template->id) }}" class="btn btn-amber btn-sm">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" action="{{ route('manufacturing-orders.raw-materials.destroy', $template->id) }}"
                                      style="display:inline;" onsubmit="return confirm('هل أنت متأكد من حذف هذا القالب؟')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-red btn-sm">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" style="text-align:center; padding:40px; color:var(--tf-text-m);">
                                <i class="fas fa-inbox" style="font-size:32px; display:block; margin-bottom:10px;"></i>
                                لا توجد قوالب خامات بعد
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{ $templates->links() }}
    </div>
</div>
@endsection
```

- [ ] **Step 2: Commit**

```bash
git add resources/views/manufacturing-orders/raw-materials/index.blade.php
git commit -m "add raw materials template index view"
```

---

### Task 8: Create View — Template Form

**Files:**
- Create: `resources/views/manufacturing-orders/raw-materials/create.blade.php`

- [ ] **Step 1: Create the form view**

This view has a name input and a dynamic components table with JS to add/remove rows and auto-calculate costs. Uses the same CSS patterns as `manufacturing-orders/create.blade.php`. The form posts `items[]` array.

```blade
@extends('layouts.app')

@section('title', 'إنشاء قالب خامات')
@section('page-title', 'إنشاء قالب خامات')

@push('styles')
<style>
    :root {
        --tf-bg: #f4f7fe; --tf-surface: #ffffff; --tf-border: #e4eaf7;
        --tf-indigo: #4f63d2; --tf-blue: #3a8ef0; --tf-green: #0faa7e;
        --tf-red: #dc2626; --tf-amber: #e8930a;
        --tf-text-h: #1a2140; --tf-text-b: #3d4f72; --tf-text-m: #7e90b0;
    }

    .mfg-page { background: var(--tf-bg); min-height: 100vh; padding: 16px; }
    @media (min-width: 1024px) { .mfg-page { padding: 26px 22px; } }
    @media (max-width: 767px) { .mfg-page { padding-bottom: 100px; } }

    .mfg-title {
        font-size: 20px; font-weight: 900; color: var(--tf-text-h);
        display: flex; align-items: center; gap: 12px; margin-bottom: 20px;
    }
    @media (min-width: 768px) { .mfg-title { font-size: 24px; } }
    .mfg-title i { color: var(--tf-indigo); }

    .mfg-card {
        background: var(--tf-surface); border-radius: 16px;
        border: 1px solid var(--tf-border); overflow: hidden; margin-bottom: 16px;
    }
    @media (min-width: 768px) { .mfg-card { margin-bottom: 20px; border-radius: 18px; } }

    .mfg-card-header {
        padding: 12px 16px; border-bottom: 1px solid var(--tf-border);
        display: flex; align-items: center; gap: 10px;
    }
    @media (min-width: 768px) { .mfg-card-header { padding: 16px 22px; } }

    .mfg-card-title { font-size: 14px; font-weight: 800; margin: 0; }
    @media (min-width: 768px) { .mfg-card-title { font-size: 16px; } }

    .mfg-card-body { padding: 16px; }
    @media (min-width: 768px) { .mfg-card-body { padding: 22px; } }

    .btn {
        display: inline-flex; align-items: center; justify-content: center;
        gap: 8px; padding: 8px 16px; border-radius: 10px; font-weight: 700;
        font-size: 13px; border: none; cursor: pointer; transition: all .3s;
        text-decoration: none;
    }
    @media (min-width: 768px) { .btn { padding: 10px 20px; font-size: 14px; } }
    .btn-primary { background: var(--tf-indigo); color: #fff; }
    .btn-red { background: var(--tf-red); color: #fff; }
    .btn-green { background: var(--tf-green); color: #fff; }
    .btn-sm { padding: 6px 12px; font-size: 12px; }
    .btn-block { width: 100%; }

    .form-group { margin-bottom: 16px; }
    @media (min-width: 768px) { .form-group { margin-bottom: 20px; } }

    .form-label {
        display: block; font-size: 13px; font-weight: 700;
        color: var(--tf-text-h); margin-bottom: 6px;
    }
    @media (min-width: 768px) { .form-label { font-size: 14px; margin-bottom: 8px; } }

    .form-control {
        width: 100%; padding: 10px 12px; border: 1px solid var(--tf-border);
        border-radius: 10px; font-size: 14px; transition: all 0.3s; background: #fff;
    }
    .form-control:focus {
        outline: none; border-color: var(--tf-indigo);
        box-shadow: 0 0 0 3px rgba(79,99,210,0.1);
    }

    .input-sm {
        width: 100%; padding: 8px 10px; border: 1px solid var(--tf-border);
        border-radius: 8px; font-size: 13px; text-align: center;
        color: var(--tf-text-b); background: #fff;
    }

    .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }

    .mfg-table { width: 100%; border-collapse: collapse; font-size: 12px; }
    @media (min-width: 768px) { .mfg-table { font-size: 14px; } }

    .mfg-table th {
        background: var(--tf-bg); padding: 10px 8px; text-align: right;
        font-weight: 700; font-size: 11px; color: var(--tf-text-h); white-space: nowrap;
    }
    @media (min-width: 768px) { .mfg-table th { padding: 12px 10px; font-size: 12px; } }

    .mfg-table td { padding: 8px; border-top: 1px solid var(--tf-border); }
    @media (min-width: 768px) { .mfg-table td { padding: 10px; } }

    .remove-btn {
        background: var(--tf-red); color: white; border: none; border-radius: 8px;
        padding: 6px 10px; font-size: 12px; cursor: pointer;
        display: inline-flex; align-items: center; gap: 4px;
    }

    .info-box {
        background: linear-gradient(135deg, #ecfdf5, #f0f9ff);
        padding: 14px 16px; border-radius: 12px; margin-bottom: 16px;
        border: 2px solid var(--tf-green); display: flex;
        align-items: flex-start; gap: 12px;
    }
    @media (min-width: 768px) {
        .info-box { padding: 18px 22px; margin-bottom: 24px; border-radius: 16px; gap: 16px; }
    }
    .info-box i { color: var(--tf-green); font-size: 24px; flex-shrink: 0; }
    @media (min-width: 768px) { .info-box i { font-size: 32px; } }
    .info-box p { margin: 0; color: var(--tf-text-b); font-size: 13px; line-height: 1.6; }

    .action-buttons {
        display: flex; flex-direction: column; gap: 10px;
        position: fixed; bottom: 0; left: 0; right: 0;
        background: white; padding: 12px 16px;
        border-top: 1px solid var(--tf-border);
        box-shadow: 0 -4px 12px rgba(0,0,0,0.1); z-index: 100;
    }
    @media (min-width: 768px) {
        .action-buttons {
            position: static; flex-direction: row; background: transparent;
            padding: 0; border: none; box-shadow: none;
        }
    }

    /* Mobile card rows */
    @media (max-width: 767px) {
        .mfg-table thead { display: none; }
        .mfg-table tbody tr {
            display: block; background: #f8faff; border-radius: 12px;
            padding: 12px; margin-bottom: 12px; border: 1px solid var(--tf-border);
        }
        .mfg-table tbody td {
            display: flex; align-items: center; gap: 8px;
            padding: 6px 0; border-top: none; text-align: right;
        }
        .mfg-table tbody td::before {
            content: attr(data-label); font-weight: 700; font-size: 11px;
            color: var(--tf-text-h); white-space: nowrap; flex-shrink: 0; min-width: 70px;
        }
        .mfg-table tbody td .input-sm,
        .mfg-table tbody td .form-control,
        .mfg-table tbody td select.form-control {
            flex: 1; min-width: 0;
        }
        .mfg-table tbody td .cost-display {
            font-weight: 700; color: var(--tf-indigo);
        }
        .mfg-table tbody td:last-child {
            justify-content: flex-end; padding-top: 8px;
            border-top: 1px solid var(--tf-border); margin-top: 4px;
        }
        .mfg-table tbody td:last-child::before { display: none; }
    }
</style>
@endpush

@section('content')
<div class="mfg-page">
    <div class="mfg-title">
        <i class="fas fa-boxes-stacked"></i>
        إنشاء قالب خامات
    </div>

    <div class="info-box">
        <i class="fas fa-lightbulb"></i>
        <div>
            <p>
                <strong style="color:var(--tf-green);">يمكنك استخدام هذا القالب لاحقاً عند إنشاء أوامر التصنيع.</strong>
                <br>
                المكونات أدناه تمثل الخامات اللازمة لـ <strong>بالة واحدة</strong> فقط.
            </p>
        </div>
    </div>

    @if(session('error'))
    <div style="background:#fee2e2; color:#dc2626; padding:14px 20px; border-radius:12px; margin-bottom:16px; font-weight:700;">
        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
    </div>
    @endif

    @if($errors->any())
    <div style="background:#fee2e2; color:#dc2626; padding:14px 20px; border-radius:12px; margin-bottom:16px; font-weight:700;">
        <i class="fas fa-exclamation-triangle"></i> أخطاء في النموذج:
        <ul style="margin:10px 0 0 20px; padding:0;">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('manufacturing-orders.raw-materials.store') }}">
        @csrf

        <div class="mfg-card">
            <div class="mfg-card-header">
                <i class="fas fa-tag" style="color:var(--tf-blue);"></i>
                <h3 class="mfg-card-title">بيانات القالب</h3>
            </div>
            <div class="mfg-card-body">
                <div class="form-group">
                    <label class="form-label">اسم القالب</label>
                    <input type="text" name="name" class="form-control" required placeholder="مثال: فرش بالة 110×120" value="{{ old('name') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">ملاحظات (اختياري)</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="أي ملاحظات إضافية...">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        <div class="mfg-card">
            <div class="mfg-card-header">
                <i class="fas fa-cubes" style="color:var(--tf-green);"></i>
                <h3 class="mfg-card-title">مكونات القالب</h3>
            </div>
            <div class="mfg-card-body">
                <button type="button" class="btn btn-primary btn-sm btn-block" onclick="addItem()" style="margin-bottom:16px;">
                    <i class="fas fa-plus"></i> [+ إضافة مكون]
                </button>

                <div class="table-responsive">
                    <table class="mfg-table" id="items-table">
                        <thead>
                            <tr>
                                <th>النوع</th>
                                <th>السمك (سم)</th>
                                <th>العرض (سم)</th>
                                <th>الطول (م)</th>
                                <th>العدد</th>
                                <th>سعر المتر</th>
                                <th>التكلفة</th>
                                <th>إجراء</th>
                            </tr>
                        </thead>
                        <tbody id="items-body"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="action-buttons">
            <button type="submit" class="btn btn-green">
                <i class="fas fa-check"></i> حفظ القالب
            </button>
            <a href="{{ route('manufacturing-orders.raw-materials.index') }}" class="btn btn-red">
                <i class="fas fa-times"></i> إلغاء
            </a>
        </div>
    </form>
</div>

<script>
let itemIndex = 0;

function addItem(data = null) {
    itemIndex++;
    const tbody = document.getElementById('items-body');
    const row = document.createElement('tr');
    row.innerHTML = `
        <td data-label="النوع">
            <select name="items[${itemIndex}][component_type]" class="form-control" style="padding:6px 8px;">
                <option value="فرش" ${data && data.component_type === 'فرش' ? 'selected' : ''}>فرش</option>
                <option value="روباط" ${data && data.component_type === 'روباط' ? 'selected' : ''}>روباط</option>
                <option value="شاسية" ${data && data.component_type === 'شاسية' ? 'selected' : ''}>شاسية</option>
                <option value="دكم" ${data && data.component_type === 'دكم' ? 'selected' : ''}>دكم</option>
            </select>
        </td>
        <td data-label="السمك (سم)"><input type="number" name="items[${itemIndex}][thickness_cm]" class="input-sm" step="0.1" placeholder="2.5" value="${data ? data.thickness_cm : ''}" oninput="recalcRow(this)"></td>
        <td data-label="العرض (سم)"><input type="number" name="items[${itemIndex}][width_cm]" class="input-sm" step="0.1" placeholder="12" value="${data ? data.width_cm : ''}" oninput="recalcRow(this)"></td>
        <td data-label="الطول (م)"><input type="number" name="items[${itemIndex}][length_cm]" class="input-sm" step="0.01" placeholder="4.00" value="${data ? data.length_cm : ''}" oninput="recalcRow(this)"></td>
        <td data-label="العدد"><input type="number" name="items[${itemIndex}][quantity]" class="input-sm" value="${data ? data.quantity : '1'}" oninput="recalcRow(this)"></td>
        <td data-label="سعر المتر"><input type="number" name="items[${itemIndex}][price_per_meter]" class="input-sm" step="0.01" placeholder="0" value="${data ? data.price_per_meter : ''}" oninput="recalcRow(this)"></td>
        <td data-label="التكلفة"><span class="cost-display">0.00</span></td>
        <td data-label="إجراء"><button type="button" class="remove-btn" onclick="this.closest('tr').remove()"><i class="fas fa-trash"></i></button></td>
    `;
    tbody.appendChild(row);
    if (data) recalcRow(row.querySelector('input'));
}

function recalcRow(el) {
    const row = el.closest('tr');
    const inputs = row.querySelectorAll('input');
    const length = parseFloat(inputs[2]?.value) || 0;
    const quantity = parseFloat(inputs[3]?.value) || 0;
    const price = parseFloat(inputs[4]?.value) || 0;
    const cost = length * quantity * price;
    const display = row.querySelector('.cost-display');
    if (display) display.textContent = cost.toFixed(2);
}

addItem();
</script>
@endsection
```

- [ ] **Step 2: Commit**

```bash
git add resources/views/manufacturing-orders/raw-materials/create.blade.php
git commit -m "add raw materials template create view"
```

---

### Task 9: Edit View

**Files:**
- Create: `resources/views/manufacturing-orders/raw-materials/edit.blade.php`

- [ ] **Step 1: Create the edit view**

This is identical to the create view, but pre-populated with existing data and pointing to the update route. Uses `@method('PUT')`.

The view should extend the same styles as the create view. The key differences:
- Form action: `route('manufacturing-orders.raw-materials.update', $template->id)` with `@method('PUT')`
- Name input has `value="{{ $template->name }}"`
- Notes textarea has existing content
- JS `addItem()` is called for each existing item with pre-populated data from `$template->items`

Use the same `<style>` block as the create view (copy it). The page title changes to "تعديل قالب خامات".

```blade
@extends('layouts.app')

@section('title', 'تعديل قالب خامات')
@section('page-title', 'تعديل قالب خامات')

@push('styles')
{{-- Same CSS as create view - copy the entire <style> block from create.blade.php --}}
<style>
    :root {
        --tf-bg: #f4f7fe; --tf-surface: #ffffff; --tf-border: #e4eaf7;
        --tf-indigo: #4f63d2; --tf-blue: #3a8ef0; --tf-green: #0faa7e;
        --tf-red: #dc2626; --tf-amber: #e8930a;
        --tf-text-h: #1a2140; --tf-text-b: #3d4f72; --tf-text-m: #7e90b0;
    }

    .mfg-page { background: var(--tf-bg); min-height: 100vh; padding: 16px; }
    @media (min-width: 1024px) { .mfg-page { padding: 26px 22px; } }
    @media (max-width: 767px) { .mfg-page { padding-bottom: 100px; } }

    .mfg-title {
        font-size: 20px; font-weight: 900; color: var(--tf-text-h);
        display: flex; align-items: center; gap: 12px; margin-bottom: 20px;
    }
    @media (min-width: 768px) { .mfg-title { font-size: 24px; } }
    .mfg-title i { color: var(--tf-indigo); }

    .mfg-card {
        background: var(--tf-surface); border-radius: 16px;
        border: 1px solid var(--tf-border); overflow: hidden; margin-bottom: 16px;
    }
    @media (min-width: 768px) { .mfg-card { margin-bottom: 20px; border-radius: 18px; } }

    .mfg-card-header {
        padding: 12px 16px; border-bottom: 1px solid var(--tf-border);
        display: flex; align-items: center; gap: 10px;
    }
    @media (min-width: 768px) { .mfg-card-header { padding: 16px 22px; } }

    .mfg-card-title { font-size: 14px; font-weight: 800; margin: 0; }
    @media (min-width: 768px) { .mfg-card-title { font-size: 16px; } }

    .mfg-card-body { padding: 16px; }
    @media (min-width: 768px) { .mfg-card-body { padding: 22px; } }

    .btn {
        display: inline-flex; align-items: center; justify-content: center;
        gap: 8px; padding: 8px 16px; border-radius: 10px; font-weight: 700;
        font-size: 13px; border: none; cursor: pointer; transition: all .3s;
        text-decoration: none;
    }
    @media (min-width: 768px) { .btn { padding: 10px 20px; font-size: 14px; } }
    .btn-primary { background: var(--tf-indigo); color: #fff; }
    .btn-red { background: var(--tf-red); color: #fff; }
    .btn-green { background: var(--tf-green); color: #fff; }
    .btn-sm { padding: 6px 12px; font-size: 12px; }
    .btn-block { width: 100%; }

    .form-group { margin-bottom: 16px; }
    @media (min-width: 768px) { .form-group { margin-bottom: 20px; } }

    .form-label {
        display: block; font-size: 13px; font-weight: 700;
        color: var(--tf-text-h); margin-bottom: 6px;
    }
    @media (min-width: 768px) { .form-label { font-size: 14px; margin-bottom: 8px; } }

    .form-control {
        width: 100%; padding: 10px 12px; border: 1px solid var(--tf-border);
        border-radius: 10px; font-size: 14px; transition: all 0.3s; background: #fff;
    }
    .form-control:focus {
        outline: none; border-color: var(--tf-indigo);
        box-shadow: 0 0 0 3px rgba(79,99,210,0.1);
    }

    .input-sm {
        width: 100%; padding: 8px 10px; border: 1px solid var(--tf-border);
        border-radius: 8px; font-size: 13px; text-align: center;
        color: var(--tf-text-b); background: #fff;
    }

    .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }

    .mfg-table { width: 100%; border-collapse: collapse; font-size: 12px; }
    @media (min-width: 768px) { .mfg-table { font-size: 14px; } }

    .mfg-table th {
        background: var(--tf-bg); padding: 10px 8px; text-align: right;
        font-weight: 700; font-size: 11px; color: var(--tf-text-h); white-space: nowrap;
    }
    @media (min-width: 768px) { .mfg-table th { padding: 12px 10px; font-size: 12px; } }

    .mfg-table td { padding: 8px; border-top: 1px solid var(--tf-border); }
    @media (min-width: 768px) { .mfg-table td { padding: 10px; } }

    .remove-btn {
        background: var(--tf-red); color: white; border: none; border-radius: 8px;
        padding: 6px 10px; font-size: 12px; cursor: pointer;
        display: inline-flex; align-items: center; gap: 4px;
    }

    .action-buttons {
        display: flex; flex-direction: column; gap: 10px;
        position: fixed; bottom: 0; left: 0; right: 0;
        background: white; padding: 12px 16px;
        border-top: 1px solid var(--tf-border);
        box-shadow: 0 -4px 12px rgba(0,0,0,0.1); z-index: 100;
    }
    @media (min-width: 768px) {
        .action-buttons {
            position: static; flex-direction: row; background: transparent;
            padding: 0; border: none; box-shadow: none;
        }
    }

    @media (max-width: 767px) {
        .mfg-table thead { display: none; }
        .mfg-table tbody tr {
            display: block; background: #f8faff; border-radius: 12px;
            padding: 12px; margin-bottom: 12px; border: 1px solid var(--tf-border);
        }
        .mfg-table tbody td {
            display: flex; align-items: center; gap: 8px;
            padding: 6px 0; border-top: none; text-align: right;
        }
        .mfg-table tbody td::before {
            content: attr(data-label); font-weight: 700; font-size: 11px;
            color: var(--tf-text-h); white-space: nowrap; flex-shrink: 0; min-width: 70px;
        }
        .mfg-table tbody td .input-sm,
        .mfg-table tbody td .form-control,
        .mfg-table tbody td select.form-control {
            flex: 1; min-width: 0;
        }
        .mfg-table tbody td .cost-display {
            font-weight: 700; color: var(--tf-indigo);
        }
        .mfg-table tbody td:last-child {
            justify-content: flex-end; padding-top: 8px;
            border-top: 1px solid var(--tf-border); margin-top: 4px;
        }
        .mfg-table tbody td:last-child::before { display: none; }
    }
</style>
@endpush

@section('content')
<div class="mfg-page">
    <div class="mfg-title">
        <i class="fas fa-boxes-stacked"></i>
        تعديل قالب خامات
    </div>

    @if(session('error'))
    <div style="background:#fee2e2; color:#dc2626; padding:14px 20px; border-radius:12px; margin-bottom:16px; font-weight:700;">
        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
    </div>
    @endif

    @if($errors->any())
    <div style="background:#fee2e2; color:#dc2626; padding:14px 20px; border-radius:12px; margin-bottom:16px; font-weight:700;">
        <i class="fas fa-exclamation-triangle"></i> أخطاء في النموذج:
        <ul style="margin:10px 0 0 20px; padding:0;">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('manufacturing-orders.raw-materials.update', $template->id) }}">
        @csrf @method('PUT')

        <div class="mfg-card">
            <div class="mfg-card-header">
                <i class="fas fa-tag" style="color:var(--tf-blue);"></i>
                <h3 class="mfg-card-title">بيانات القالب</h3>
            </div>
            <div class="mfg-card-body">
                <div class="form-group">
                    <label class="form-label">اسم القالب</label>
                    <input type="text" name="name" class="form-control" required value="{{ old('name', $template->name) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">ملاحظات (اختياري)</label>
                    <textarea name="notes" class="form-control" rows="2">{{ old('notes', $template->notes) }}</textarea>
                </div>
            </div>
        </div>

        <div class="mfg-card">
            <div class="mfg-card-header">
                <i class="fas fa-cubes" style="color:var(--tf-green);"></i>
                <h3 class="mfg-card-title">مكونات القالب</h3>
            </div>
            <div class="mfg-card-body">
                <button type="button" class="btn btn-primary btn-sm btn-block" onclick="addItem()" style="margin-bottom:16px;">
                    <i class="fas fa-plus"></i> [+ إضافة مكون]
                </button>

                <div class="table-responsive">
                    <table class="mfg-table" id="items-table">
                        <thead>
                            <tr>
                                <th>النوع</th>
                                <th>السمك (سم)</th>
                                <th>العرض (سم)</th>
                                <th>الطول (م)</th>
                                <th>العدد</th>
                                <th>سعر المتر</th>
                                <th>التكلفة</th>
                                <th>إجراء</th>
                            </tr>
                        </thead>
                        <tbody id="items-body">
                            @foreach($template->items as $index => $item)
                            <tr>
                                <td data-label="النوع">
                                    <select name="items[{{ $index + 1 }}][component_type]" class="form-control" style="padding:6px 8px;">
                                        <option value="فرش" {{ $item->component_type === 'فرش' ? 'selected' : '' }}>فرش</option>
                                        <option value="روباط" {{ $item->component_type === 'روباط' ? 'selected' : '' }}>روباط</option>
                                        <option value="شاسية" {{ $item->component_type === 'شاسية' ? 'selected' : '' }}>شاسية</option>
                                        <option value="دكم" {{ $item->component_type === 'دكم' ? 'selected' : '' }}>دكم</option>
                                    </select>
                                </td>
                                <td data-label="السمك (سم)"><input type="number" name="items[{{ $index + 1 }}][thickness_cm]" class="input-sm" step="0.1" value="{{ $item->thickness_cm }}" oninput="recalcRow(this)"></td>
                                <td data-label="العرض (سم)"><input type="number" name="items[{{ $index + 1 }}][width_cm]" class="input-sm" step="0.1" value="{{ $item->width_cm }}" oninput="recalcRow(this)"></td>
                                <td data-label="الطول (م)"><input type="number" name="items[{{ $index + 1 }}][length_cm]" class="input-sm" step="0.01" value="{{ $item->length_cm }}" oninput="recalcRow(this)"></td>
                                <td data-label="العدد"><input type="number" name="items[{{ $index + 1 }}][quantity]" class="input-sm" value="{{ $item->quantity }}" oninput="recalcRow(this)"></td>
                                <td data-label="سعر المتر"><input type="number" name="items[{{ $index + 1 }}][price_per_meter]" class="input-sm" step="0.01" value="{{ $item->price_per_meter }}" oninput="recalcRow(this)"></td>
                                <td data-label="التكلفة"><span class="cost-display">{{ number_format($item->total_cost, 2) }}</span></td>
                                <td data-label="إجراء"><button type="button" class="remove-btn" onclick="this.closest('tr').remove()"><i class="fas fa-trash"></i></button></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="action-buttons">
            <button type="submit" class="btn btn-green">
                <i class="fas fa-check"></i> حفظ التعديلات
            </button>
            <a href="{{ route('manufacturing-orders.raw-materials.index') }}" class="btn btn-red">
                <i class="fas fa-times"></i> إلغاء
            </a>
        </div>
    </form>
</div>

<script>
let itemIndex = {{ $template->items->count() }};

function addItem(data = null) {
    itemIndex++;
    const tbody = document.getElementById('items-body');
    const row = document.createElement('tr');
    row.innerHTML = `
        <td data-label="النوع">
            <select name="items[${itemIndex}][component_type]" class="form-control" style="padding:6px 8px;">
                <option value="فرش" ${data && data.component_type === 'فرش' ? 'selected' : ''}>فرش</option>
                <option value="روباط" ${data && data.component_type === 'روباط' ? 'selected' : ''}>روباط</option>
                <option value="شاسية" ${data && data.component_type === 'شاسية' ? 'selected' : ''}>شاسية</option>
                <option value="دكم" ${data && data.component_type === 'دكم' ? 'selected' : ''}>دكم</option>
            </select>
        </td>
        <td data-label="السمك (سم)"><input type="number" name="items[${itemIndex}][thickness_cm]" class="input-sm" step="0.1" placeholder="2.5" value="${data ? data.thickness_cm : ''}" oninput="recalcRow(this)"></td>
        <td data-label="العرض (سم)"><input type="number" name="items[${itemIndex}][width_cm]" class="input-sm" step="0.1" placeholder="12" value="${data ? data.width_cm : ''}" oninput="recalcRow(this)"></td>
        <td data-label="الطول (م)"><input type="number" name="items[${itemIndex}][length_cm]" class="input-sm" step="0.01" placeholder="4.00" value="${data ? data.length_cm : ''}" oninput="recalcRow(this)"></td>
        <td data-label="العدد"><input type="number" name="items[${itemIndex}][quantity]" class="input-sm" value="${data ? data.quantity : '1'}" oninput="recalcRow(this)"></td>
        <td data-label="سعر المتر"><input type="number" name="items[${itemIndex}][price_per_meter]" class="input-sm" step="0.01" placeholder="0" value="${data ? data.price_per_meter : ''}" oninput="recalcRow(this)"></td>
        <td data-label="التكلفة"><span class="cost-display">0.00</span></td>
        <td data-label="إجراء"><button type="button" class="remove-btn" onclick="this.closest('tr').remove()"><i class="fas fa-trash"></i></button></td>
    `;
    tbody.appendChild(row);
    if (data) recalcRow(row.querySelector('input'));
}

function recalcRow(el) {
    const row = el.closest('tr');
    const inputs = row.querySelectorAll('input');
    const length = parseFloat(inputs[2]?.value) || 0;
    const quantity = parseFloat(inputs[3]?.value) || 0;
    const price = parseFloat(inputs[4]?.value) || 0;
    const cost = length * quantity * price;
    const display = row.querySelector('.cost-display');
    if (display) display.textContent = cost.toFixed(2);
}
</script>
@endsection
```

- [ ] **Step 2: Commit**

```bash
git add resources/views/manufacturing-orders/raw-materials/edit.blade.php
git commit -m "add raw materials template edit view"
```

---

### Task 10: Show View

**Files:**
- Create: `resources/views/manufacturing-orders/raw-materials/show.blade.php`

- [ ] **Step 1: Create the show view**

Read-only display of template details and components table with the important note about per-pallet materials.

```blade
@extends('layouts.app')

@section('title', 'تفاصيل قالب الخامات')
@section('page-title', 'تفاصيل قالب الخامات')

@push('styles')
<style>
    :root {
        --tf-bg: #f4f7fe; --tf-surface: #ffffff; --tf-border: #e4eaf7;
        --tf-indigo: #4f63d2; --tf-blue: #3a8ef0; --tf-green: #0faa7e;
        --tf-red: #dc2626; --tf-amber: #e8930a;
        --tf-text-h: #1a2140; --tf-text-b: #3d4f72; --tf-text-m: #7e90b0;
    }

    .mfg-page { background: var(--tf-bg); min-height: 100vh; padding: 16px; }
    @media (min-width: 1024px) { .mfg-page { padding: 26px 22px; } }

    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .mfg-section { animation: fadeUp 0.55s cubic-bezier(0.22,1,0.36,1) both; }

    .mfg-header {
        display: flex; justify-content: space-between; align-items: center;
        margin-bottom: 20px; flex-wrap: wrap; gap: 12px;
    }
    @media (min-width: 768px) { .mfg-header { margin-bottom: 24px; gap: 16px; } }

    .mfg-title {
        font-size: 18px; font-weight: 900; color: var(--tf-text-h);
        display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
    }
    @media (min-width: 768px) { .mfg-title { font-size: 24px; gap: 12px; } }
    .mfg-title i { color: var(--tf-indigo); }

    .mfg-card {
        background: var(--tf-surface); border-radius: 16px;
        border: 1px solid var(--tf-border); overflow: hidden; margin-bottom: 16px;
    }
    @media (min-width: 768px) { .mfg-card { border-radius: 18px; margin-bottom: 20px; } }

    .mfg-card-header {
        padding: 12px 16px; border-bottom: 1px solid var(--tf-border);
        display: flex; align-items: center; gap: 10px;
    }
    @media (min-width: 768px) { .mfg-card-header { padding: 16px 22px; } }

    .mfg-card-title { font-size: 14px; font-weight: 800; margin: 0; }
    @media (min-width: 768px) { .mfg-card-title { font-size: 16px; } }

    .mfg-card-body { padding: 16px; }
    @media (min-width: 768px) { .mfg-card-body { padding: 22px; } }

    .btn {
        display: inline-flex; align-items: center; justify-content: center;
        gap: 8px; padding: 8px 16px; border-radius: 10px; font-weight: 700;
        font-size: 13px; border: none; cursor: pointer; transition: all .3s;
        text-decoration: none;
    }
    @media (min-width: 768px) { .btn { padding: 10px 20px; font-size: 14px; } }
    .btn-primary { background: var(--tf-indigo); color: #fff; }
    .btn-amber { background: var(--tf-amber); color: #fff; }
    .btn-sm { padding: 6px 12px; font-size: 12px; }

    .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }

    .mfg-table { width: 100%; border-collapse: collapse; font-size: 12px; }
    @media (min-width: 768px) { .mfg-table { font-size: 14px; } }

    .mfg-table th {
        background: var(--tf-bg); padding: 10px 8px; text-align: right;
        font-weight: 700; font-size: 11px; color: var(--tf-text-h); white-space: nowrap;
    }
    @media (min-width: 768px) { .mfg-table th { padding: 12px 10px; font-size: 12px; } }

    .mfg-table td { padding: 8px; border-top: 1px solid var(--tf-border); }
    @media (min-width: 768px) { .mfg-table td { padding: 10px; } }

    .info-box {
        background: linear-gradient(135deg, #e3f2fd, #f3e5f5);
        padding: 12px 16px; border-radius: 12px; margin-bottom: 16px;
        border-left: 4px solid var(--tf-indigo);
    }
    @media (min-width: 768px) {
        .info-box { padding: 18px 22px; margin-bottom: 24px; border-radius: 16px; }
    }
    .info-box .info-text { font-size: 13px; color: var(--tf-text-h); }
    @media (min-width: 768px) { .info-box .info-text { font-size: 14px; } }

    .summary-box {
        background: linear-gradient(135deg, var(--tf-indigo), #3b52c0);
        color: white; padding: 16px; border-radius: 12px;
        display: flex; flex-direction: column; gap: 10px;
    }
    @media (min-width: 768px) { .summary-box { padding: 20px; border-radius: 16px; gap: 12px; } }

    .summary-row {
        display: flex; justify-content: space-between;
        font-size: 13px; align-items: center;
    }
    @media (min-width: 768px) { .summary-row { font-size: 14px; } }
    .summary-row.total {
        font-size: 16px; font-weight: 900;
        border-top: 1px solid rgba(255,255,255,0.2);
        padding-top: 10px; margin-top: 6px;
    }
    @media (min-width: 768px) { .summary-row.total { font-size: 18px; padding-top: 12px; margin-top: 8px; } }
    .summary-label { opacity: 0.9; }
    .summary-value { font-weight: 800; }
    .summary-value.price { color: #ffdd57; font-size: 16px; }
    @media (min-width: 768px) { .summary-value.price { font-size: 18px; } }

    @media (max-width: 767px) {
        .mfg-table thead { display: none; }
        .mfg-table tbody tr {
            display: block; background: #f8faff; border-radius: 12px;
            padding: 12px; margin-bottom: 12px; border: 1px solid var(--tf-border);
        }
        .mfg-table tbody td {
            display: flex; align-items: center; gap: 8px;
            padding: 6px 0; border-top: none; text-align: right;
        }
        .mfg-table tbody td::before {
            content: attr(data-label); font-weight: 700; font-size: 11px;
            color: var(--tf-text-h); white-space: nowrap; flex-shrink: 0; min-width: 70px;
        }
    }
</style>
@endpush

@section('content')
<div class="mfg-page">
    <div class="mfg-section">
        <div class="mfg-header">
            <div class="mfg-title">
                <i class="fas fa-boxes-stacked"></i>
                {{ $template->name }}
            </div>
            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                <a href="{{ route('manufacturing-orders.raw-materials.edit', $template->id) }}" class="btn btn-amber">
                    <i class="fas fa-edit"></i> تعديل
                </a>
                <a href="{{ route('manufacturing-orders.raw-materials.index') }}" class="btn btn-primary">
                    <i class="fas fa-arrow-right"></i> العودة
                </a>
            </div>
        </div>

        @if($template->notes)
        <div class="mfg-card">
            <div class="mfg-card-body">
                <p style="margin:0; color:var(--tf-text-b);">{{ $template->notes }}</p>
            </div>
        </div>
        @endif

        <div class="mfg-card">
            <div class="mfg-card-header">
                <i class="fas fa-cubes" style="color:var(--tf-green);"></i>
                <h3 class="mfg-card-title">مكونات القالب</h3>
            </div>
            <div class="mfg-card-body">
                <div class="info-box">
                    <div class="info-text">
                        <strong>ملاحظة هامة:</strong> المكونات أدناه تمثل الخامات اللازمة لـ <strong>بالة واحدة</strong> فقط.
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="mfg-table">
                        <thead>
                            <tr>
                                <th>النوع</th>
                                <th>السمك (سم)</th>
                                <th>العرض (سم)</th>
                                <th>الطول (م)</th>
                                <th>العدد</th>
                                <th>سعر المتر</th>
                                <th>التكلفة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($template->items as $item)
                            <tr>
                                <td data-label="النوع">{{ $item->component_type }}</td>
                                <td data-label="السمك (سم)">{{ $item->thickness_cm }}</td>
                                <td data-label="العرض (سم)">{{ $item->width_cm }}</td>
                                <td data-label="الطول (م)">{{ $item->length_cm }}</td>
                                <td data-label="العدد">{{ $item->quantity }}</td>
                                <td data-label="سعر المتر">{{ number_format($item->price_per_meter, 2) }}</td>
                                <td data-label="التكلفة">{{ number_format($item->total_cost, 2) }} ج.م</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="mfg-card">
            <div class="mfg-card-body">
                <div class="summary-box">
                    <div class="summary-row">
                        <span class="summary-label">عدد المكونات:</span>
                        <span class="summary-value">{{ $template->items_count }}</span>
                    </div>
                    <div class="summary-row total">
                        <span class="summary-label">إجمالي تكلفة الخامات للبالة:</span>
                        <span class="summary-value price">{{ number_format($template->total_cost, 2) }} ج.م</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
```

Note: There is a typo in the show view `data-label="التكلفe"` — fix it to `data-label="التكلفة"` before committing.

- [ ] **Step 2: Fix the typo and commit**

```bash
# Fix the typo in show.blade.php: data-label="التكلفe" -> data-label="التكلفة"
git add resources/views/manufacturing-orders/raw-materials/show.blade.php
git commit -m "add raw materials template show view"
```

---

### Task 11: Load Template into Order — Modify Order Create Page

**Files:**
- Modify: `resources/views/manufacturing-orders/create.blade.php`

- [ ] **Step 1: Add the "Load from template" button and dropdown**

In the create view, find the wood components section. The info box with "ملاحظة هامة" is around line 536-540. Add a template loader button and dropdown **after** that info box div and **before** the "إضافة قطعة خشب" button.

Add this HTML after the info-box div (after line 540):

```html
<div x-data="{ showTemplates: false, templates: [], loading: false }" style="margin-bottom:16px;">
    <button type="button" class="btn btn-amber btn-sm" @click="
        if (templates.length === 0) {
            loading = true;
            fetch('{{ route('manufacturing-orders.raw-materials.list') }}', {
                method: 'POST',
                headers: {'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json'}
            })
            .then(r => r.json())
            .then(data => { templates = data.templates; loading = false; });
        }
        showTemplates = !showTemplates;
    " :disabled="loading">
        <i class="fas fa-download"></i> <span x-text="loading ? 'جاري التحميل...' : 'تحميل من قالب خامات'"></span>
    </button>
    <div x-show="showTemplates && templates.length > 0" x-transition style="margin-top:12px;">
        <div style="background:#fffbeb; border:1px solid var(--tf-amber); border-radius:12px; padding:12px; max-height:250px; overflow-y:auto;">
            <div style="font-size:12px; font-weight:700; color:var(--tf-text-h); margin-bottom:8px;">اختر قالب:</div>
            <template x-for="tpl in templates" :key="tpl.id">
                <button type="button" @click="
                    loading = true;
                    fetch('{{ route('manufacturing-orders.raw-materials.load', '') }}' + tpl.id, {
                        method: 'POST',
                        headers: {'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json'}
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('components-body').innerHTML = '';
                            componentIndex = 0;
                            data.template.items.forEach(item => {
                                componentIndex++;
                                const tbody = document.getElementById('components-body');
                                const row = document.createElement('tr');
                                row.innerHTML = `
                                    <td data-label="النوع">
                                        <select name="components[${componentIndex}][component_type]" class="form-control" style="padding:6px 8px;">
                                            <option value="فرش" ${item.component_type === 'فرش' ? 'selected' : ''}>فرش</option>
                                            <option value="روباط" ${item.component_type === 'روباط' ? 'selected' : ''}>روباط</option>
                                            <option value="شاسية" ${item.component_type === 'شاسية' ? 'selected' : ''}>شاسية</option>
                                            <option value="دكم" ${item.component_type === 'دكم' ? 'selected' : ''}>دكم</option>
                                        </select>
                                    </td>
                                    <td data-label="السمك (سم)"><input type="number" name="components[${componentIndex}][thickness_cm]" class="input-sm" step="0.1" value="${item.thickness_cm}"></td>
                                    <td data-label="العرض (سم)"><input type="number" name="components[${componentIndex}][width_cm]" class="input-sm" step="0.1" value="${item.width_cm}"></td>
                                    <td data-label="الطول (م)"><input type="number" name="components[${componentIndex}][length_cm]" class="input-sm" step="0.01" value="${item.length_cm}"></td>
                                    <td data-label="العدد"><input type="number" name="components[${componentIndex}][quantity]" class="input-sm" value="${item.quantity}"></td>
                                    <td data-label="سعر المتر"><input type="number" name="components[${componentIndex}][price_per_cubic_meter]" class="input-sm" step="0.01" value="${item.price_per_cubic_meter}" oninput="recalculateAll()"></td>
                                    <td data-label="التكلفة"><span class="cost-display">0.00</span></td>
                                    <td data-label="إجراء"><button type="button" class="remove-btn" onclick="removeComponent(${componentIndex}, this)"><i class="fas fa-trash"></i></button></td>
                                `;
                                tbody.appendChild(row);
                            });
                            recalculateAll();
                            showTemplates = false;
                        }
                        loading = false;
                    });
                " style="display:block; width:100%; text-align:right; padding:10px 12px; border:1px solid var(--tf-border); border-radius:8px; margin-bottom:6px; background:#fff; cursor:pointer; font-size:13px; font-weight:600; color:var(--tf-text-b); transition: all .2s;"
                   onmouseover="this.style.background='var(--tf-bg)'" onmouseout="this.style.background='#fff'">
                    <i class="fas fa-boxes-stacked" style="color:var(--tf-amber); margin-left:8px;"></i>
                    <span x-text="tpl.name"></span>
                </button>
            </template>
            <div x-show="templates.length === 0 && !loading" style="text-align:center; padding:20px; color:var(--tf-text-m);">
                لا توجد قوالب متاحة
            </div>
        </div>
    </div>
</div>
```

This uses Alpine.js (already loaded in the layout) to toggle a template selector dropdown. When a template is selected, it fetches the template items via AJAX and populates the existing components table, then calls `recalculateAll()` to update the cost summary.

- [ ] **Step 2: Commit**

```bash
git add resources/views/manufacturing-orders/create.blade.php
git commit -m "add load from template button to manufacturing order create page"
```

---

### Task 12: End-to-End Test

- [ ] **Step 1: Test the full flow manually**

1. Navigate to `/manufacturing-orders/raw-materials/create`
2. Fill in template name, add 2-3 component rows with real values
3. Save — verify redirect to index with success message
4. View the template — verify all data displays correctly
5. Edit the template — modify a row, save, verify changes
6. Navigate to `/manufacturing-orders/create`
7. Click "تحميل من قالب خامات" — verify dropdown appears with the template
8. Select the template — verify components table populates correctly
9. Verify cost summary recalculates after loading
10. Delete the template from the index page — verify confirmation dialog and successful deletion

- [ ] **Step 2: Final commit if any fixes needed**

```bash
git add -A
git commit -m "fix: raw materials template adjustments from testing"
```
