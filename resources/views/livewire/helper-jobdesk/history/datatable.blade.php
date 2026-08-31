<div>
    <!-- Top Controls (Filter & Search) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
        <!-- Length Filter -->
        <div class="flex items-center gap-2 {{ !isset($show_filter) || $show_filter == true ? '' : 'hidden' }}">
            <label class="text-xs font-semibold text-slate-600 dark:text-slate-400 whitespace-nowrap">Show</label>
            <select wire:model.live.change="length" 
                    class="rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs text-slate-700 shadow-xs focus:border-blue-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                @foreach ($lengthOptions as $item)
                    <option value="{{ $item }}">{{ $item }}</option>
                @endforeach
            </select>
        </div>

        <!-- Keyword Filter -->
        <div class="flex items-center gap-2 w-full sm:w-auto {{ !isset($keyword_filter) || $keyword_filter == true ? '' : 'hidden' }}">
            <div class="relative w-full sm:w-64">
                <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
                <input wire:model.live.debounce.300ms="search" 
                       type="text" 
                       class="w-full pl-8 pr-3 py-1.5 rounded-lg border border-slate-300 bg-white text-xs text-slate-700 shadow-xs focus:border-blue-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                       placeholder="Cari aktivitas, grup, status...">
            </div>
        </div>
    </div>

    <!-- Container with Relative Position for Overlay Loading -->
    <div class="relative min-h-[150px]">
        <!-- Loading Overlay -->
        <div wire:loading 
             class="absolute inset-0 z-20 flex items-center justify-center bg-slate-900/10 backdrop-blur-[1px] transition-all rounded-xl">
            <div class="flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 shadow-xl text-xs font-bold text-slate-700 border border-slate-100 dark:bg-slate-800 dark:text-white dark:border-slate-700">
                <svg class="animate-spin h-4 w-4 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Memuat data...
            </div>
        </div>

        <!-- 1. DESKTOP VIEW: Full 8-Column Tabular Layout (Hidden on Mobile) -->
        <div class="hidden md:block overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs">
            <table class="w-full text-left text-sm whitespace-nowrap border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/70 border-b border-slate-200 dark:border-slate-800">
                        @foreach ($columns as $index => $col)
                            <th class="px-4 py-3 text-[11px] font-bold tracking-wider text-slate-600 dark:text-slate-300 uppercase" 
                                wire:key='datatable_header_{{ $index }}'>
                                @if (!isset($col['sortable']) || $col['sortable'])
                                    @php $isSortAscending = $col['key'] == $sortBy && $sortDirection == 'asc'; @endphp
                                    <button type="button" 
                                            class="inline-flex items-center gap-1 font-bold p-0 m-0 border-0 bg-transparent text-left cursor-pointer focus:outline-none text-slate-700 dark:text-slate-300"
                                            wire:click="datatableSort('{{ $col['key'] }}')">
                                        <span>{{ $col['name'] }}</span>
                                        <span class="material-symbols-outlined text-xs {{ $col['key'] == $sortBy ? 'text-blue-600' : 'text-slate-400' }}">
                                            {{ $isSortAscending ? 'arrow_upward' : 'arrow_downward' }}
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
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 bg-white dark:bg-slate-900">
                    @forelse ($data as $index => $item)
                        <tr wire:key='datatable_row_{{ $index }}'
                            class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
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
                                    <td class="px-4 py-3 text-slate-700 dark:text-slate-300 {!! $cell_class !!}" style="{!! $cell_style !!}">
                                        {!! call_user_func($col['render'], $item) !!}
                                    </td>
                                @elseif (isset($col['key']))
                                    <td class="px-4 py-3 text-slate-700 dark:text-slate-300 {!! $cell_class !!}" style="{!! $cell_style !!}">
                                        {{ $item->{$col['key']} }}
                                    </td>
                                @endif
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($columns) }}" class="px-4 py-8 text-center text-xs text-slate-400">
                                Tidak ada data pekerjaan untuk filter petugas dan tanggal ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- 2. MOBILE VIEW: Native-Feel Card Feed (Visible ONLY on Mobile < md) -->
        <div class="block md:hidden space-y-3">
            @forelse ($data as $index => $item)
                @php
                    $isCompleted = !empty($item->finish_at);
                    $isInProgress = !empty($item->start_at) && empty($item->finish_at);
                    
                    // Duration calculation
                    $durationText = '-';
                    if (!empty($item->start_at) && !empty($item->finish_at)) {
                        $start = \Illuminate\Support\Carbon::parse($item->start_at)->setTimezone('Asia/Jakarta');
                        $finish = \Illuminate\Support\Carbon::parse($item->finish_at)->setTimezone('Asia/Jakarta');
                        $diffMinutes = (int) round(abs($finish->diffInMinutes($start)));
                        $hours = intdiv($diffMinutes, 60);
                        $mins = $diffMinutes % 60;
                        $durationText = $hours > 0 ? "{$hours}j {$mins}m" : "{$mins} mnt";
                    }

                    $startFormatted = !empty($item->start_at) 
                        ? \Illuminate\Support\Carbon::parse($item->start_at)->setTimezone('Asia/Jakarta')->format('H:i') 
                        : '-';
                    $finishFormatted = !empty($item->finish_at) 
                        ? \Illuminate\Support\Carbon::parse($item->finish_at)->setTimezone('Asia/Jakarta')->format('H:i') 
                        : '-';
                @endphp

                <div wire:key='mobile_card_{{ $index }}' 
                     class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 p-4 shadow-xs space-y-3">
                    
                    <!-- Card Header: Type Badge, Category, and Status -->
                    <div class="flex items-start justify-between gap-2 border-b border-slate-100 dark:border-slate-800 pb-2.5">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            @if ($item->task_type === 'Rutinitas')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300 border border-blue-200/60">
                                    <span class="material-symbols-outlined text-[11px]">checklist</span> Rutin
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-purple-50 text-purple-700 dark:bg-purple-950/40 dark:text-purple-300 border border-purple-200/60">
                                    <span class="material-symbols-outlined text-[11px]">assignment</span> Request
                                </span>
                            @endif

                            <span class="text-xs font-black text-slate-800 dark:text-slate-200 uppercase tracking-tight">
                                {{ $item->category }}
                            </span>
                        </div>

                        <!-- Status Badge -->
                        <div>
                            @if ($isCompleted)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border border-emerald-200/80">
                                    <span class="material-symbols-outlined text-xs">check_circle</span> Selesai
                                </span>
                            @elseif ($isInProgress)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 border border-amber-200/80">
                                    <span class="size-1.5 rounded-full bg-amber-500 animate-ping"></span> Berjalan
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400 border border-rose-200/60">
                                    Belum Selesai
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Activities Body -->
                    <div class="space-y-1.5">
                        @if ($item->task_type === 'Rutinitas')
                            @php $activities = explode('|||', $item->activity_name ?? ''); @endphp
                            <div class="bg-slate-50 dark:bg-slate-800/40 rounded-xl p-3 space-y-2 border border-slate-100 dark:border-slate-800/60">
                                <div class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                    Daftar Tugas ({{ count($activities) }}):
                                </div>
                                <div class="space-y-1.5">
                                    @foreach($activities as $act)
                                        <div class="flex items-start gap-2 text-xs text-slate-700 dark:text-slate-300 leading-snug">
                                            <span class="material-symbols-outlined text-sm text-blue-600 dark:text-blue-400 shrink-0 mt-0.5">check_circle</span>
                                            <span>{{ trim($act) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div class="text-sm font-bold text-slate-900 dark:text-white leading-snug">
                                {{ $item->activity_name }}
                            </div>
                        @endif
                    </div>

                    <!-- Timings 3-Column Strip -->
                    <div class="grid grid-cols-3 gap-2 bg-slate-50/90 dark:bg-slate-800/50 p-2.5 rounded-xl text-center border border-slate-100 dark:border-slate-800/50">
                        <div>
                            <span class="text-[10px] font-semibold text-slate-400 block uppercase tracking-wide">Mulai</span>
                            <span class="text-xs font-bold text-slate-800 dark:text-slate-200">{{ $startFormatted }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] font-semibold text-slate-400 block uppercase tracking-wide">Selesai</span>
                            <span class="text-xs font-bold text-slate-800 dark:text-slate-200">{{ $finishFormatted }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] font-semibold text-slate-400 block uppercase tracking-wide">Durasi</span>
                            <span class="text-xs font-bold text-blue-600 dark:text-blue-400">{{ $durationText }}</span>
                        </div>
                    </div>

                    <!-- Catatan Laporan (If present) -->
                    @if (!empty($item->logged_note))
                        <div class="bg-amber-50/60 dark:bg-amber-950/20 border border-amber-200/60 dark:border-amber-900/40 rounded-xl p-2.5 text-xs text-amber-900 dark:text-amber-200 flex items-start gap-2">
                            <span class="material-symbols-outlined text-sm text-amber-600 shrink-0 mt-0.5">chat</span>
                            <div class="leading-relaxed">
                                <span class="font-bold block text-[10px] uppercase text-amber-700 dark:text-amber-300">Catatan Laporan:</span>
                                {{ $item->logged_note }}
                            </div>
                        </div>
                    @endif

                    <!-- Bukti Foto (If present) -->
                    @if ($item->attachments && $item->attachments->count() > 0)
                        <div class="space-y-1.5 pt-1">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">
                                Bukti Foto ({{ $item->attachments->count() }}):
                            </span>
                            <div class="flex gap-2 overflow-x-auto pb-1">
                                @foreach ($item->attachments as $att)
                                    @php $url = asset('storage/' . $att->path); @endphp
                                    <a href="{{ $url }}" target="_blank" class="shrink-0 group relative">
                                        <img src="{{ $url }}" 
                                             class="size-16 rounded-xl object-cover border border-slate-200 dark:border-slate-700 shadow-2xs group-hover:opacity-90 transition"
                                             alt="Bukti Foto">
                                        <div class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 rounded-xl flex items-center justify-center transition">
                                            <span class="material-symbols-outlined text-white text-sm">open_in_new</span>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                </div>
            @empty
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 text-center text-xs text-slate-400">
                    Tidak ada data pekerjaan untuk filter petugas dan tanggal ini.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Bottom Controls (Total & Pagination) -->
    <div class="flex flex-col sm:flex-row items-center justify-between gap-3 mt-4 text-xs text-slate-500 dark:text-slate-400">
        <div>
            <span class="font-bold text-slate-700 dark:text-slate-300">Total:</span> {{ $data->total() }} tugas
        </div>
        <div class="w-full sm:w-auto flex justify-center">
            {{ $data->links(data: ['scrollTo' => false]) }}
        </div>
    </div>
</div>
