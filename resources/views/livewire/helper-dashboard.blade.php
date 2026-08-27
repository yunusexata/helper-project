@php
    $totalTasks = $routines->count();
    $completedCount = $routines->filter(fn($r) => $r->is_completed)->count();
    $percent = $totalTasks > 0 ? round(($completedCount / $totalTasks) * 100) : 0;

    $selectedIndex = $routines->search(fn($r) => $r->id === $selectedRoutineId);
    $prevRoutine = $selectedIndex > 0 ? $routines->get($selectedIndex - 1) : null;
    $activeRoutine = $routines->first(fn($r) => $r->id === $selectedRoutineId);
    $nextRoutine = $selectedIndex !== false && $selectedIndex < $routines->count() - 1 ? $routines->get($selectedIndex + 1) : null;
@endphp

<div class="p-4 max-w-md mx-auto space-y-6">
    <!-- Header & Progress -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider">{{ now()->locale('id')->isoFormat('dddd, D MMMM Y') }}</p>
                <h1 class="text-lg font-bold text-slate-900 dark:text-white mt-0.5">{{ auth()->user()->name }}</h1>
            </div>
            <span class="text-xs font-bold text-blue-600 bg-blue-50 dark:bg-blue-950/30 dark:text-blue-400 px-2.5 py-1 rounded-full">Helper</span>
        </div>

        <!-- Progress Bar -->
        <div class="space-y-1.5">
            <div class="flex justify-between text-xs font-bold text-slate-600 dark:text-slate-350">
                <span>Progres Kerja Hari Ini</span>
                <span>{{ $completedCount }} / {{ $totalTasks }} Selesai ({{ $percent }}%)</span>
            </div>
            <div class="w-full bg-slate-100 dark:bg-slate-800 h-3 rounded-full overflow-hidden">
                <div class="bg-blue-600 h-full rounded-full transition-all duration-500" style="width: {{ $percent }}%"></div>
            </div>
        </div>
    </div>

    <!-- ROW 1: 3-Column Focus Slider -->
    <div class="grid grid-cols-3 gap-3">
        <!-- Column 1: Selesai (Sebelumnya) -->
        <div class="flex flex-col">
            <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase text-center mb-1">Sebelumnya</span>
            @if($prevRoutine)
                <button wire:click="selectRoutine({{ $prevRoutine->id }})" 
                    class="h-24 rounded-2xl border text-left p-3 flex flex-col justify-between transition bg-emerald-50/60 dark:bg-emerald-950/10 border-emerald-200 dark:border-emerald-900/30">
                    <span class="w-6 h-6 rounded-full bg-emerald-100 dark:bg-emerald-900/50 flex items-center justify-center text-emerald-600 dark:text-emerald-400 font-bold text-xs">
                        {{ $prevRoutine->order }}
                    </span>
                    <span class="text-xs font-bold text-slate-800 dark:text-slate-200 truncate w-full mt-2">{{ $prevRoutine->activity_name }}</span>
                    <span class="text-[9px] text-emerald-700 dark:text-emerald-400 flex items-center gap-1 font-semibold">
                        <span class="material-symbols-outlined text-xs">check_circle</span> Selesai
                    </span>
                </button>
            @else
                <div class="h-24 rounded-2xl border border-dashed border-slate-200 dark:border-slate-800 flex items-center justify-center p-3 text-[10px] text-slate-350 dark:text-slate-650 text-center">
                    Mulai
                </div>
            @endif
        </div>

        <!-- Column 2: Aktif (Harus Dikerjakan) -->
        <div class="flex flex-col">
            <span class="text-[10px] font-bold text-blue-600 uppercase text-center mb-1">Pilihan Aktif</span>
            @if($activeRoutine)
                <div class="h-24 rounded-2xl border-2 text-left p-3 flex flex-col justify-between bg-blue-50/50 dark:bg-blue-950/20 border-blue-600 shadow-md">
                    <span class="w-6 h-6 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-xs">
                        {{ $activeRoutine->order }}
                    </span>
                    <span class="text-xs font-bold text-slate-900 dark:text-white truncate w-full mt-2">{{ $activeRoutine->activity_name }}</span>
                    <span class="text-[9px] font-bold text-blue-600 dark:text-blue-400 flex items-center gap-1">
                        @if($activeRoutine->is_completed)
                            <span class="material-symbols-outlined text-xs">check_circle</span> Selesai
                        @elseif($activeRoutine->is_in_progress)
                            <span class="material-symbols-outlined text-xs animate-spin">sync</span> Jalan...
                        @else
                            <span class="material-symbols-outlined text-xs">hourglass_empty</span> Antri...
                        @endif
                    </span>
                </div>
            @endif
        </div>

        <!-- Column 3: Nanti (Tugas Berikutnya) -->
        <div class="flex flex-col">
            <span class="text-[10px] font-bold text-slate-450 dark:text-slate-500 uppercase text-center mb-1">Berikutnya</span>
            @if($nextRoutine)
                <button wire:click="selectRoutine({{ $nextRoutine->id }})" 
                    class="h-24 rounded-2xl border border-slate-200 dark:border-slate-800 text-left p-3 flex flex-col justify-between transition bg-slate-50 dark:bg-slate-900 hover:bg-slate-100 dark:hover:bg-slate-800/50">
                    <span class="w-6 h-6 rounded-full bg-slate-200 dark:bg-slate-800 text-slate-500 dark:text-slate-450 flex items-center justify-center font-bold text-xs">
                        {{ $nextRoutine->order }}
                    </span>
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-350 truncate w-full mt-2">{{ $nextRoutine->activity_name }}</span>
                    <span class="text-[9px] text-slate-450 dark:text-slate-500 flex items-center gap-1 font-semibold">
                        <span class="material-symbols-outlined text-xs">arrow_forward</span> Nanti
                    </span>
                </button>
            @else
                <div class="h-24 rounded-2xl border border-dashed border-slate-200 dark:border-slate-800 flex items-center justify-center p-3 text-[10px] text-slate-350 dark:text-slate-650 text-center">
                    Selesai
                </div>
            @endif
        </div>
    </div>

    <!-- ROW 2: Selected Task Action Card -->
    @if($activeRoutine)
        <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm space-y-6">
            <!-- Title & Info -->
            <div class="border-b border-slate-100 dark:border-slate-800 pb-4">
                <span class="text-[10px] font-bold text-blue-600 bg-blue-50 dark:bg-blue-950/30 px-2.5 py-1 rounded-full uppercase">Tugas #{{ $activeRoutine->order }}</span>
                <h2 class="text-xl font-extrabold text-slate-900 dark:text-white mt-3">{{ $activeRoutine->activity_name }}</h2>
                @if($activeRoutine->note)
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-2 bg-slate-50 dark:bg-slate-800/50 p-3 rounded-xl border border-slate-100 dark:border-slate-800">
                        <strong>Petunjuk:</strong> {{ $activeRoutine->note }}
                    </p>
                @endif
            </div>

            <!-- STATE 1: Completed Tasks -->
            @if($activeRoutine->is_completed)
                <div class="bg-emerald-50/50 dark:bg-emerald-950/10 border border-emerald-100 dark:border-emerald-950/20 rounded-2xl p-5 text-center space-y-4">
                    <span class="material-symbols-outlined text-5xl text-emerald-500">check_circle</span>
                    <div>
                        <h4 class="font-bold text-emerald-800 dark:text-emerald-400">Tugas Sudah Selesai!</h4>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                            Mulai: {{ Carbon\Carbon::parse($activeRoutine->start_at)->format('H:i') }} | 
                            Selesai: {{ Carbon\Carbon::parse($activeRoutine->finish_at)->format('H:i') }}
                        </p>
                    </div>

                    @if($activeRoutine->logged_note)
                        <div class="text-left text-xs bg-white dark:bg-slate-900 p-3 rounded-xl border border-emerald-100 dark:border-emerald-950/30">
                            <strong>Catatan Laporan:</strong><br>
                            <span class="text-slate-650 dark:text-slate-350">"{{ $activeRoutine->logged_note }}"</span>
                        </div>
                    @endif

                    @if($activeRoutine->attachments->isNotEmpty())
                        <div class="text-left space-y-1.5">
                            <span class="text-xs font-bold text-slate-600 dark:text-slate-300">Lampiran Foto:</span>
                            <div class="grid grid-cols-3 gap-2">
                                @foreach($activeRoutine->attachments as $img)
                                    <a href="{{ asset('storage/' . $img->path) }}" target="_blank" class="aspect-square rounded-lg border overflow-hidden bg-slate-100">
                                        <img src="{{ asset('storage/' . $img->path) }}" class="object-cover w-full h-full">
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($completedCount < $totalTasks)
                        <button wire:click="selectNextIncomplete" class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-sm shadow-sm transition">
                            Lanjut ke Tugas Berikutnya
                        </button>
                    @endif
                </div>

            <!-- STATE 2: Pending or In-Progress Tasks -->
            @else
                <div class="space-y-6">
                    <!-- Top Action: Mulai Kerja (Only shown if pending/not started) -->
                    @if(!$activeRoutine->is_in_progress)
                        <button wire:click="startTask({{ $activeRoutine->id }})" class="w-full flex items-center justify-center gap-3 py-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl font-bold text-lg shadow-md transition">
                            <span class="material-symbols-outlined text-2xl">play_circle</span> Mulai Kerja
                        </button>
                    @else
                        <div class="flex items-center gap-2 text-xs font-bold text-blue-600 bg-blue-50 dark:bg-blue-950/30 px-3.5 py-2.5 rounded-xl border border-blue-150 dark:border-blue-900/30">
                            <span class="material-symbols-outlined text-lg animate-spin">sync</span>
                            <span>Sedang Dikerjakan... (Mulai pukul {{ Carbon\Carbon::parse($activeRoutine->start_at)->format('H:i') }})</span>
                        </div>
                    @endif

                    <!-- Details Section (Progressive disclosure: hidden if pending, shown if in_progress) -->
                    @if($activeRoutine->is_in_progress)
                        <div class="space-y-5 border-t border-slate-100 dark:border-slate-800 pt-5">
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
                    @if($activeRoutine->is_in_progress)
                        <!-- Selesai button for in-progress tasks -->
                        <button wire:click="completeTask({{ $activeRoutine->id }}, false)" class="w-full py-4 bg-blue-600 hover:bg-blue-700 text-white rounded-2xl font-bold text-lg shadow-md transition">
                            Selesai Kerja
                        </button>
                    @else
                        <!-- Direct Complete button for pending tasks -->
                        <button wire:click="completeTask({{ $activeRoutine->id }}, true)" class="w-full py-4 bg-blue-600 hover:bg-blue-700 text-white rounded-2xl font-bold text-lg shadow-md transition">
                            Selesai Kerja (Langsung Selesai)
                        </button>
                    @endif
                </div>
            @endif
        </div>
    @endif
</div>
