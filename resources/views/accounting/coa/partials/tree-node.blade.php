@php
    $hasChildren = $node->children->count() > 0;
    $indentClass = match($level) {
        2 => 'mr-6',
        3 => 'mr-12',
        4 => 'mr-16',
        default => 'mr-0'
    };
@endphp

<div class="space-y-1 {{ $indentClass }}" x-data="{ expanded: expandedNodes['{{ $node->id }}'] || false }" @expand-all.window="expanded = true" @collapse-all.window="expanded = false">
    <!-- Account Line Row -->
    <div class="flex items-center justify-between p-3 bg-gray-50 hover:bg-blue-50 rounded-lg border border-gray-100 transition-colors">
        <div class="flex items-center gap-3">
            <!-- Expand/Collapse Button if children exist -->
            @if($hasChildren)
                <button @click="expanded = !expanded" class="w-6 h-6 rounded flex items-center justify-center bg-white border border-gray-200 text-gray-500 hover:text-blue-600 transition-colors">
                    <i class="fas" :class="expanded ? 'fa-chevron-down' : 'fa-chevron-left'"></i>
                </button>
            @else
                <div class="w-6 h-6 flex items-center justify-center text-gray-300">
                    <span class="w-1.5 h-1.5 rounded-full bg-gray-300"></span>
                </div>
            @endif

            <!-- Account Details -->
            <div>
                <span class="font-mono text-sm text-gray-500 bg-gray-200 px-2 py-0.5 rounded font-semibold">{{ $node->code }}</span>
                <span class="font-semibold text-gray-800 ml-2">{{ $node->name_ar }}</span>
                @if($node->name_en)
                    <span class="text-xs text-gray-400 font-mono">({{ $node->name_en }})</span>
                @endif
            </div>

            <!-- Type badge -->
            <span class="px-2 py-0.5 text-xs rounded bg-blue-50 text-blue-700 font-medium font-mono">
                {{ optional($node->accountType)->name_ar ?? 'عام' }}
            </span>
        </div>

        <div class="flex items-center gap-4">
            <!-- Balance & Normal Balance indicator -->
            <div class="text-left">
                <span class="font-mono font-bold text-sm text-gray-900 block">
                    {{ number_format(optional($node->balance)->balance ?? 0, 2) }}
                </span>
                <span class="text-xxs text-gray-400 uppercase font-mono tracking-wider">
                    @if(optional($node->accountType)->normal_balance === 'debit')
                        مدين (DR)
                    @else
                        دائن (CR)
                    @endif
                </span>
            </div>

            <!-- Status Indicator -->
            <span class="w-2.5 h-2.5 rounded-full {{ $node->is_active ? 'bg-green-500' : 'bg-gray-400' }}" title="{{ $node->is_active ? 'نشط' : 'معطل' }}"></span>

            <!-- CRUD Dropdown / Actions -->
            <div class="flex items-center gap-1.5">
                <a href="{{ route('accounting.coa.show', $node->id) }}" class="p-1 text-gray-500 hover:text-blue-600 transition-colors" title="عرض كشف الحساب (دفتر الأستاذ)">
                    <i class="fas fa-file-invoice"></i>
                </a>
                @if(!$node->is_system)
                    <a href="{{ route('accounting.coa.edit', $node->id) }}" class="p-1 text-gray-500 hover:text-yellow-600 transition-colors" title="تعديل">
                        <i class="fas fa-edit"></i>
                    </a>
                    <form method="POST" action="{{ route('accounting.coa.destroy', $node->id) }}" onsubmit="return confirm('هل أنت متأكد من حذف هذا الحساب من الدليل؟')" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-1 text-gray-500 hover:text-red-600 transition-colors" title="حذف">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <!-- Children Recursive Render Container -->
    @if($hasChildren)
        <div x-show="expanded" x-collapse x-cloak class="space-y-2 mt-1 border-r-2 border-gray-200 border-dashed mr-4">
            @foreach($node->children as $child)
                @include('accounting.coa.partials.tree-node', ['node' => $child, 'level' => $level + 1])
            @endforeach
        </div>
    @endif
</div>
