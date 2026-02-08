@extends('layouts.app')

@section('title', 'تفاصيل العميل')

@section('content')
<div class="container">
    <h2 class="mb-4">تفاصيل العميل</h2>

    <table class="table table-bordered">
        <tr>
            <th>اسم العميل</th>
            <td>{{ $customer->name }}</td>
        </tr>
        <tr>
            <th>رقم الهاتف</th>
            <td>{{ $customer->phone ?? '-' }}</td>
        </tr>
        <tr>
            <th>البريد الإلكتروني</th>
            <td>{{ $customer->email ?? '-' }}</td>
        </tr>
        <tr>
            <th>العنوان</th>
            <td>{{ $customer->address ?? '-' }}</td>
        </tr>
        <tr>
            <th>تاريخ التسجيل</th>
            <td>{{ $customer->created_at->format('Y-m-d') }}</td>
        </tr>
    </table>

    <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-warning">
        تعديل
    </a>

    <a href="{{ route('customers.index') }}" class="btn btn-secondary">
        رجوع
    </a>
</div>
@endsection
