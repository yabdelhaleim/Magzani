@extends('layouts.app')

@section('title', 'أذونات إدخال البضاعة')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-arrow-down text-success"></i>
                أذونات إدخال البضاعة
            </h1>
            <p class="text-muted mb-0 mt-2">
                إدارة أذونات إدخال البضاعة للمخازن
            </p>
        </div>
        <div class="btn-group">
            <a href="{{ route('warehouse-orders.inbound.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                أذن إدخال جديد
            </a>
            <a href="{{ route('warehouse-orders.outbound.index') }}" class="btn btn-outline-danger">
                <i class="fas fa-arrow-up"></i>
                أذونات الإخراج
            </a>
        </div>
    </div>

    <!-- إحصائيات -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-file-alt fa-2x text-primary"></i>
                        </div>
                        <div>
                            <h6 class="mb-0">إجمالي الأذون</h6>
                            <h3 class="mb-0">{{ $orders->total() }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-check-circle fa-2x text-success"></i>
                        </div>
                        <div>
                            <h6 class="mb-0">مكتملة</h6>
                            <h3 class="mb-0">
                                {{ \App\Models\WarehouseInboundOrder::completed()->count() }}
                            </h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-clock fa-2x text-warning"></i>
                        </div>
                        <div>
                            <h6 class="mb-0">معلقة</h6>
                            <h3 class="mb-0">
                                {{ \App\Models\WarehouseInboundOrder::pending()->count() }}
                            </h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-calendar-day fa-2x text-info"></i>
                        </div>
                        <div>
                            <h6 class="mb-0">أذون اليوم</h6>
                            <h3 class="mb-0">
                                {{ \App\Models\WarehouseInboundOrder::whereDate('created_at', today())->count() }}
                            </h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- جدول الأذون -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-list text-primary"></i>
                    قائمة أذونات الإدخال
                </h5>
                <form class="d-flex" method="GET" action="">
                    <select name="warehouse_id" class="form-select me-2" style="width: 200px;">
                        <option value="">كل المخازن</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" {{ request('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                {{ $warehouse->name }}
                            </option>
                        @endforeach
                    </select>
                    <input type="text" name="search" class="form-control me-2" placeholder="بحث رقم الأذن، المرجع..." value="{{ request('search') }}">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i>
                    </button>
                    @if(request()->anyFilled(['search', 'warehouse_id']))
                        <a href="{{ route('warehouse-orders.inbound.index') }}" class="btn btn-outline-secondary ms-2">
                            <i class="fas fa-times"></i>
                        </a>
                    @endif
                </form>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>رقم الأذن</th>
                            <th>التاريخ</th>
                            <th>المخزن</th>
                            <th>عدد الأصناف</th>
                            <th>الحالة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                        <tr>
                            <td>
                                <strong>{{ $order->order_number }}</strong>
                                @if($order->reference_number)
                                <br><small class="text-muted">{{ $order->reference_number }}</small>
                                @endif
                            </td>
                            <td>{{ $order->order_date->format('Y-m-d') }}</td>
                            <td>{{ $order->warehouse->name }}</td>
                            <td>{{ $order->items->count() }} صنف</td>
                            <td>{!! $order->status_badge !!}</td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('warehouse-orders.inbound.show', $order) }}"
                                       class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('warehouse-orders.inbound.print', $order) }}"
                                       class="btn btn-sm btn-secondary" target="_blank">
                                        <i class="fas fa-print"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                                <p class="mb-0">لا يوجد أذونات إدخال</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($orders->hasPages())
            <div class="d-flex justify-content-center mt-3">
                {{ $orders->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
