@extends('layouts.app')

@section('title', 'تعديل بيانات العميل')

@section('content')
<div class="container">
    <h2 class="mb-4">تعديل بيانات العميل</h2>

    <form action="{{ route('customers.update', $customer->id) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- Errors --}}
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Name --}}
        <div class="mb-3">
            <label class="form-label">اسم العميل</label>
            <input type="text"
                   name="name"
                   class="form-control"
                   value="{{ old('name', $customer->name) }}"
                   required>
        </div>

        {{-- Phone --}}
        <div class="mb-3">
            <label class="form-label">رقم الهاتف</label>
            <input type="text"
                   name="phone"
                   class="form-control"
                   value="{{ old('phone', $customer->phone) }}">
        </div>

        {{-- Email --}}
        <div class="mb-3">
            <label class="form-label">البريد الإلكتروني</label>
            <input type="email"
                   name="email"
                   class="form-control"
                   value="{{ old('email', $customer->email) }}">
        </div>

        {{-- Address --}}
        <div class="mb-3">
            <label class="form-label">العنوان</label>
            <textarea name="address"
                      class="form-control"
                      rows="3">{{ old('address', $customer->address) }}</textarea>
        </div>

        {{-- Buttons --}}
        <button type="submit" class="btn btn-primary">
            تحديث
        </button>

        <a href="{{ route('customers.index') }}" class="btn btn-secondary">
            رجوع
        </a>
    </form>
</div>
@endsection
