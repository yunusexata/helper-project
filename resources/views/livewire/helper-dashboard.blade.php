@php
    $totalGroups = $groupedRoutines->count();
    $completedCount = $groupedRoutines->filter(fn($g) => $g->is_completed)->count();
    $percent = $totalGroups > 0 ? round(($completedCount / $totalGroups) * 100) : 0;

    $activeGroup = $groupedRoutines->get($selectedGroupName);
@endphp

<div class="p-4 max-w-lg mx-auto space-y-5">
    <!-- Header & Progress Card -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm space-y-3">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-base font-extrabold text-slate-900 dark:text-white">{{ auth()->user()->name }}</h1>
                <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mt-0.5">{{ now()->locale('id')->isoFormat('dddd, D MMMM Y') }}</p>
            </div>
            <span class="text-[10px] font-bold text-blue-600 bg-blue-50 dark:bg-blue-950/30 dark:text-blue-400 px-2.5 py-1 rounded-full">Helper</span>
        </div>

        <!-- Progress Bar -->
        <div class="space-y-1">
            <div class="flex justify-between text-xs font-bold text-slate-600 dark:text-slate-350">
                <span>Progres Grup Hari Ini</span>
                <span>{{ $completedCount }} / {{ $totalGroups }} Grup Selesai ({{ $percent }}%)</span>
            </div>
            <div class="w-full bg-slate-100 dark:bg-slate-800 h-3 rounded-full overflow-hidden">
                <div class="bg-blue-600 h-full rounded-full transition-all duration-500" style="width: {{ $percent }}%"></div>
            </div>
        </div>
    </div>

    <!-- ROW 1: Vertical Scrollable Group List (Capped at ~3 rows max height) -->
    <div class="max-h-[168px] overflow-y-auto w-full p-2.5 space-y-3.5 snap-y [&::-webkit-scrollbar]:hidden" style="scrollbar-width: none; -ms-overflow-style: none;">
        @foreach($groupedRoutines as $groupName => $group)
            @php
                $isActive = $selectedGroupName === $groupName;
                
                // Card-based base class with left status indicator border strip
                if ($group->is_completed) {
                    $styleClass = 'bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 border-l-4 border-l-emerald-500 text-slate-800 dark:text-slate-200';
                } else {
                    $styleClass = 'bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 border-l-4 border-l-amber-500 text-slate-800 dark:text-slate-200';
                }

                // Active state scaling and ring outline
                if ($isActive) {
                    $styleClass .= ' scale-[1.02] shadow-md ring-2 ring-blue-500 border-blue-500 bg-blue-50/10 dark:bg-blue-950/10 z-10';
                } else {
                    $styleClass .= ' hover:bg-slate-50 dark:hover:bg-slate-850/50';
                }
            @endphp
            
            <button wire:click="selectGroup('{{ $groupName }}')" 
                class="w-full text-left p-3.5 rounded-2xl flex items-center justify-between transition-all duration-300 transform {{ $styleClass }}">
                <div class="flex items-center gap-3.5 min-w-0">
                    <!-- Icon Status Indicator -->
                    @if($group->is_completed)
                        <span class="material-symbols-outlined text-emerald-500 flex-shrink-0 text-xl">check_circle</span>
                    @elseif($group->is_in_progress)
                        <span class="material-symbols-outlined text-blue-550 flex-shrink-0 text-xl animate-spin">sync</span>
                    @else
                        <span class="material-symbols-outlined text-amber-500 flex-shrink-0 text-xl">hourglass_empty</span>
                    @endif

                    <div class="min-w-0">
                        <span class="text-xs font-extrabold block uppercase tracking-wider text-slate-900 dark:text-white">{{ $groupName }}</span>
                        <!-- Display first activity as group preview -->
                        <span class="text-[11px] font-semibold truncate block max-w-[200px] text-slate-500 dark:text-slate-400 mt-0.5">{{ $group->activities->first()->activity_name }}</span>
                    </div>
                </div>
                
                <span class="text-[10px] font-extrabold px-2.5 py-1 rounded-full {{ $group->is_completed ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-400' : 'bg-amber-50 text-amber-700 dark:bg-amber-950/30 dark:text-amber-400' }} border border-slate-200/50">
                    {{ $group->activities->count() }} Tugas
                </span>
            </button>
        @endforeach
    </div>

    <!-- ROW 2: Selected Group Action Card -->
    @if($activeGroup)
        <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm space-y-5">
            <!-- Title & Info -->
            <div class="border-b border-slate-100 dark:border-slate-800 pb-4">
                <span class="text-[10px] font-bold text-blue-600 bg-blue-50 dark:bg-blue-950/30 px-2.5 py-1 rounded-full uppercase">Kategori {{ $activeGroup->name }}</span>
                <h2 class="text-lg font-extrabold text-slate-900 dark:text-white mt-3">{{ $activeGroup->activities->first()->activity_name }} & Lainnya</h2>
                
                <!-- Sub-Task Bulleted Checklist -->
                <div class="space-y-2.5 mt-4 bg-slate-50 dark:bg-slate-850/50 p-4 rounded-2xl border border-slate-100 dark:border-slate-800/80">
                    <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider block">Daftar Aktivitas Rutinitas:</span>
                    <ul class="space-y-2">
                        @foreach($activeGroup->activities as $activity)
                            <li class="flex items-start gap-2.5 text-sm">
                                <span class="material-symbols-outlined text-blue-600 dark:text-blue-400 text-base mt-0.5 select-none">task_alt</span>
                                <span class="text-slate-800 dark:text-slate-200 leading-tight font-semibold">{{ $activity->activity_name }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <!-- STATE 1: Completed Tasks -->
            @if($activeGroup->is_completed)
                <div class="bg-emerald-50/50 dark:bg-emerald-950/10 border border-emerald-100 dark:border-emerald-950/20 rounded-2xl p-5 text-center space-y-4">
                    <span class="material-symbols-outlined text-5xl text-emerald-500">check_circle</span>
                    <div>
                        <h4 class="font-bold text-emerald-800 dark:text-emerald-400 text-sm">Tugas Grup Selesai!</h4>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                            Mulai: {{ Carbon\Carbon::parse($activeGroup->start_at)->format('H:i') }} | 
                            Selesai: {{ Carbon\Carbon::parse($activeGroup->finish_at)->format('H:i') }}
                        </p>
                    </div>

                    @if($activeGroup->logged_note)
                        <div class="text-left text-xs bg-white dark:bg-slate-900 p-3 rounded-xl border border-emerald-100 dark:border-emerald-950/30">
                            <strong>Catatan Laporan:</strong><br>
                            <span class="text-slate-650 dark:text-slate-350">"{{ $activeGroup->logged_note }}"</span>
                        </div>
                    @endif

                    @if($activeGroup->attachments->isNotEmpty())
                        <div class="text-left space-y-1.5">
                            <span class="text-xs font-bold text-slate-600 dark:text-slate-300">Lampiran Foto:</span>
                            <div class="grid grid-cols-3 gap-2">
                                @foreach($activeGroup->attachments as $img)
                                    <a href="{{ asset('storage/' . $img->path) }}" target="_blank" class="aspect-square rounded-lg border overflow-hidden bg-slate-100">
                                        <img src="{{ asset('storage/' . $img->path) }}" class="object-cover w-full h-full">
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($completedCount < $totalGroups)
                        <button wire:click="selectNextIncomplete" class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-sm shadow-sm transition">
                            Lanjut ke Grup Berikutnya
                        </button>
                    @endif
                </div>

            <!-- STATE 2: Pending or In-Progress Tasks -->
            @else
                <div class="space-y-5">
                    <!-- Top Action: Mulai Kerja (Only shown if pending/not started) -->
                    @if(!$activeGroup->is_in_progress)
                        <button wire:click="startTask({{ $activeGroup->first_activity_id }})" wire:loading.attr="disabled" wire:target="startTask"
                            class="w-full flex items-center justify-center gap-2 py-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl font-extrabold text-lg shadow-md transition disabled:opacity-50">
                            <span class="flex items-center gap-2" wire:loading.remove wire:target="startTask">
                                <span class="material-symbols-outlined text-2xl">play_circle</span> Mulai Kerja
                            </span>
                            <span class="flex items-center gap-2" wire:loading wire:target="startTask">
                                <span class="material-symbols-outlined text-2xl animate-spin">sync</span> Memproses...
                            </span>
                        </button>
                    @else
                        <div class="flex items-center gap-2 text-xs font-bold text-blue-600 bg-blue-50 dark:bg-blue-950/30 px-3.5 py-2.5 rounded-xl border border-blue-150 dark:border-blue-900/30">
                            <span class="material-symbols-outlined text-base animate-spin">sync</span>
                            <span>Sedang Dikerjakan... (Mulai pukul {{ Carbon\Carbon::parse($activeGroup->start_at)->format('H:i') }})</span>
                        </div>
                    @endif

                    <!-- Details Section (Progressive disclosure: hidden if pending, shown if in_progress) -->
                    @if($activeGroup->is_in_progress)
                        <div class="space-y-4 border-t border-slate-100 dark:border-slate-800 pt-4">
                            <!-- Notes input -->
                            <div>
                                <label for="task-note" class="block text-xs font-bold text-slate-700 dark:text-slate-350 uppercase tracking-wider mb-2">Catatan Laporan (Opsional)</label>
                                <textarea id="task-note" wire:model="note" rows="3" placeholder="Tulis keterangan hasil pengerjaan atau kendala..." 
                                    class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white"></textarea>
                                <flux:error name="note" />
                            </div>

                            <!-- Camera Photo picker container -->
                            <div>
                                <span class="block text-xs font-bold text-slate-700 dark:text-slate-350 uppercase tracking-wider mb-2">Ambil Bukti Foto (Opsional)</span>
                                <label class="flex flex-col items-center justify-center border-2 border-dashed border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/30 rounded-2xl p-6 cursor-pointer hover:bg-slate-100/50 dark:hover:bg-slate-800/50 transition relative">
                                    <span class="material-symbols-outlined text-4xl text-blue-600 mb-2">photo_camera</span>
                                    <span class="text-sm font-semibold text-slate-700 dark:text-slate-350">Ambil Foto / Pilih Gambar</span>
                                    <span class="text-[10px] text-slate-450 dark:text-slate-500 mt-1">Buka kamera langsung atau pilih dari galeri</span>
                                    <input type="file" wire:model="attachments" accept="image/*" class="hidden" multiple>
                                </label>
                                <flux:error name="attachments" />
                                <flux:error name="attachments.*" />

                                <!-- Photo Uploading Loader -->
                                <div wire:loading wire:target="attachments" class="text-xs text-blue-600 dark:text-blue-400 mt-2 flex items-center gap-1">
                                    <span class="material-symbols-outlined text-sm animate-spin">sync</span>
                                    <span>Sedang mengunggah foto...</span>
                                </div>

                                <!-- Thumbnails image preview grid -->
                                @if($attachments)
                                    <div class="grid grid-cols-4 gap-2.5 mt-4">
                                        @foreach($attachments as $tempFile)
                                            <div class="relative aspect-square rounded-xl overflow-hidden border border-slate-200">
                                                <img src="{{ $tempFile->temporaryUrl() }}" class="object-cover w-full h-full">
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Bottom Action Button -->
                    @if($activeGroup->is_in_progress)
                        <!-- Selesai button for in-progress tasks -->
                        <button wire:click="completeTask({{ $activeGroup->first_activity_id }}, false)" wire:loading.attr="disabled" wire:target="completeTask"
                            class="w-full flex items-center justify-center py-4 bg-blue-600 hover:bg-blue-700 text-white rounded-2xl font-extrabold text-lg shadow-md transition disabled:opacity-50">
                            <span class="flex items-center gap-2" wire:loading.remove wire:target="completeTask">
                                Selesai Kerja ✓
                            </span>
                            <span class="flex items-center gap-2" wire:loading wire:target="completeTask">
                                <span class="material-symbols-outlined text-2xl animate-spin">sync</span> Menyimpan...
                            </span>
                        </button>
                    @else
                        <!-- Direct Complete button for pending tasks (styled as secondary/slate button) -->
                        <div class="space-y-1.5 pt-2 border-t border-slate-100 dark:border-slate-800">
                            <button wire:click="completeTask({{ $activeGroup->first_activity_id }}, true)" wire:loading.attr="disabled" wire:target="completeTask"
                                class="w-full flex items-center justify-center py-3.5 bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-slate-800 dark:hover:bg-slate-750 dark:text-slate-200 rounded-2xl font-bold text-sm shadow-sm transition disabled:opacity-50">
                                <span class="flex items-center gap-2" wire:loading.remove wire:target="completeTask">
                                    Langsung Selesai ✓
                                </span>
                                <span class="flex items-center gap-2" wire:loading wire:target="completeTask">
                                    <span class="material-symbols-outlined text-sm animate-spin">sync</span> Menyimpan...
                                </span>
                            </button>
                            <p class="text-[10px] text-slate-400 dark:text-slate-500 text-center font-medium">Bypass: Selesaikan tugas langsung tanpa menekan Mulai</p>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    @endif
</div>
