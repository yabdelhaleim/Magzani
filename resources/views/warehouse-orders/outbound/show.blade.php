@extends('layouts.app')

@section('title', 'أذن إخراج بضاعة - ' . $order->order_number)

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-arrow-up text-danger"></i>
                أذن إخراج بضاعة
            </h1>
            <p class="text-muted mb-0 mt-2">{{ $order->order_number }}</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('warehouse-orders.outbound.print', $order) }}"
               class="btn btn-outline-secondary" target="_blank">
                <i class="fas fa-print"></i>
                طباعة
            </a>
            <a href="{{ route('warehouse-orders.outbound.index') }}" class="btn btn-outline-primary">
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
                            <strong>الغرض:</strong>
                            <br>{{ $order->purpose_text }}
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>المستلم:</strong>
                            <br>{{ $order->recipient_name ?: '-' }}
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
                        الأصناف المطلوبة ({{ $order->items->count() }})
                    </h5>
                </div>
                <div class="card-body">
                    @if($order->status === 'pending')
                    <form method="POST" action="{{ route('warehouse-orders.outbound.approve', $order) }}">
                        @csrf
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            هذا الأذن معلق. قم باعتماد الكميات لتنفيذه وتنزيلها من المخزون.
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead class="table-light">
                                    <tr>
                                        <th>الصنف</th>
                                        <th>الكمية المطلوبة</th>
                                        <th>الوحدة</th>
                                        <th>الكمية المعتمدة</th>
                                        <th>الرصيد الحالي</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($order->items as $item)
                                    @php
                                    $warehouseProduct = \App\Models\ProductWarehouse::where('warehouse_id', $order->warehouse_id)
                                        ->where('product_id', $item->product_id)
                                        ->first();
                                    $currentStock = $warehouseProduct ? $warehouseProduct->quantity : 0;
                                    @endphp
                                    <tr>
                                        <td>{{ $item->product->name }}</td>
                                        <td>{{ number_format($item->requested_quantity, 3) }}</td>
                                        <td>{{ $item->unit }}</td>
                                        <td>
                                            <input type="number" name="items[{{ $item->id }}][approved_quantity]"
                                                   class="form-control" step="0.001" min="0"
                                                   value="{{ $item->approved_quantity ?? $item->requested_quantity }}"
                                                   max="{{ $currentStock }}">
                                            <small class="text-muted">الرصيد: {{ number_format($currentStock, 3) }}</small>
                                        </td>
                                        <td>
                                            <span class="{{ $currentStock < $item->requested_quantity ? 'text-danger' : 'text-success' }}">
                                                {{ number_format($currentStock, 3) }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-between mt-3">
                            @if($order->status === 'pending')
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-check"></i>
                                اعتماد وتنفيذ الأذن
                            </button>
                            <a href="{{ route('warehouse-orders.outbound.cancel', $order) }}"
                               class="btn btn-danger"
                               onclick="return confirm('هل أنت متأكد من إلغاء هذا الأذن؟')">
                                <i class="fas fa-times"></i>
                                إلغاء الأذن
                            </a>
                            @endif
                        </div>
                    </form>
                    @else
                    <div class="table-responsive">
                        <table class="table">
                            <thead class="table-light">
                                <tr>
                                    <th>الصنف</th>
                                    <th>الكمية المطلوبة</th>
                                    <th>الكمية المعتمدة</th>
                                    <th>الوحدة</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items as $item)
                                <tr>
                                    <td>{{ $item->product->name }}</td>
                                    <td>{{ number_format($item->requested_quantity, 3) }}</td>
                                    <td>
                                        <strong>{{ $item->approved_quantity ? number_format($item->approved_quantity, 3) : '-' }}</strong>
                                    </td>
                                    <td>{{ $item->unit }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
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
                        <strong>إجمالي الكميات المطلوبة:</strong>
                        <span class="float-end">{{ number_format($order->items->sum('requested_quantity'), 3) }}</span>
                    </div>
                    @if($order->status === 'completed')
                    <div class="mb-3">
                        <strong>إجمالي الكميات المعتمدة:</strong>
                        <span class="float-end">{{ number_format($order->items->sum('approved_quantity') ?: $order->items->sum('requested_quantity'), 3) }}</span>
                    </div>
                    <div class="mb-3">
                        <strong>تاريخ التنفيذ:</strong>
                        <span class="float-end">{{ $order->completed_at?->format('Y-m-d H:i') }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
