<div>
    <!-- Top Controls (Filter & Search) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
        <!-- Length Filter -->
        <div class="flex items-center gap-2 {{ !isset($show_filter) || $show_filter == true ? '' : 'hidden' }}">
            <label class="text-sm font-medium text-gray-700 whitespace-nowrap">Show</label>
            <select wire:model.live.change="length" 
                    class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-sm text-gray-700 shadow-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500">
                @foreach ($lengthOptions as $item)
                    <option value="{{ $item }}">{{ $item }}</option>
                @endforeach
            </select>
        </div>

        <!-- Keyword Filter -->
        <div class="flex items-center gap-2 w-full sm:w-auto {{ !isset($keyword_filter) || $keyword_filter == true ? '' : 'hidden' }}">
            <label class="text-sm font-medium text-gray-700 whitespace-nowrap">Kata Kunci</label>
            <input wire:model.live.debounce.300ms="search" 
                   type="text" 
                   class="w-full sm:w-64 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-sm text-gray-700 shadow-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500"
                   placeholder="Cari...">
        </div>
    </div>

    <!-- Table Container with Relative Position for Overlay Loading -->
    <div class="relative min-h-[150px]">
        <!-- Loading Overlay -->
        <div wire:loading 
             class="absolute inset-0 z-20 flex items-center justify-center bg-gray-900/10 backdrop-blur-[1px] transition-all rounded-lg">
            <div class="flex items-center gap-2 rounded-lg bg-white px-4 py-2.5 shadow-xl text-sm font-semibold text-gray-700 border border-gray-100">
                <svg class="animate-spin h-4 w-4 text-gray-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Loading...
            </div>
        </div>

        <!-- Table Responsive Wrapper -->
        <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
            <table class="w-full text-left text-sm whitespace-nowrap border-collapse">
                <thead>
                    <tr class="bg-surface-container-low/50 border-b border-gray-200">
                        @foreach ($columns as $index => $col)
                            <th class="px-4 py-3 text-[11px] font-label font-bold tracking-widest text-on-surface-variant uppercase" 
                                wire:key='datatable_header_{{ $index }}'>
                                @if (!isset($col['sortable']) || $col['sortable'])
                                    @php $isSortAscending = $col['key'] == $sortBy && $sortDirection == 'asc'@endphp
                                    <button type="button" 
                                            class="inline-flex items-center gap-2 font-bold p-0 m-0 border-0 bg-transparent text-left cursor-pointer focus:outline-none"
                                            wire:click="datatableSort('{{ $col['key'] }}')">
                                        <span>{{ $col['name'] }}</span>
                                        <span class="inline-flex flex-col leading-none text-xs">
                                            <i class="ki-duotone ki-up m-0 p-0 {{ $isSortAscending ? 'text-gray-900 font-extrabold' : 'text-gray-400' }}"></i>
                                            <i class="ki-duotone ki-down m-0 p-0 {{ $isSortAscending ? 'text-gray-400' : 'text-gray-900 font-extrabold' }}"></i>
                                        </span>
                                    </button>
                                @else
                                    <div class="py-1">
                                        {{ $col['name'] }}
                                    </div>
                                @endif
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-variant/20 bg-white">
                    @foreach ($data as $index => $item)
                        <tr wire:key='datatable_row_{{ $index }}'
                            class="hover:bg-surface-container-low transition-colors group">
                            @foreach ($columns as $col)
                                @php
                                    $cell_style = '';
                                    if (isset($col['style'])) {
                                        $cell_style = is_callable($col['style'])
                                            ? call_user_func($col['style'], $item, $index)
                                            : $col['style'];
                                    }

                                    $cell_class = '';
                                    if (isset($col['class'])) {
                                        $cell_class = is_callable($col['class'])
                                            ? call_user_func($col['class'], $item, $index)
                                            : $col['class'];
                                    }
                                @endphp

                                @if (isset($col['render']) && is_callable($col['render']))
                                    <td class="px-4 py-2.5 text-gray-700 {!! $cell_class !!}" style="{!! $cell_style !!}">
                                        {!! call_user_func($col['render'], $item) !!}
                                    </td>
                                @elseif (isset($col['key']))
                                    <td class="px-4 py-2.5 text-gray-700 {!! $cell_class !!}" style="{!! $cell_style !!}">
                                        {{ $item->{$col['key']} }}
                                    </td>
                                @endif
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bottom Controls (Total & Pagination) -->
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mt-4 text-sm text-gray-600">
        <div>
            <em class="not-italic font-medium">Total Data: {{ $data->total() }}</em>
        </div>
        <div>
            {{ $data->links(data: ['scrollTo' => false]) }}
        </div>
    </div>
</div>