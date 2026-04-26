@extends('layouts.app')

@section('title', 'أذن إدخال بضاعة - ' . $order->order_number)

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-arrow-down text-success"></i>
                أذن إدخال بضاعة
            </h1>
            <p class="text-muted mb-0 mt-2">{{ $order->order_number }}</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('warehouse-orders.inbound.print', $order) }}"
               class="btn btn-outline-secondary" target="_blank">
                <i class="fas fa-print"></i>
                طباعة
            </a>
            <a href="{{ route('warehouse-orders.inbound.index') }}" class="btn btn-outline-primary">
                <i class="fas fa-list"></i>
                قائمة الأذون
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-file-alt text-primary"></i>
                            بيانات الأذن
                        </h5>
                        {!! $order->status_badge !!}
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>رقم الأذن:</strong>
                            <br>{{ $order->order_number }}
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>التاريخ:</strong>
                            <br>{{ $order->order_date->format('Y-m-d') }}
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>المخزن:</strong>
                            <br>{{ $order->warehouse->name }}
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>رقم المرجع:</strong>
                            <br>{{ $order->reference_number ?: '-' }}
                        </div>
                        <div class="col-12 mb-3">
                            <strong>الملاحظات:</strong>
                            <br>{{ $order->notes ?: 'لا توجد ملاحظات' }}
                        </div>
                        <div class="col-12">
                            <strong>تم الإنشاء بواسطة:</strong>
                            <br>{{ $order->creator->name }} -
                            {{ $order->created_at->format('Y-m-d H:i') }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-boxes text-warning"></i>
                        الأصناف ({{ $order->items->count() }})
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead class="table-light">
                                <tr>
                                    <th>الصنف</th>
                                    <th>الكمية</th>
                                    <th>الوحدة</th>
                                    <th>التكلفة</th>
                                    <th>الإجمالي</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items as $item)
                                <tr>
                                    <td>{{ $item->product->name }}</td>
                                    <td>{{ number_format($item->quantity, 3) }}</td>
                                    <td>{{ $item->unit }}</td>
                                    <td>{{ $item->unit_cost ? number_format($item->unit_cost, 2) : '-' }}</td>
                                    <td>{{ $item->total_cost ? number_format($item->total_cost, 2) : '-' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                @if($order->items->whereNotNull('total_cost')->isNotEmpty())
                                <tr>
                                    <th colspan="4" class="text-left">الإجمالي:</th>
                                    <th>{{ number_format($order->items->sum('total_cost'), 2) }}</th>
                                </tr>
                                @endif
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            @if(session('company_logo'))
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body text-center">
                    <img src="{{ session('company_logo') }}" alt="شعار الشركة"
                         class="img-fluid mb-2" style="max-height: 80px;">
                    <h5 class="mb-0">{{ session('company_name', 'اسم الشركة') }}</h5>
                    @if(session('company_address'))
                    <p class="mb-0 text-muted small">{{ session('company_address') }}</p>
                    @endif
                </div>
            </div>
            @endif

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle text-info"></i>
                        معلومات إضافية
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>عدد الأصناف:</strong>
                        <span class="float-end">{{ $order->items->count() }}</span>
                    </div>
                    <div class="mb-3">
                        <strong>إجمالي الكميات:</strong>
                        <span class="float-end">{{ number_format($order->items->sum('quantity'), 3) }}</span>
                    </div>
                    @if($order->items->whereNotNull('total_cost')->isNotEmpty())
                    <div class="mb-3">
                        <strong>إجمالي التكلفة:</strong>
                        <span class="float-end">{{ number_format($order->items->sum('total_cost'), 2) }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
