@extends('layouts.app')

@section('title', 'إدارة المخازن')
@section('page-title', 'إدارة المخازن')

@push('styles')
<style>
    /* ── Page Header ── */
    .page-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 28px;
        flex-wrap: wrap;
    }
    .page-header-left h2 {
        font-size: 22px;
        font-weight: 800;
        color: var(--text-main);
        margin: 0 0 4px;
    }
    .page-header-left p {
        font-size: 13px;
        color: var(--text-muted);
        margin: 0;
    }

    /* ── Btn ── */
    .btn-primary {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 11px 22px;
        background: linear-gradient(135deg, #6366f1, #3b82f6);
        color: #fff;
        border-radius: 12px;
        font-size: 13.5px;
        font-weight: 700;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: all 0.25s;
        box-shadow: 0 4px 18px rgba(99,102,241,0.35);
        white-space: nowrap;
    }
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 28px rgba(99,102,241,0.45);
        color: #fff;
    }
    .btn-primary .btn-icon {
        width: 22px; height: 22px;
        background: rgba(255,255,255,0.2);
        border-radius: 6px;
        display: flex; align-items: center; justify-content: center;
        font-size: 12px;
    }

    /* ── Stats Bar ── */
    .stats-bar {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 28px;
    }
    @media (max-width: 900px) { .stats-bar { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 500px) { .stats-bar { grid-template-columns: 1fr; } }

    .stat-card {
        background: #fff;
        border: 1px solid rgba(99,102,241,0.1);
        border-radius: 16px;
        padding: 18px 20px;
        display: flex;
        align-items: center;
        gap: 14px;
        position: relative;
        overflow: hidden;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .stat-card:hover { transform: translateY(-2px); box-shadow: 0 8px 30px rgba(99,102,241,0.1); }
    .stat-card::after {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
    }
    .stat-card.blue::after   { background: linear-gradient(90deg, #6366f1, #3b82f6); }
    .stat-card.green::after  { background: linear-gradient(90deg, #10b981, #059669); }
    .stat-card.amber::after  { background: linear-gradient(90deg, #f59e0b, #d97706); }
    .stat-card.rose::after   { background: linear-gradient(90deg, #f43f5e, #e11d48); }

    .stat-icon {
        width: 46px; height: 46px;
        border-radius: 13px;
        display: flex; align-items: center; justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }
    .stat-card.blue  .stat-icon { background: rgba(99,102,241,0.1);  color: #6366f1; }
    .stat-card.green .stat-icon { background: rgba(16,185,129,0.1);  color: #10b981; }
    .stat-card.amber .stat-icon { background: rgba(245,158,11,0.1);  color: #f59e0b; }
    .stat-card.rose  .stat-icon { background: rgba(244,63,94,0.1);   color: #f43f5e; }

    .stat-info { flex: 1; }
    .stat-info .val {
        font-size: 22px;
        font-weight: 800;
        color: var(--text-main);
        line-height: 1.1;
    }
    .stat-info .lbl {
        font-size: 11.5px;
        color: var(--text-muted);
        font-weight: 500;
        margin-top: 2px;
    }

    /* ── Grid ── */
    .warehouses-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
    }
    @media (max-width: 1100px) { .warehouses-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 640px)  { .warehouses-grid { grid-template-columns: 1fr; } }

    /* ── Warehouse Card ── */
    .wh-card {
        background: #fff;
        border: 1px solid rgba(99,102,241,0.1);
        border-radius: 18px;
        overflow: hidden;
        transition: transform 0.25s, box-shadow 0.25s;
        animation: fadeUp 0.4s ease both;
    }
    .wh-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 16px 48px rgba(99,102,241,0.13);
    }
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(16px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    /* Card header strip */
    .wh-card-header {
        padding: 20px;
        background: linear-gradient(135deg, #1e2d4a 0%, #0f1f3d 100%);
        position: relative;
        overflow: hidden;
    }
    .wh-card-header::before {
        content: '';
        position: absolute;
        top: -40px; left: -40px;
        width: 140px; height: 140px;
        background: rgba(99,102,241,0.12);
        border-radius: 50%;
    }
    .wh-card-header::after {
        content: '';
        position: absolute;
        bottom: -30px; right: 20px;
        width: 90px; height: 90px;
        background: rgba(59,130,246,0.1);
        border-radius: 50%;
    }
    .wh-header-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 10px;
        position: relative;
        z-index: 1;
    }
    .wh-icon-wrap {
        width: 48px; height: 48px;
        background: linear-gradient(135deg, rgba(99,102,241,0.4), rgba(59,130,246,0.3));
        border: 1px solid rgba(255,255,255,0.15);
        border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        font-size: 20px;
        color: #c7d2fe;
        flex-shrink: 0;
    }
    .wh-name { color: #fff; font-size: 15px; font-weight: 700; margin: 0 0 2px; }
    .wh-code {
        display: inline-block;
        background: rgba(255,255,255,0.1);
        color: rgba(255,255,255,0.6);
        font-size: 11px;
        padding: 2px 8px;
        border-radius: 20px;
        font-weight: 600;
        letter-spacing: 0.5px;
    }
    .status-badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        flex-shrink: 0;
    }
    .status-active   { background: rgba(16,185,129,0.2); color: #6ee7b7; border: 1px solid rgba(16,185,129,0.3); }
    .status-inactive { background: rgba(239,68,68,0.2);  color: #fca5a5; border: 1px solid rgba(239,68,68,0.3); }

    /* Card body */
    .wh-card-body { padding: 18px 20px; }

    .wh-location {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        color: var(--text-muted);
        font-size: 12.5px;
        margin-bottom: 16px;
        padding-bottom: 16px;
        border-bottom: 1px solid rgba(99,102,241,0.07);
    }
    .wh-location i { color: #6366f1; margin-top: 1px; font-size: 13px; }

    /* Mini stats */
    .wh-stats {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        margin-bottom: 16px;
    }
    .wh-stat {
        border-radius: 12px;
        padding: 12px 14px;
        position: relative;
        overflow: hidden;
    }
    .wh-stat.products { background: rgba(99,102,241,0.06); border: 1px solid rgba(99,102,241,0.1); }
    .wh-stat.value    { background: rgba(16,185,129,0.06); border: 1px solid rgba(16,185,129,0.1); }
    .wh-stat .s-label {
        font-size: 10.5px;
        font-weight: 700;
        margin: 0 0 4px;
        display: flex; align-items: center; gap: 5px;
    }
    .wh-stat.products .s-label { color: #6366f1; }
    .wh-stat.value    .s-label { color: #10b981; }
    .wh-stat .s-val {
        font-size: 20px;
        font-weight: 800;
        margin: 0;
        line-height: 1;
    }
    .wh-stat.products .s-val { color: #4f46e5; }
    .wh-stat.value    .s-val { color: #059669; }

    /* Manager row */
    .wh-manager {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 12px;
        background: rgba(99,102,241,0.04);
        border: 1px solid rgba(99,102,241,0.08);
        border-radius: 10px;
        margin-bottom: 16px;
    }
    .manager-avatar {
        width: 30px; height: 30px;
        border-radius: 8px;
        background: linear-gradient(135deg, #6366f1, #3b82f6);
        display: flex; align-items: center; justify-content: center;
        font-size: 12px; color: #fff;
        font-weight: 700;
        flex-shrink: 0;
    }
    .wh-manager span { font-size: 12.5px; font-weight: 600; color: var(--text-main); }
    .wh-manager small { font-size: 11px; color: var(--text-muted); display: block; }

    /* Actions */
    .wh-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        padding-top: 14px;
        border-top: 1px solid rgba(99,102,241,0.07);
    }
    .act-btn {
        display: flex; align-items: center; justify-content: center;
        gap: 6px;
        padding: 9px 14px;
        border-radius: 10px;
        font-size: 12.5px;
        font-weight: 600;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
    }
    .act-btn.primary {
        flex: 1;
        background: linear-gradient(135deg, #6366f1, #3b82f6);
        color: #fff;
        box-shadow: 0 3px 12px rgba(99,102,241,0.3);
    }
    .act-btn.primary:hover {
        box-shadow: 0 5px 18px rgba(99,102,241,0.4);
        transform: translateY(-1px);
        color: #fff;
    }
    .act-btn.edit {
        background: rgba(99,102,241,0.08);
        color: #6366f1;
        border: 1px solid rgba(99,102,241,0.15);
        padding: 9px 12px;
    }
    .act-btn.edit:hover { background: rgba(99,102,241,0.15); }
    .act-btn.del {
        background: rgba(239,68,68,0.07);
        color: #ef4444;
        border: 1px solid rgba(239,68,68,0.15);
        padding: 9px 12px;
    }
    .act-btn.del:hover { background: rgba(239,68,68,0.14); }

    /* ── Empty State ── */
    .empty-state {
        grid-column: 1 / -1;
        background: #fff;
        border: 1px dashed rgba(99,102,241,0.2);
        border-radius: 20px;
        padding: 64px 20px;
        text-align: center;
    }
    .empty-icon {
        width: 80px; height: 80px;
        background: rgba(99,102,241,0.08);
        border-radius: 24px;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 20px;
        font-size: 32px;
        color: #6366f1;
    }
    .empty-state h3 { font-size: 18px; font-weight: 800; color: var(--text-main); margin: 0 0 8px; }
    .empty-state p  { font-size: 13px; color: var(--text-muted); margin: 0 0 24px; }

    /* ── Pagination ── */
    .pagination-wrap {
        display: flex;
        justify-content: center;
        margin-top: 28px;
    }

    /* Animation delays for cards */
    .wh-card:nth-child(1) { animation-delay: 0.05s; }
    .wh-card:nth-child(2) { animation-delay: 0.10s; }
    .wh-card:nth-child(3) { animation-delay: 0.15s; }
    .wh-card:nth-child(4) { animation-delay: 0.20s; }
    .wh-card:nth-child(5) { animation-delay: 0.25s; }
    .wh-card:nth-child(6) { animation-delay: 0.30s; }
</style>
@endpush

@section('content')

<!-- Page Header -->
<div class="page-header">
    <div class="page-header-left">
        <h2>إدارة المخازن</h2>
        <p>عرض وإدارة جميع مخازن الشركة</p>
    </div>
    <a href="{{ route('warehouses.create') }}" class="btn-primary">
        <div class="btn-icon"><i class="fas fa-plus"></i></div>
        مخزن جديد
    </a>
</div>

<!-- Stats Bar -->
<div class="stats-bar">
    <div class="stat-card blue">
        <div class="stat-icon"><i class="fas fa-warehouse"></i></div>
        <div class="stat-info">
            <div class="val">{{ $totalWarehouses }}</div>
            <div class="lbl">إجمالي المخازن</div>
        </div>
    </div>
    <div class="stat-card green">
        <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
        <div class="stat-info">
            <div class="val">{{ $activeWarehouses }}</div>
            <div class="lbl">المخازن النشطة</div>
        </div>
    </div>
    <div class="stat-card amber">
        <div class="stat-icon"><i class="fas fa-boxes"></i></div>
        <div class="stat-info">
            <div class="val">{{ number_format($totalProducts) }}</div>
            <div class="lbl">إجمالي الأصناف</div>
        </div>
    </div>
    <div class="stat-card rose">
        <div class="stat-icon"><i class="fas fa-dollar-sign"></i></div>
        <div class="stat-info">
            <div class="val">{{ number_format($totalValue) }}</div>
            <div class="lbl">إجمالي القيمة</div>
        </div>
    </div>
</div>

<!-- Warehouses Grid -->
<div class="warehouses-grid">
    @forelse($warehouses as $warehouse)
    <div class="wh-card">

        <!-- Card Header -->
        <div class="wh-card-header">
            <div class="wh-header-top">
                <div style="display:flex;align-items:center;gap:12px;">
                    <div class="wh-icon-wrap">
                        <i class="fas fa-warehouse"></i>
                    </div>
                    <div>
                        <p class="wh-name">{{ $warehouse->name }}</p>
                        <span class="wh-code">{{ $warehouse->code }}</span>
                    </div>
                </div>
                <span class="status-badge {{ $warehouse->is_active ? 'status-active' : 'status-inactive' }}">
                    <i class="fas fa-circle" style="font-size:7px;margin-left:4px;"></i>
                    {{ $warehouse->is_active ? 'نشط' : 'غير نشط' }}
                </span>
            </div>
        </div>

        <!-- Card Body -->
        <div class="wh-card-body">

            @if($warehouse->address)
            <div class="wh-location">
                <i class="fas fa-map-marker-alt"></i>
                <span>{{ Str::limit($warehouse->address, 55) }}</span>
            </div>
            @endif

            <!-- Mini Stats -->
            <div class="wh-stats">
                <div class="wh-stat products">
                    <p class="s-label">
                        <i class="fas fa-cube"></i> إجمالي الأصناف
                    </p>
                    <p class="s-val">{{ number_format($warehouse->products_count ?? 0) }}</p>
                </div>
                <div class="wh-stat value">
                    <p class="s-label">
                        <i class="fas fa-coins"></i> قيمة المخزون
                    </p>
                    <p class="s-val">{{ number_format($warehouse->total_value ?? 0) }}</p>
                </div>
            </div>

            @if($warehouse->manager_name)
            <div class="wh-manager">
                <div class="manager-avatar">
                    {{ mb_substr($warehouse->manager_name, 0, 1) }}
                </div>
                <div>
                    <span>{{ $warehouse->manager_name }}</span>
                    <small>مدير المخزن</small>
                </div>
            </div>
            @endif

            <!-- Actions -->
            <div class="wh-actions">
                <a href="{{ route('warehouses.show', $warehouse->id) }}" class="act-btn primary">
                    <i class="fas fa-eye"></i>
                    عرض التفاصيل
                </a>
                <a href="{{ route('warehouses.edit', $warehouse->id) }}" class="act-btn edit" title="تعديل">
                    <i class="fas fa-pen"></i>
                </a>
                <form action="{{ route('warehouses.destroy', $warehouse->id) }}" method="POST"
                      onsubmit="return confirmDelete(event, '{{ $warehouse->name }}')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="act-btn del" title="حذف">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </form>
            </div>

        </div>
    </div>
    @empty
    <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-warehouse"></i></div>
        <h3>لا توجد مخازن بعد</h3>
        <p>ابدأ بإنشاء مخزنك الأول لإدارة مخزونك بكفاءة</p>
        <a href="{{ route('warehouses.create') }}" class="btn-primary" style="display:inline-flex;">
            <div class="btn-icon"><i class="fas fa-plus"></i></div>
            إنشاء مخزن جديد
        </a>
    </div>
    @endforelse
</div>

<!-- Pagination -->
@if($warehouses->hasPages())
<div class="pagination-wrap">
    {{ $warehouses->links() }}
</div>
@endif

<!-- Delete Confirm Modal -->
<div id="deleteModal" style="
    display:none; position:fixed; inset:0; z-index:9999;
    background:rgba(15,23,42,0.6); backdrop-filter:blur(6px);
    align-items:center; justify-content:center;
">
    <div style="
        background:#fff; border-radius:20px; padding:32px;
        width:100%; max-width:400px; margin:16px;
        box-shadow:0 24px 80px rgba(15,23,42,0.2);
        animation: fadeUp 0.3s ease;
        text-align:center;
    ">
        <div style="
            width:64px; height:64px; background:rgba(239,68,68,0.1);
            border-radius:18px; display:flex; align-items:center;
            justify-content:center; margin:0 auto 18px;
            font-size:26px; color:#ef4444;
        ">
            <i class="fas fa-trash-alt"></i>
        </div>
        <h3 style="font-size:17px;font-weight:800;color:var(--text-main);margin:0 0 8px;">تأكيد الحذف</h3>
        <p style="font-size:13px;color:var(--text-muted);margin:0 0 6px;">هل أنت متأكد من حذف المخزن</p>
        <p id="deleteModalName" style="font-size:14px;font-weight:700;color:#ef4444;margin:0 0 24px;"></p>
        <p style="font-size:12px;color:var(--text-muted);margin:0 0 24px;
                  background:rgba(239,68,68,0.06);border:1px solid rgba(239,68,68,0.15);
                  border-radius:10px;padding:10px;">
            ⚠️ لا يمكن التراجع عن هذا الإجراء
        </p>
        <div style="display:flex;gap:10px;">
            <button onclick="closeDeleteModal()" style="
                flex:1; padding:11px; border-radius:11px;
                background:rgba(99,102,241,0.07); border:1px solid rgba(99,102,241,0.15);
                color:var(--text-main); font-size:13px; font-weight:600;
                cursor:pointer; font-family:'Cairo',sans-serif;
            ">إلغاء</button>
            <button id="confirmDeleteBtn" style="
                flex:1; padding:11px; border-radius:11px;
                background:linear-gradient(135deg,#ef4444,#dc2626);
                border:none; color:#fff; font-size:13px; font-weight:700;
                cursor:pointer; font-family:'Cairo',sans-serif;
                box-shadow:0 4px 14px rgba(239,68,68,0.35);
            ">حذف نهائياً</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    let pendingForm = null;

    function confirmDelete(e, name) {
        e.preventDefault();
        pendingForm = e.target.closest('form');
        document.getElementById('deleteModalName').textContent = name;
        const modal = document.getElementById('deleteModal');
        modal.style.display = 'flex';
        return false;
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').style.display = 'none';
        pendingForm = null;
    }

    document.getElementById('confirmDeleteBtn').addEventListener('click', () => {
        if (pendingForm) pendingForm.submit();
    });

    document.getElementById('deleteModal').addEventListener('click', function(e) {
        if (e.target === this) closeDeleteModal();
    });
</script>
@endpush