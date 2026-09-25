@props([
    'columns'   => [],
    'rows'      => [],
    'striped'   => false,
    'hoverable' => true,
])

<div class="overflow-hidden rounded-2xl border border-ink-200 bg-white">
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    @foreach($columns as $column)
                        <th style="{{ $column['width'] ? 'width: '.$column['width'] : '' }}">
                            {{ $column['label'] }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    <tr>
                        @foreach($columns as $column)
                            <td>
                                @if(isset($column['render']))
                                    {!! $column['render']($row) !!}
                                @else
                                    {{ data_get($row, $column['key'] ?? '') }}
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($columns) }}" class="!py-12 text-center">
                            <div class="empty-state">
                                <div class="empty-state__icon mx-auto">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m9-7a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <div class="empty-state__title">No data available</div>
                                <div class="empty-state__desc">There are no records to display at this time.</div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
