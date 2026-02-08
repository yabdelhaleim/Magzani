@extends('layouts.app')

@section('title', 'إضافة منتج جديد')
@section('page-title', 'إضافة منتج جديد')

@section('content')
<div class="max-w-6xl mx-auto" x-data="productCreateAppV3()">
    
    {{-- تنبيهات --}}
    @if(session('success'))
        <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-lg animate-pulse">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="font-semibold text-green-800">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="font-semibold text-red-800">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
            <div class="flex items-start gap-2">
                <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div class="flex-1">
                    <p class="font-semibold text-red-800 mb-2">يوجد أخطاء في النموذج:</p>
                    <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

<form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    
    {{-- نفس الحقول من create.blade.php لكن مع القيم القديمة --}}
    
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="purchase_price">سعر الشراء <span class="text-danger">*</span></label>
                <input type="number" 
                       name="purchase_price"  {{-- ✅ --}}
                       id="purchase_price" 
                       class="form-control @error('purchase_price') is-invalid @enderror" 
                       value="{{ old('purchase_price', $product->purchase_price) }}" 
                       step="0.01" 
                       min="0" 
                       required>
                @error('purchase_price')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label for="selling_price">سعر البيع <span class="text-danger">*</span></label>
                <input type="number" 
                       name="selling_price"  {{-- ✅ --}}
                       id="selling_price" 
                       class="form-control @error('selling_price') is-invalid @enderror" 
                       value="{{ old('selling_price', $product->selling_price) }}" 
                       step="0.01" 
                       min="0" 
                       required>
                @error('selling_price')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    {{-- باقي الحقول... --}}
</form></div>

@push('scripts')
<script>
function productEditAppV2() {
    return {
        baseUnit: '{{ old("base_unit", $product->basePricing?->base_unit ?? $product->base_unit ?? "") }}',
        baseUnitLabel: '',
        category: '{{ old("category", $product->category ?? "") }}',
        basePurchasePrice: parseFloat('{{ old("base_purchase_price", $product->basePricing?->base_purchase_price ?? $product->base_purchase_price ?? 0) }}'),
        profitType: '{{ old("profit_type", $product->basePricing?->profit_type ?? "fixed") }}',
        profitValue: parseFloat('{{ old("profit_value", $product->basePricing?->profit_value ?? 0) }}'),
        calculatedSellingPrice: 0,
        calculatedProfit: 0,
        profitPercentage: 0,
        
        init() {
            this.updateBaseUnitLabel();
            this.calculatePrices();
        },
        
        validateForm() {
            // التحقق من الحقول المطلوبة
            if (!this.baseUnit) {
                alert('⚠️ يجب اختيار الوحدة الأساسية');
                return false;
            }
            
            if (this.basePurchasePrice <= 0) {
                alert('⚠️ يجب إدخال سعر شراء صحيح');
                return false;
            }
            
            if (this.profitValue <= 0) {
                alert('⚠️ يجب إدخال هامش ربح صحيح');
                return false;
            }
            
            if (this.calculatedSellingPrice <= 0) {
                alert('⚠️ سعر البيع المحسوب غير صحيح');
                return false;
            }
            
            return true;
        },
        
        updateBaseUnitLabel() {
            const select = document.querySelector('[name="base_unit"]');
            if (select && select.selectedOptions[0]) {
                this.baseUnitLabel = select.selectedOptions[0].text;
            }
        },
        
        calculatePrices() {
            const purchase = parseFloat(this.basePurchasePrice) || 0;
            const profit = parseFloat(this.profitValue) || 0;
            
            if (this.profitType === 'percentage') {
                this.calculatedProfit = (purchase * profit) / 100;
            } else {
                this.calculatedProfit = profit;
            }
            
            this.calculatedSellingPrice = purchase + this.calculatedProfit;
            
            if (purchase > 0) {
                this.profitPercentage = (this.calculatedProfit / purchase) * 100;
            } else {
                this.profitPercentage = 0;
            }
        },
        
        formatPrice(value) {
            return new Intl.NumberFormat('ar-EG', {
                style: 'decimal',
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(value || 0) + ' ج.م';
        }
    }
}
</script>
@endpush
@endsection