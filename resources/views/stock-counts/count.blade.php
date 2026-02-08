@extends('layouts.app')

@section('title', 'تنفيذ الجرد - ' . $stockCount->count_number)

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('stock-counts.show', $stockCount->id) }}" 
               class="w-12 h-12 bg-white border-2 border-gray-200 hover:border-gray-300 rounded-xl flex items-center justify-center transition-all group">
                <svg class="w-6 h-6 text-gray-600 group-hover:text-gray-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <h1 class="text-3xl font-black text-gray-900">{{ $stockCount->count_number }}</h1>
                <p class="text-gray-500 font-medium">{{ $stockCount->warehouse->name }}</p>
            </div>
        </div>
        
        <div class="flex gap-3">
            <button onclick="saveAllProgress()" 
                    class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-xl font-bold transition-all shadow-md hover:shadow-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                </svg>
                حفظ التقدم
            </button>

            <form action="{{ route('stock-counts.complete', $stockCount->id) }}" method="POST" 
                  onsubmit="return confirmComplete()">
                @csrf
                <button type="submit" id="completeBtn"
                        class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-xl font-bold transition-all shadow-md hover:shadow-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    إتمام الجرد
                </button>
            </form>
        </div>
    </div>

    <!-- Progress Section -->
    <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl shadow-sm border-2 border-blue-100 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-black text-gray-900">التقدم</h3>
            <span class="text-sm font-bold text-gray-600">
                <span id="progress-text">{{ $stockCount->items_counted }}</span> / {{ $stockCount->total_items }}
            </span>
        </div>
        <div class="w-full bg-white/50 rounded-full h-6 overflow-hidden shadow-inner">
            <div id="progress-bar" 
                 class="bg-gradient-to-r from-blue-600 to-indigo-600 h-full rounded-full transition-all duration-500 flex items-center justify-end px-3" 
                 style="width: {{ $stockCount->progress_percentage }}%">
                <span class="text-white text-sm font-bold" id="progress-percentage-text">
                    {{ number_format($stockCount->progress_percentage, 1) }}%
                </span>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
            </div>
            <p class="text-blue-100 text-sm font-semibold mb-1">إجمالي الأصناف</p>
            <h3 class="text-4xl font-black">{{ $stockCount->total_items }}</h3>
        </div>

        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-2xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-green-100 text-sm font-semibold mb-1">تم الجرد</p>
            <h3 class="text-4xl font-black" id="counted-items">{{ $stockCount->items_counted }}</h3>
        </div>

        <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-2xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-orange-100 text-sm font-semibold mb-1">متبقي</p>
            <h3 class="text-4xl font-black" id="pending-items">{{ $stockCount->total_items - $stockCount->items_counted }}</h3>
        </div>

        <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-2xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
            </div>
            <p class="text-red-100 text-sm font-semibold mb-1">فروقات</p>
            <h3 class="text-4xl font-black" id="discrepancies-count">{{ $stockCount->discrepancies }}</h3>
        </div>
    </div>

    <!-- Search & Filter -->
    <div class="bg-white rounded-2xl shadow-sm border-2 border-gray-100 p-5">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="relative">
                <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" 
                       id="searchInput" 
                       class="w-full pl-12 pr-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all font-medium"
                       placeholder="ابحث عن منتج...">
            </div>
            <select id="filterStatus" 
                    class="px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all font-medium">
                <option value="">جميع الحالات</option>
                <option value="pending">معلق</option>
                <option value="counted">تم الجرد</option>
                <option value="variance">به فروقات</option>
            </select>
            <button onclick="scanBarcode()" 
                    class="bg-purple-600 hover:bg-purple-700 text-white px-5 py-3 rounded-xl font-bold transition-all shadow-md hover:shadow-lg flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                </svg>
                مسح باركود
            </button>
        </div>
    </div>

    <!-- Items Table -->
    <div class="bg-white rounded-2xl shadow-sm border-2 border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full" id="itemsTable">
                <thead class="bg-gray-50 border-b-2 border-gray-100">
                    <tr>
                        <th class="px-6 py-4 text-right text-xs font-black text-gray-700 uppercase">#</th>
                        <th class="px-6 py-4 text-right text-xs font-black text-gray-700 uppercase">المنتج</th>
                        <th class="px-6 py-4 text-right text-xs font-black text-gray-700 uppercase">كمية النظام</th>
                        <th class="px-6 py-4 text-right text-xs font-black text-gray-700 uppercase">الكمية الفعلية</th>
                        <th class="px-6 py-4 text-right text-xs font-black text-gray-700 uppercase">الفرق</th>
                        <th class="px-6 py-4 text-right text-xs font-black text-gray-700 uppercase">ملاحظات</th>
                        <th class="px-6 py-4 text-right text-xs font-black text-gray-700 uppercase">إجراء</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($stockCount->items as $index => $item)
                    <tr class="item-row hover:bg-gray-50 transition-colors {{ $item->status == 'counted' ? 'bg-green-50/50' : '' }}" 
                        data-item-id="{{ $item->id }}"
                        data-status="{{ $item->status }}">
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center justify-center w-10 h-10 bg-gray-100 rounded-xl text-sm font-bold text-gray-700">
                                {{ $index + 1 }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                @if($item->status == 'counted')
                                <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center">
                                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                @else
                                <div class="w-10 h-10 bg-gray-100 rounded-xl flex items-center justify-center">
                                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                @endif
                                <div>
                                    <div class="font-bold text-gray-900">{{ $item->product->name }}</div>
                                    <div class="text-sm text-gray-500 font-medium">{{ $item->product->code }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-xl font-black text-gray-900">{{ number_format($item->system_quantity, 2) }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <input type="number" 
                                   class="actual-quantity w-32 px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-center font-bold transition-all"
                                   value="{{ $item->actual_quantity }}"
                                   step="0.01"
                                   min="0"
                                   placeholder="0.00"
                                   onchange="calculateVariance(this, {{ $item->system_quantity }})">
                        </td>
                        <td class="px-6 py-4">
                            <span class="variance-display">
                                @if($item->actual_quantity !== null)
                                    @php $variance = $item->actual_quantity - $item->system_quantity; @endphp
                                    <span class="inline-flex items-center px-3 py-2 rounded-xl text-lg font-black {{ $variance > 0 ? 'bg-green-100 text-green-700' : ($variance < 0 ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700') }}">
                                        {{ $variance > 0 ? '+' : '' }}{{ number_format($variance, 2) }}
                                    </span>
                                @else
                                    <span class="text-gray-400 font-bold">--</span>
                                @endif
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <input type="text" 
                                   class="notes-input w-full px-4 py-2 bg-gray-50 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm transition-all"
                                   value="{{ $item->notes }}"
                                   placeholder="ملاحظات...">
                        </td>
                        <td class="px-6 py-4">
                            <button onclick="saveItem({{ $item->id }}, this)"
                                    class="save-btn bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl font-bold transition-all shadow-sm hover:shadow-md text-sm flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                حفظ
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function calculateVariance(input, systemQty) {
    const actualQty = parseFloat(input.value) || 0;
    const variance = actualQty - systemQty;
    const varianceDisplay = input.closest('tr').querySelector('.variance-display');
    
    let colorClass = variance > 0 ? 'bg-green-100 text-green-700' : (variance < 0 ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700');
    varianceDisplay.innerHTML = `<span class="inline-flex items-center px-3 py-2 rounded-xl text-lg font-black ${colorClass}">
        ${variance > 0 ? '+' : ''}${variance.toFixed(2)}
    </span>`;
}

async function saveItem(itemId, button) {
    const row = button.closest('tr');
    const actualQty = row.querySelector('.actual-quantity').value;
    const notes = row.querySelector('.notes-input').value;
    
    if (!actualQty || actualQty === '') {
        alert('⚠️ يجب إدخال الكمية الفعلية');
        return;
    }
    
    button.disabled = true;
    button.innerHTML = '<svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> جاري...';
    
    try {
        const response = await fetch(`/stock-counts/{{ $stockCount->id }}/items/${itemId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ actual_quantity: actualQty, notes: notes })
        });
        
        const data = await response.json();
        
        if (data.success) {
            row.classList.add('bg-green-50/50');
            row.dataset.status = 'counted';
            updateStats();
            showToast('✅ تم حفظ الجرد بنجاح', 'success');
            button.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> محفوظ';
            button.classList.remove('bg-blue-600', 'hover:bg-blue-700');
            button.classList.add('bg-green-600');
        } else {
            throw new Error(data.message);
        }
    } catch (error) {
        alert('❌ حدث خطأ: ' + error.message);
        button.disabled = false;
        button.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> حفظ';
    }
}

async function saveAllProgress() {
    const pendingRows = document.querySelectorAll('.item-row[data-status="pending"]');
    if (pendingRows.length === 0) {
        alert('✅ جميع الأصناف محفوظة');
        return;
    }
    
    let saved = 0;
    for (const row of pendingRows) {
        const actualQty = row.querySelector('.actual-quantity').value;
        if (actualQty && actualQty !== '') {
            const itemId = row.dataset.itemId;
            const button = row.querySelector('.save-btn');
            await saveItem(itemId, button);
            saved++;
        }
    }
    showToast(`✅ تم حفظ ${saved} صنف`, 'success');
}

function updateStats() {
    const rows = document.querySelectorAll('.item-row');
    const total = rows.length;
    const counted = document.querySelectorAll('.item-row[data-status="counted"]').length;
    const pending = total - counted;
    const progress = (counted / total) * 100;
    
    let discrepancies = 0;
    document.querySelectorAll('.variance-display span').forEach(span => {
        const text = span.textContent.trim();
        if (text !== '--' && text !== '') {
            const variance = parseFloat(text);
            if (variance && variance !== 0) discrepancies++;
        }
    });
    
    document.getElementById('counted-items').textContent = counted;
    document.getElementById('pending-items').textContent = pending;
    document.getElementById('discrepancies-count').textContent = discrepancies;
    document.getElementById('progress-text').textContent = counted;
    document.getElementById('progress-percentage-text').textContent = progress.toFixed(1) + '%';
    document.getElementById('progress-bar').style.width = progress + '%';
    
    if (pending === 0) {
        document.getElementById('completeBtn').disabled = false;
    }
}

document.getElementById('searchInput').addEventListener('input', function(e) {
    const search = e.target.value.toLowerCase();
    document.querySelectorAll('.item-row').forEach(row => {
        const productName = row.querySelector('.font-bold').textContent.toLowerCase();
        const productCode = row.querySelector('.text-sm').textContent.toLowerCase();
        row.style.display = (productName.includes(search) || productCode.includes(search)) ? '' : 'none';
    });
});

document.getElementById('filterStatus').addEventListener('change', function(e) {
    const status = e.target.value;
    document.querySelectorAll('.item-row').forEach(row => {
        if (!status) {
            row.style.display = '';
        } else if (status === 'variance') {
            const variance = row.querySelector('.variance-display span').textContent.trim();
            const hasVariance = variance !== '--' && variance !== '' && parseFloat(variance) !== 0;
            row.style.display = hasVariance ? '' : 'none';
        } else {
            row.style.display = row.dataset.status === status ? '' : 'none';
        }
    });
});

function confirmComplete() {
    const pending = document.querySelectorAll('.item-row[data-status="pending"]').length;
    if (pending > 0) {
        return confirm(`⚠️ يوجد ${pending} صنف لم يتم جرده بعد.\n\nهل تريد المتابعة؟`);
    }
    return confirm('✅ هل أنت متأكد من إتمام الجرد؟\n\nسيتم تطبيق جميع التسويات على المخزون.');
}

function showToast(message, type) {
    const toast = document.createElement('div');
    toast.className = `fixed top-6 left-1/2 -translate-x-1/2 px-6 py-4 rounded-xl shadow-2xl text-white font-bold z-50 ${
        type === 'success' ? 'bg-green-600' : 'bg-red-600'
    }`;
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

function scanBarcode() {
    alert('🔜 ميزة مسح الباركود قريباً');
}

updateStats();
</script>
@endsection