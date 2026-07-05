@extends('layouts.app')

@section('title', 'إدارة التصنيفات')
@section('page-title', 'تصنيفات المنتجات')

@push('styles')
<style>
    :root {
        --tf-bg:          #f4f7fe;
        --tf-surface:     #ffffff;
        --tf-surface2:    #f8faff;
        --tf-border:      #e4eaf7;
        --tf-border-soft: #edf0f9;
        --tf-indigo:      #0b1120;
        --tf-indigo-light:#1a2540;
        --tf-indigo-soft: #f1f5f9;
        --tf-blue:        #0b1120;
        --tf-blue-soft:   #f1f5f9;
        --tf-green:       #0b1120;
        --tf-green-soft:  #f1f5f9;
        --tf-red:         #0b1120;
        --tf-red-soft:    #f1f5f9;
        --tf-amber:       #0b1120;
        --tf-amber-soft:  #f1f5f9;
        --tf-violet:      #0b1120;
        --tf-violet-soft: #f1f5f9;

        --tf-text-h:      #1a2140;
        --tf-text-b:      #3d4f72;
        --tf-text-m:      #7e90b0;
        --tf-text-d:      #94a3b8;
        --tf-text-s:      #64748b;

        --tf-shadow-sm:   0 2px 12px rgba(79,99,210,0.07);
        --tf-shadow-card: 0 4px 20px rgba(79,99,210,0.06);
        --tf-shadow-lg:   0 8px 30px rgba(79,99,210,0.12);
        --radius-lg:      20px;
        --radius-md:      14px;
        --radius-sm:      8px;
    }

    .cat-page {
        background: var(--tf-bg);
        background-image:
            radial-gradient(ellipse 80% 60% at 10% -10%, rgba(79,99,210,0.1) 0%, transparent 50%),
            radial-gradient(ellipse 60% 50% at 90% 110%, rgba(58,142,240,0.08) 0%, transparent 50%);
        min-height: 100vh;
        padding: 24px;
    }

    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(16px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    @keyframes scaleIn {
        from { opacity: 0; transform: scale(0.95); }
        to   { opacity: 1; transform: scale(1); }
    }

    .animated-fade-up {
        animation: fadeUp 0.4s cubic-bezier(0.16, 1, 0.3, 1) both;
    }

    /* Stat Cards */
    .stat-card {
        background: var(--tf-surface);
        border-radius: var(--radius-md);
        border: 1px solid var(--tf-border);
        padding: 20px;
        position: relative;
        overflow: hidden;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        box-shadow: var(--tf-shadow-sm);
    }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--tf-shadow-lg);
    }
    .stat-card::after {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 4px;
    }
    .stat-card.indigo::after { background: var(--tf-indigo); }
    .stat-card.green::after  { background: var(--tf-green); }
    .stat-card.amber::after  { background: var(--tf-amber); }
    .stat-card.violet::after { background: var(--tf-violet); }

    .stat-icon {
        width: 46px; height: 46px;
        border-radius: var(--radius-sm);
        display: flex; align-items: center; justify-content: center;
        font-size: 18px;
    }
    .stat-card.indigo .stat-icon { background: var(--tf-indigo-soft); color: var(--tf-indigo); }
    .stat-card.green  .stat-icon { background: var(--tf-green-soft); color: var(--tf-green); }
    .stat-card.amber  .stat-icon { background: var(--tf-amber-soft); color: var(--tf-amber); }
    .stat-card.violet .stat-icon { background: var(--tf-violet-soft); color: var(--tf-violet); }

    /* Category Grid Cards */
    .category-grid-card {
        background: var(--tf-surface);
        border-radius: var(--radius-md);
        border: 1px solid var(--tf-border);
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        position: relative;
        overflow: hidden;
    }
    .category-grid-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--tf-shadow-lg);
        border-color: rgba(79,99,210,0.25);
    }
    .category-badge-icon {
        width: 50px; height: 50px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 20px;
        transition: transform 0.3s ease;
    }
    .category-grid-card:hover .category-badge-icon {
        transform: scale(1.08) rotate(4deg);
    }

    /* Modal Backdrop Blur */
    .modal-backdrop {
        backdrop-filter: blur(8px);
        background-color: rgba(15, 23, 42, 0.45);
        transition: opacity 0.3s ease;
    }

    .modal-content {
        animation: scaleIn 0.3s cubic-bezier(0.34, 1.56, 0.64, 1) both;
    }

    /* Custom Pickers */
    .color-dot {
        width: 32px; height: 32px;
        border-radius: 50%;
        cursor: pointer;
        transition: all 0.2s;
        border: 3px solid transparent;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .color-dot:hover {
        transform: scale(1.15);
    }
    .color-dot.active {
        border-color: #0f172a;
        box-shadow: 0 0 0 2px rgba(255,255,255,0.8);
    }

    .icon-picker-btn {
        width: 44px; height: 44px;
        border-radius: 10px;
        background: var(--tf-surface2);
        border: 1.5px solid var(--tf-border);
        color: var(--tf-text-b);
        display: flex; align-items: center; justify-content: center;
        font-size: 16px; cursor: pointer; transition: all 0.2s;
    }
    .icon-picker-btn:hover {
        background: var(--tf-indigo-soft);
        color: var(--tf-indigo);
        border-color: var(--tf-indigo-light);
        transform: scale(1.05);
    }
    .icon-picker-btn.active {
        background: var(--tf-indigo);
        color: white;
        border-color: var(--tf-indigo);
        box-shadow: 0 0 10px rgba(79, 99, 210, 0.4);
    }

    /* Switch button styling */
    .switch-btn {
        width: 38px; height: 38px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        cursor: pointer; transition: all 0.2s;
        background: var(--tf-surface);
        border: 1px solid var(--tf-border);
        color: var(--tf-text-s);
    }
    .switch-btn.active {
        background: var(--tf-indigo-soft);
        color: var(--tf-indigo);
        border-color: var(--tf-indigo-light);
    }

    /* Table styles */
    .cat-table th {
        background: var(--tf-surface2);
        color: var(--tf-text-d);
        font-weight: 700;
        font-size: 12px;
        letter-spacing: 0.5px;
        padding: 14px 20px;
        border-bottom: 1px solid var(--tf-border-soft);
    }
    .cat-table td {
        padding: 16px 20px;
        border-bottom: 1px solid var(--tf-border-soft);
        vertical-align: middle;
    }

    /* Custom Toast styles */
    .toast-container {
        position: fixed;
        bottom: 24px;
        left: 24px;
        z-index: 9999;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .toast-item {
        display: flex; align-items: center; gap: 12px;
        padding: 14px 20px; border-radius: 12px;
        min-width: 300px;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        border: 1px solid transparent;
        animation: fadeUp 0.3s ease both;
    }
    .toast-success { background: #f1f5f9; border-color: #a7f3d0; color: #065f46; }
    .toast-error { background: #f1f5f9; border-color: #fca5a5; color: #991b1b; }

    /* Custom Checkbox Toggle */
    .status-toggle {
        position: relative; width: 44px; height: 24px;
        background: #cbd5e1; border-radius: 50px;
        cursor: pointer; transition: background 0.25s;
    }
    .status-toggle::before {
        content: ''; position: absolute; top: 2px; right: 2px;
        width: 20px; height: 20px; border-radius: 50%;
        background: white; transition: transform 0.25s;
        box-shadow: 0 1px 3px rgba(0,0,0,0.15);
    }
    .status-toggle.active { background: var(--tf-green); }
    .status-toggle.active::before { transform: translateX(-20px); }

</style>
@endpush

@section('content')
<div class="cat-page" x-data="categoriesManager()" x-init="init()">
    
    <!-- ══ TOAST NOTIFICATIONS ══ -->
    <div class="toast-container">
        <template x-for="toast in toasts" :key="toast.id">
            <div class="toast-item" :class="toast.type === 'success' ? 'toast-success' : 'toast-error'">
                <div class="w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0" :class="toast.type === 'success' ? 'bg-emerald-100 text-emerald-600' : 'bg-red-100 text-red-600'">
                    <i class="fas" :class="toast.type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'"></i>
                </div>
                <div class="text-sm font-bold flex-1" x-text="toast.message"></div>
                <button @click="removeToast(toast.id)" class="text-current opacity-60 hover:opacity-100"><i class="fas fa-times"></i></button>
            </div>
        </template>
    </div>

    <!-- ══ Header Section ══ -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6 animated-fade-up">
        <div>
            <h1 class="text-2xl font-black text-slate-800 flex items-center gap-3">
                <span class="w-10 h-10 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center"><i class="fas fa-tags"></i></span>
                تصنيفات المنتجات
            </h1>
            <p class="text-xs text-slate-400 font-semibold mt-1">قم بإنشاء وتعديل تصنيفات المنتجات لتسهيل عملية البيع والجرد ونقاط البيع (POS)</p>
        </div>
        <button @click="openCreateModal()" class="btn-primary" style="padding: 11px 22px; border-radius: 12px;">
            <i class="fas fa-plus"></i> إضافة تصنيف جديد
        </button>
    </div>

    <!-- ══ Statistics Bar ══ -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6 animated-fade-up" style="animation-delay: 0.05s;">
        <!-- Total Categories -->
        <div class="stat-card indigo">
            <div class="flex items-center gap-3">
                <div class="stat-icon"><i class="fas fa-tag"></i></div>
                <div>
                    <div class="stat-val font-black" x-text="categories.length">0</div>
                    <div class="stat-lbl font-bold">إجمالي التصنيفات</div>
                </div>
            </div>
        </div>
        
        <!-- Active Categories -->
        <div class="stat-card green">
            <div class="flex items-center gap-3">
                <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                <div>
                    <div class="stat-val font-black" x-text="categories.filter(c => c.is_active).length">0</div>
                    <div class="stat-lbl font-bold">نشط بالبرنامج</div>
                </div>
            </div>
        </div>

        <!-- Inactive Categories -->
        <div class="stat-card amber">
            <div class="flex items-center gap-3">
                <div class="stat-icon"><i class="fas fa-eye-slash"></i></div>
                <div>
                    <div class="stat-val font-black" x-text="categories.filter(c => !c.is_active).length">0</div>
                    <div class="stat-lbl font-bold">غير نشط</div>
                </div>
            </div>
        </div>

        <!-- Total Products Mapped -->
        <div class="stat-card violet">
            <div class="flex items-center gap-3">
                <div class="stat-icon"><i class="fas fa-boxes"></i></div>
                <div>
                    <div class="stat-val font-black" x-text="totalProducts">0</div>
                    <div class="stat-lbl font-bold">منتجات مصنّفة</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ══ Filtering & Layout Switches ══ -->
    <div class="flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-4 mb-6 bg-white p-4 rounded-2xl border border-slate-100 shadow-sm animated-fade-up" style="animation-delay: 0.1s;">
        
        <!-- Search and Filter -->
        <div class="flex-1 flex gap-3">
            <div class="relative flex-1">
                <span class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400"><i class="fas fa-search"></i></span>
                <input type="text" x-model="searchQuery" placeholder="بحث باسم التصنيف أو الوصف..." class="w-full pr-10 pl-4 py-2 border border-slate-200 rounded-xl text-sm focus:border-indigo-500 focus:outline-none transition">
            </div>
            
            <select x-model="statusFilter" class="px-4 py-2 border border-slate-200 rounded-xl text-sm focus:border-indigo-500 focus:outline-none transition bg-white cursor-pointer">
                <option value="all">كل الحالات</option>
                <option value="active">نشط فقط</option>
                <option value="inactive">غير نشط</option>
            </select>
        </div>

        <!-- Switch Views -->
        <div class="flex items-center gap-2">
            <span class="text-xs text-slate-400 font-bold hidden sm:inline">طريقة العرض:</span>
            <button @click="viewMode = 'grid'" class="switch-btn" :class="viewMode === 'grid' ? 'active' : ''" title="عرض شبكي">
                <i class="fas fa-th-large"></i>
            </button>
            <button @click="viewMode = 'table'" class="switch-btn" :class="viewMode === 'table' ? 'active' : ''" title="عرض جدول">
                <i class="fas fa-list"></i>
            </button>
        </div>

    </div>

    <!-- ══ Empty State ══ -->
    <template x-if="filteredCategories.length === 0">
        <div class="bg-white rounded-2xl border border-slate-200/80 p-16 text-center animated-fade-up shadow-sm">
            <div class="w-16 h-16 bg-slate-50 text-slate-300 rounded-2xl flex items-center justify-center mx-auto mb-4 text-2xl">
                <i class="fas fa-folder-open"></i>
            </div>
            <h3 class="text-lg font-black text-slate-700 mb-1">لا توجد تصنيفات مطابقة</h3>
            <p class="text-xs font-semibold text-slate-400 mb-5">يرجى تعديل خيارات البحث أو إضافة تصنيف جديد للبدء في تنظيم منتجاتك</p>
            <button @click="openCreateModal()" class="btn-primary py-2 px-6 rounded-xl text-sm">
                <i class="fas fa-plus"></i> إضافة تصنيف جديد
            </button>
        </div>
    </template>

    <!-- ══ Grid View ══ -->
    <template x-if="viewMode === 'grid' && filteredCategories.length > 0">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 animated-fade-up">
            <template x-for="cat in filteredCategories" :key="cat.id">
                <div class="category-grid-card p-5">
                    
                    <!-- Color ribbon -->
                    <div class="absolute top-0 right-0 left-0 h-1.5" :style="{ backgroundColor: cat.color }"></div>

                    <!-- Card Header -->
                    <div class="flex justify-between items-start mb-4">
                        <div class="category-badge-icon" :style="{ backgroundColor: cat.color + '15', color: cat.color }">
                            <i class="fas" :class="cat.icon || 'fa-tag'"></i>
                        </div>
                        <div class="flex items-center gap-1">
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full" :class="cat.is_active ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-400'" x-text="cat.is_active ? 'نشط' : 'غير نشط'"></span>
                            
                            <!-- Dropdown or quick options -->
                            <div class="relative" x-data="{ open: false }" @click.away="open = false">
                                <button @click="open = !open" class="w-7 h-7 flex items-center justify-center rounded-lg hover:bg-slate-50 text-slate-400 hover:text-slate-600">
                                    <i class="fas fa-ellipsis-v text-xs"></i>
                                </button>
                                <div x-show="open" class="absolute left-0 mt-1 w-32 bg-white border border-slate-100 rounded-lg shadow-lg py-1 z-10 text-right">
                                    <button @click="openEditModal(cat); open = false" class="w-full text-right px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-50 font-bold flex items-center gap-2"><i class="fas fa-pen text-amber-500 w-4 text-center"></i> تعديل</button>
                                    <button @click="toggleStatus(cat); open = false" class="w-full text-right px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-50 font-bold flex items-center gap-2">
                                        <i class="fas w-4 text-center" :class="cat.is_active ? 'fa-eye-slash text-slate-500' : 'fa-eye text-emerald-500'"></i>
                                        <span x-text="cat.is_active ? 'تعطيل نشاط' : 'تفعيل نشاط'"></span>
                                    </button>
                                    <div class="border-t border-slate-100 my-1"></div>
                                    <button @click="deleteCategory(cat); open = false" class="w-full text-right px-3 py-1.5 text-xs text-red-600 hover:bg-red-50 font-bold flex items-center gap-2"><i class="fas fa-trash-alt w-4 text-center"></i> حذف</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Category Info -->
                    <div class="mb-4">
                        <h4 class="font-black text-slate-800 text-base mb-1" x-text="cat.name"></h4>
                        <p class="text-xs text-slate-400 font-semibold line-clamp-2 h-8" x-text="cat.description || 'لا يوجد وصف للمجموعة'"></p>
                    </div>

                    <div class="border-t border-slate-100 pt-3 flex justify-between items-center">
                        <div class="flex items-center gap-1.5 text-xs text-slate-500 font-bold">
                            <i class="fas fa-boxes text-slate-400"></i>
                            <span x-text="cat.products_count + ' منتج'"></span>
                        </div>
                        <div class="text-[10px] text-slate-300 font-bold">ترتيب: <span x-text="cat.sort_order"></span></div>
                    </div>

                </div>
            </template>
        </div>
    </template>

    <!-- ══ Table View ══ -->
    <template x-if="viewMode === 'table' && filteredCategories.length > 0">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden animated-fade-up">
            <div class="overflow-x-auto">
                <table class="w-full text-right cat-table">
                    <thead>
                        <tr>
                            <th style="width: 80px;">اللون</th>
                            <th>الاسم والمجموعة</th>
                            <th>الوصف</th>
                            <th style="width: 120px;" class="text-center">المنتجات المرتبطة</th>
                            <th style="width: 100px;" class="text-center">الترتيب</th>
                            <th style="width: 120px;" class="text-center">الحالة</th>
                            <th style="width: 120px;" class="text-center">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="cat in filteredCategories" :key="cat.id">
                            <tr class="hover:bg-slate-50/50 transition">
                                <!-- Color dot -->
                                <td>
                                    <div class="w-8 h-8 rounded-full border border-white flex items-center justify-center text-white" :style="{ backgroundColor: cat.color }">
                                        <i class="fas text-xs" :class="cat.icon"></i>
                                    </div>
                                </td>
                                
                                <!-- Name -->
                                <td>
                                    <span class="font-black text-slate-800" x-text="cat.name"></span>
                                </td>

                                <!-- Description -->
                                <td>
                                    <span class="text-xs text-slate-400 font-semibold" x-text="cat.description || '—'"></span>
                                </td>

                                <!-- Products count -->
                                <td class="text-center">
                                    <span class="bg-indigo-50 text-indigo-600 px-3 py-1 rounded-full text-xs font-black" x-text="cat.products_count"></span>
                                </td>

                                <!-- Sort Order -->
                                <td class="text-center font-bold text-xs text-slate-500" x-text="cat.sort_order"></td>

                                <!-- Status -->
                                <td class="text-center">
                                    <button @click="toggleStatus(cat)" class="status-toggle inline-block" :class="cat.is_active ? 'active' : ''"></button>
                                </td>

                                <!-- Actions -->
                                <td>
                                    <div class="flex items-center justify-center gap-2">
                                        <button @click="openEditModal(cat)" class="w-8 h-8 bg-amber-50 text-amber-600 hover:bg-amber-500 hover:text-white rounded-lg flex items-center justify-center transition" title="تعديل"><i class="fas fa-pen text-xs"></i></button>
                                        <button @click="deleteCategory(cat)" class="w-8 h-8 bg-red-50 text-red-600 hover:bg-red-500 hover:text-white rounded-lg flex items-center justify-center transition" title="حذف"><i class="fas fa-trash-alt text-xs"></i></button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </template>


    <!-- ══ ADD / EDIT MODAL ══ -->
    <div x-show="modalOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <!-- Backdrop -->
        <div class="fixed inset-0 modal-backdrop" @click="closeModal()"></div>
        
        <div class="flex min-h-screen items-center justify-center p-4">
            <!-- Modal Box -->
            <div class="relative bg-white rounded-2xl w-full max-w-lg shadow-2xl modal-content overflow-hidden border border-slate-100" @click.stop>
                
                <!-- Header -->
                <div class="px-6 py-4 bg-slate-50 border-b border-slate-100 flex justify-between items-center">
                    <h3 class="text-lg font-black text-slate-800 flex items-center gap-2">
                        <span class="w-8 h-8 bg-indigo-50 text-indigo-600 rounded-lg flex items-center justify-center text-sm"><i class="fas" :class="isEditMode ? 'fa-pen' : 'fa-plus'"></i></span>
                        <span x-text="isEditMode ? 'تعديل بيانات التصنيف' : 'إضافة تصنيف جديد'"></span>
                    </h3>
                    <button @click="closeModal()" class="text-slate-400 hover:text-slate-600"><i class="fas fa-times"></i></button>
                </div>

                <!-- Form -->
                <form @submit.prevent="submitForm">
                    <div class="p-6 space-y-4">
                        
                        <!-- Name input -->
                        <div>
                            <label class="block text-xs font-black text-slate-600 mb-1.5">اسم التصنيف <span class="text-red-500">*</span></label>
                            <input type="text" x-model="formData.name" placeholder="مثال: مشروبات، إلكترونيات، خضار" class="w-full px-4 py-2 border.5 border-slate-200 rounded-xl text-sm focus:border-indigo-500 focus:outline-none transition font-bold" required>
                        </div>

                        <!-- Description input -->
                        <div>
                            <label class="block text-xs font-black text-slate-600 mb-1.5">الوصف <span class="text-slate-400 font-semibold">(اختياري)</span></label>
                            <textarea x-model="formData.description" placeholder="اكتب وصفاً موجزاً للمجموعة..." rows="2" class="w-full px-4 py-2 border.5 border-slate-200 rounded-xl text-sm focus:border-indigo-500 focus:outline-none transition"></textarea>
                        </div>

                        <!-- Grid: Sort Order and Active -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-black text-slate-600 mb-1.5">ترتيب العرض <span class="text-slate-400 font-semibold">(رقمي)</span></label>
                                <input type="number" x-model="formData.sort_order" min="0" class="w-full px-4 py-2 border.5 border-slate-200 rounded-xl text-sm focus:border-indigo-500 focus:outline-none transition text-center font-bold">
                            </div>
                            <div class="flex items-end pb-2">
                                <label class="flex items-center gap-2 cursor-pointer select-none">
                                    <input type="checkbox" x-model="formData.is_active" class="w-4 h-4 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500">
                                    <span class="text-xs font-black text-slate-700">تفعيل التصنيف بالبرنامج</span>
                                </label>
                            </div>
                        </div>

                        <!-- Color Picker -->
                        <div>
                            <label class="block text-xs font-black text-slate-600 mb-2">اختر اللون المميز بالبرنامج</label>
                            <div class="flex flex-wrap gap-2.5">
                                @foreach($colors as $color)
                                    <button type="button" @click="formData.color = '{{ $color }}'" class="color-dot" :class="formData.color === '{{ $color }}' ? 'active' : ''" style="background-color: {{ $color }}"></button>
                                @endforeach
                            </div>
                        </div>

                        <!-- Icon Picker -->
                        <div>
                            <label class="block text-xs font-black text-slate-600 mb-2">اختر أيقونة التصنيف لكاشير المبيعات</label>
                            <div class="grid grid-cols-6 gap-2 max-h-36 overflow-y-auto p-1 bg-slate-50 rounded-xl border border-slate-100">
                                @foreach($icons as $iconClass => $iconName)
                                    <button type="button" @click="formData.icon = '{{ $iconClass }}'" class="icon-picker-btn" :class="formData.icon === '{{ $iconClass }}' ? 'active' : ''" title="{{ $iconName }}">
                                        <i class="fas {{ $iconClass }}"></i>
                                    </button>
                                @endforeach
                            </div>
                        </div>

                    </div>

                    <!-- Footer -->
                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-between items-center">
                        <button type="button" @click="closeModal()" class="px-4 py-2 border border-slate-200 rounded-xl text-xs font-bold text-slate-500 hover:bg-slate-100 transition">إلغاء</button>
                        <button type="submit" class="btn-primary py-2 px-6 rounded-xl text-xs flex items-center gap-2" :disabled="submitting">
                            <template x-if="submitting">
                                <span class="spinner w-4 h-4"></span>
                            </template>
                            <span x-text="submitting ? 'جاري الحفظ...' : 'حفظ البيانات'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    function categoriesManager() {
        return {
            // State variables
            categories: @json($categories),
            totalProducts: {{ $totalProducts }},
            searchQuery: '',
            statusFilter: 'all',
            viewMode: 'grid',
            toasts: [],
            submitting: false,
            
            // Modal state
            modalOpen: false,
            isEditMode: false,
            currentId: null,
            formData: {
                name: '',
                description: '',
                color: '#6366f1',
                icon: 'fa-tag',
                sort_order: 0,
                is_active: true
            },

            init() {
                // Initial setup or loading
            },

            // Filter computation
            get filteredCategories() {
                return this.categories.filter(c => {
                    const matchesSearch = c.name.toLowerCase().includes(this.searchQuery.toLowerCase()) || 
                                          (c.description && c.description.toLowerCase().includes(this.searchQuery.toLowerCase()));
                    
                    let matchesStatus = true;
                    if (this.statusFilter === 'active') matchesStatus = c.is_active;
                    else if (this.statusFilter === 'inactive') matchesStatus = !c.is_active;

                    return matchesSearch && matchesStatus;
                });
            },

            // Notification helpers
            showToast(message, type = 'success') {
                const id = Date.now();
                this.toasts.push({ id, message, type });
                setTimeout(() => this.removeToast(id), 5000);
            },
            
            removeToast(id) {
                this.toasts = this.toasts.filter(t => t.id !== id);
            },

            // Modal actions
            resetForm() {
                this.formData = {
                    name: '',
                    description: '',
                    color: '#6366f1',
                    icon: 'fa-tag',
                    sort_order: 0,
                    is_active: true
                };
                this.isEditMode = false;
                this.currentId = null;
            },

            openCreateModal() {
                this.resetForm();
                this.modalOpen = true;
            },

            openEditModal(cat) {
                this.isEditMode = true;
                this.currentId = cat.id;
                this.formData = {
                    name: cat.name,
                    description: cat.description || '',
                    color: cat.color || '#6366f1',
                    icon: cat.icon || 'fa-tag',
                    sort_order: cat.sort_order || 0,
                    is_active: cat.is_active ? true : false
                };
                this.modalOpen = true;
            },

            closeModal() {
                this.modalOpen = false;
                this.resetForm();
            },

            // Save / Create AJAX
            async submitForm() {
                this.submitting = true;
                const url = this.isEditMode ? `/categories/${this.currentId}` : '/categories';
                const method = this.isEditMode ? 'PUT' : 'POST';

                try {
                    const response = await fetch(url, {
                        method: method,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(this.formData)
                    });

                    const data = await response.json();
                    
                    if (data.success) {
                        this.showToast(data.message, 'success');
                        
                        if (this.isEditMode) {
                            // Update locally
                            this.categories = this.categories.map(c => c.id === this.currentId ? data.category : c);
                        } else {
                            // Add locally
                            this.categories.push(data.category);
                        }

                        // Recalculate total products
                        this.totalProducts = this.categories.reduce((acc, c) => acc + parseInt(c.products_count || 0), 0);
                        
                        this.closeModal();
                    } else {
                        this.showToast(data.message || '❌ حدث خطأ غير متوقع', 'error');
                    }
                } catch (error) {
                    this.showToast('❌ حدث خطأ في الاتصال بالخادم', 'error');
                } finally {
                    this.submitting = false;
                }
            },

            // Toggle active status AJAX
            async toggleStatus(cat) {
                try {
                    const response = await fetch(`/categories/${cat.id}/toggle-status`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    });
                    
                    const data = await response.json();
                    if (data.success) {
                        cat.is_active = data.is_active;
                        this.showToast(data.message, 'success');
                    } else {
                        this.showToast(data.message || '❌ خطأ أثناء تبديل الحالة', 'error');
                    }
                } catch (error) {
                    this.showToast('❌ حدث خطأ في الاتصال بالخادم', 'error');
                }
            },

            // Delete Category AJAX
            async deleteCategory(cat) {
                if (cat.products_count > 0) {
                    this.showToast(`❌ لا يمكن حذف التصنيف "${cat.name}" لأنه يحتوي على ${cat.products_count} منتج. قم بإزالة المنتجات أولاً.`, 'error');
                    return;
                }

                if (!confirm(`هل أنت متأكد من رغبتك في حذف التصنيف "${cat.name}" نهائياً؟`)) {
                    return;
                }

                try {
                    const response = await fetch(`/categories/${cat.id}`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    });
                    
                    const data = await response.json();
                    if (data.success) {
                        this.showToast(data.message, 'success');
                        this.categories = this.categories.filter(c => c.id !== cat.id);
                        this.totalProducts = this.categories.reduce((acc, c) => acc + parseInt(c.products_count || 0), 0);
                    } else {
                        this.showToast(data.message || '❌ فشل في حذف التصنيف', 'error');
                    }
                } catch (error) {
                    this.showToast('❌ حدث خطأ في الاتصال بالخادم', 'error');
                }
            }
        };
    }
</script>
@endpush
