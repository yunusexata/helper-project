@php
    $totalGroups = $groupedRoutines->count();
    $completedCount = $groupedRoutines->filter(fn($g) => $g->is_completed)->count();
    $percent = $totalGroups > 0 ? round(($completedCount / $totalGroups) * 100) : 0;

    $activeGroup = $groupedRoutines->get($selectedGroupName);
@endphp

<div x-data="{ activeTab: 'routine' }" 
     x-init="$watch('activeTab', tab => { if (tab === 'request') $nextTick(() => document.getElementById('request-activity')?.focus()) })" 
     class="p-4 max-w-lg mx-auto space-y-5">
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
                <span>Progres Rutinitas Hari Ini</span>
                <span>{{ $completedCount }} / {{ $totalGroups }} Grup ({{ $percent }}%)</span>
            </div>
            <div class="w-full bg-slate-100 dark:bg-slate-800 h-3 rounded-full overflow-hidden">
                <div class="bg-blue-600 h-full rounded-full transition-all duration-500" style="width: {{ $percent }}%"></div>
            </div>
        </div>
    </div>

    <!-- Segmented Navigation Tabs (Alpine.js Pure Frontend Toggle) -->
    <div class="grid grid-cols-2 p-1.5 bg-slate-100 dark:bg-slate-800/70 rounded-2xl border border-slate-200/70 dark:border-slate-800 gap-1.5">
        <button @click="activeTab = 'routine'" type="button"
            class="flex items-center justify-center gap-2 py-2.5 px-3 rounded-xl font-bold text-xs transition-all duration-200"
            :class="activeTab === 'routine' ? 'bg-white dark:bg-slate-900 text-blue-600 dark:text-blue-400 shadow-sm' : 'text-slate-500 hover:text-slate-700 dark:text-slate-400'">
            <span class="material-symbols-outlined text-lg">checklist</span>
            <span>Rutinitas</span>
            <span class="text-[10px] px-1.5 py-0.5 rounded-full font-extrabold"
                :class="activeTab === 'routine' ? 'bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400' : 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300'">
                {{ $completedCount }}/{{ $totalGroups }}
            </span>
        </button>

        <button @click="activeTab = 'request'; $nextTick(() => document.getElementById('request-activity')?.focus())" type="button"
            class="flex items-center justify-center gap-2 py-2.5 px-3 rounded-xl font-bold text-xs transition-all duration-200 relative"
            :class="activeTab === 'request' ? 'bg-white dark:bg-slate-900 text-blue-600 dark:text-blue-400 shadow-sm' : 'text-slate-500 hover:text-slate-700 dark:text-slate-400'">
            <span class="material-symbols-outlined text-lg">assignment_add</span>
            <span>Request</span>
            @if($activeRequest)
                <span class="size-2 rounded-full bg-blue-600 animate-pulse"></span>
            @elseif($todayRequests->isNotEmpty())
                <span class="text-[10px] px-1.5 py-0.5 rounded-full font-extrabold"
                    :class="activeTab === 'request' ? 'bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400' : 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300'">
                    {{ $todayRequests->count() }}
                </span>
            @endif
        </button>
    </div>

    <!-- ============================================================= -->
    <!-- TAB 1: RUTINITAS                                              -->
    <!-- ============================================================= -->
    <div x-show="activeTab === 'routine'" class="space-y-5">
        <!-- ROW 1: Vertical Scrollable Group List (Capped at ~3 rows max height) -->
        <div class="max-h-[168px] overflow-y-auto w-full p-2.5 space-y-3.5 snap-y [&::-webkit-scrollbar]:hidden" style="scrollbar-width: none; -ms-overflow-style: none;">
            @foreach($groupedRoutines as $groupName => $group)
                @php
                    $isActive = $selectedGroupName === $groupName;
                    
                    if ($group->is_completed) {
                        $styleClass = 'bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 border-l-4 border-l-emerald-500 text-slate-800 dark:text-slate-200';
                    } else {
                        $styleClass = 'bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 border-l-4 border-l-amber-500 text-slate-800 dark:text-slate-200';
                    }

                    if ($isActive) {
                        $styleClass .= ' scale-[1.02] shadow-md ring-2 ring-blue-500 border-blue-500 bg-blue-50/10 dark:bg-blue-950/10 z-10';
                    } else {
                        $styleClass .= ' hover:bg-slate-50 dark:hover:bg-slate-850/50';
                    }
                @endphp
                
                <button wire:click="selectGroup('{{ $groupName }}')" type="button"
                    class="w-full text-left p-3.5 rounded-2xl flex items-center justify-between transition-all duration-300 transform {{ $styleClass }}">
                    <div class="flex items-center gap-3.5 min-w-0">
                        @if($group->is_completed)
                            <span class="material-symbols-outlined text-emerald-500 flex-shrink-0 text-xl">check_circle</span>
                        @elseif($group->is_in_progress)
                            <span class="material-symbols-outlined text-blue-550 flex-shrink-0 text-xl animate-spin">sync</span>
                        @else
                            <span class="material-symbols-outlined text-amber-500 flex-shrink-0 text-xl">hourglass_empty</span>
                        @endif

                        <div class="min-w-0">
                            <span class="text-xs font-extrabold block uppercase tracking-wider text-slate-900 dark:text-white">{{ $groupName }}</span>
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

                <!-- STATE 1: Completed Routine Tasks -->
                @if($activeGroup->is_completed)
                    <div class="bg-emerald-50/50 dark:bg-emerald-950/10 border border-emerald-100 dark:border-emerald-950/20 rounded-2xl p-5 text-center space-y-4">
                        <span class="material-symbols-outlined text-5xl text-emerald-500">check_circle</span>
                        <div>
                            <h4 class="font-bold text-emerald-800 dark:text-emerald-400 text-sm">Tugas Grup Selesai!</h4>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                                Mulai: {{ Carbon\Carbon::parse($activeGroup->start_at)->setTimezone('Asia/Jakarta')->format('H:i') }} | 
                                Selesai: {{ Carbon\Carbon::parse($activeGroup->finish_at)->setTimezone('Asia/Jakarta')->format('H:i') }}
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
                                            <img src="{{ asset('storage/' . $img->path) }}" class="object-cover w-full h-full" alt="Lampiran">
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if($completedCount < $totalGroups)
                            <button wire:click="selectNextIncomplete" type="button" class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-sm shadow-sm transition">
                                Lanjut ke Grup Berikutnya
                            </button>
                        @endif
                    </div>

                <!-- STATE 2: Pending or In-Progress Routine Tasks -->
                @else
                    <div class="space-y-5">
                        <!-- Top Action: Mulai Kerja -->
                        @if(!$activeGroup->is_in_progress)
                            <button wire:click="startTask({{ $activeGroup->first_activity_id }})" wire:loading.attr="disabled" wire:target="startTask" type="button"
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
                                <span>Sedang Dikerjakan... (Mulai pukul {{ Carbon\Carbon::parse($activeGroup->start_at)->setTimezone('Asia/Jakarta')->format('H:i') }})</span>
                            </div>
                        @endif

                        <!-- Details Section -->
                        @if($activeGroup->is_in_progress)
                            <div class="space-y-4 border-t border-slate-100 dark:border-slate-800 pt-4">
                                <div>
                                    <label for="routine-note" class="block text-xs font-bold text-slate-700 dark:text-slate-350 uppercase tracking-wider mb-2">Catatan Laporan (Opsional)</label>
                                    <textarea id="routine-note" wire:model="note" rows="3" placeholder="Tulis keterangan hasil pengerjaan atau kendala..." 
                                        class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white"></textarea>
                                    @error('note') <p class="text-xs text-red-500 mt-1 font-semibold">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <span class="block text-xs font-bold text-slate-700 dark:text-slate-350 uppercase tracking-wider mb-2">Ambil Bukti Foto (Opsional)</span>
                                    <label class="flex flex-col items-center justify-center border-2 border-dashed border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/30 rounded-2xl p-6 cursor-pointer hover:bg-slate-100/50 dark:hover:bg-slate-800/50 transition relative">
                                        <span class="material-symbols-outlined text-4xl text-blue-600 mb-2">photo_camera</span>
                                        <span class="text-sm font-semibold text-slate-700 dark:text-slate-350">Ambil Foto / Pilih Gambar</span>
                                        <span class="text-[10px] text-slate-450 dark:text-slate-500 mt-1">Buka kamera langsung atau pilih dari galeri</span>
                                        <input type="file" wire:model="attachments" accept="image/*" class="hidden" multiple>
                                    </label>
                                    @error('attachments') <p class="text-xs text-red-500 mt-1 font-semibold">{{ $message }}</p> @enderror
                                    @error('attachments.*') <p class="text-xs text-red-500 mt-1 font-semibold">{{ $message }}</p> @enderror

                                    <div wire:loading wire:target="attachments" class="text-xs text-blue-600 dark:text-blue-400 mt-2 flex items-center gap-1">
                                        <span class="material-symbols-outlined text-sm animate-spin">sync</span>
                                        <span>Sedang mengunggah foto...</span>
                                    </div>

                                    @if($attachments)
                                        <div class="grid grid-cols-4 gap-2.5 mt-4">
                                            @foreach($attachments as $tempFile)
                                                <div class="relative aspect-square rounded-xl overflow-hidden border border-slate-200">
                                                    <img src="{{ $tempFile->temporaryUrl() }}" class="object-cover w-full h-full" alt="Preview">
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <!-- Bottom Action Button -->
                        @if($activeGroup->is_in_progress)
                            <button wire:click="completeTask({{ $activeGroup->first_activity_id }}, false)" wire:loading.attr="disabled" wire:target="completeTask" type="button"
                                class="w-full flex items-center justify-center py-4 bg-blue-600 hover:bg-blue-700 text-white rounded-2xl font-extrabold text-lg shadow-md transition disabled:opacity-50">
                                <span class="flex items-center gap-2" wire:loading.remove wire:target="completeTask">
                                    Selesai Kerja ✓
                                </span>
                                <span class="flex items-center gap-2" wire:loading wire:target="completeTask">
                                    <span class="material-symbols-outlined text-2xl animate-spin">sync</span> Menyimpan...
                                </span>
                            </button>
                        @else
                            <div class="space-y-1.5 pt-2 border-t border-slate-100 dark:border-slate-800">
                                <button wire:click="completeTask({{ $activeGroup->first_activity_id }}, true)" wire:loading.attr="disabled" wire:target="completeTask" type="button"
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

    <!-- ============================================================= -->
    <!-- TAB 2: REQUEST (Ad-hoc Tasks)                                 -->
    <!-- ============================================================= -->
    <div x-show="activeTab === 'request'" x-cloak class="space-y-5">
        <!-- Active / In-Progress Request Card -->
        @if($activeRequest)
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border-2 border-blue-500 shadow-md space-y-5">
                <div class="border-b border-slate-100 dark:border-slate-800 pb-4">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold text-blue-600 bg-blue-50 dark:bg-blue-950/40 px-2.5 py-1 rounded-full uppercase">Pekerjaan Request Aktif</span>
                        <div class="flex items-center gap-1.5 text-xs font-bold text-blue-600">
                            <span class="material-symbols-outlined text-base animate-spin">sync</span>
                            <span>Sedang Dikerjakan</span>
                        </div>
                    </div>
                    
                    <h2 class="text-lg font-extrabold text-slate-900 dark:text-white mt-3">{{ $activeRequest->activity_name }}</h2>
                    
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                        Mulai: {{ Carbon\Carbon::parse($activeRequestHistory->start_at)->setTimezone('Asia/Jakarta')->format('H:i') }} WIB
                    </p>
                </div>

                <!-- Input Note & Attachments for finishing request -->
                <div class="space-y-4">
                    <div>
                        <label for="request-note" class="block text-xs font-bold text-slate-700 dark:text-slate-350 uppercase tracking-wider mb-2">Catatan Laporan (Opsional)</label>
                        <textarea id="request-note" wire:model="requestNote" rows="3" placeholder="Tuliskan hasil pengerjaan request atau kendala..." 
                            class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white"></textarea>
                        @error('requestNote') <p class="text-xs text-red-500 mt-1 font-semibold">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <span class="block text-xs font-bold text-slate-700 dark:text-slate-350 uppercase tracking-wider mb-2">Ambil Bukti Foto (Opsional)</span>
                        <label class="flex flex-col items-center justify-center border-2 border-dashed border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/30 rounded-2xl p-6 cursor-pointer hover:bg-slate-100/50 dark:hover:bg-slate-800/50 transition relative">
                            <span class="material-symbols-outlined text-4xl text-blue-600 mb-2">photo_camera</span>
                            <span class="text-sm font-semibold text-slate-700 dark:text-slate-350">Ambil Foto / Pilih Gambar</span>
                            <span class="text-[10px] text-slate-450 dark:text-slate-500 mt-1">Buka kamera langsung atau pilih dari galeri</span>
                            <input type="file" wire:model="requestAttachments" accept="image/*" class="hidden" multiple>
                        </label>
                        @error('requestAttachments') <p class="text-xs text-red-500 mt-1 font-semibold">{{ $message }}</p> @enderror
                        @error('requestAttachments.*') <p class="text-xs text-red-500 mt-1 font-semibold">{{ $message }}</p> @enderror

                        <div wire:loading wire:target="requestAttachments" class="text-xs text-blue-600 dark:text-blue-400 mt-2 flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm animate-spin">sync</span>
                            <span>Sedang mengunggah foto...</span>
                        </div>

                        @if($requestAttachments)
                            <div class="grid grid-cols-4 gap-2.5 mt-4">
                                @foreach($requestAttachments as $tempFile)
                                    <div class="relative aspect-square rounded-xl overflow-hidden border border-slate-200">
                                        <img src="{{ $tempFile->temporaryUrl() }}" class="object-cover w-full h-full" alt="Preview Request">
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Complete Button -->
                <div class="space-y-2 pt-2">
                    <button wire:click="completeRequest" wire:loading.attr="disabled" wire:target="completeRequest" type="button"
                        class="w-full flex items-center justify-center py-4 bg-blue-600 hover:bg-blue-700 text-white rounded-2xl font-extrabold text-lg shadow-md transition disabled:opacity-50">
                        <span class="flex items-center gap-2" wire:loading.remove wire:target="completeRequest">
                            Selesai Kerja ✓
                        </span>
                        <span class="flex items-center gap-2" wire:loading wire:target="completeRequest">
                            <span class="material-symbols-outlined text-2xl animate-spin">sync</span> Menyimpan...
                        </span>
                    </button>

                    <button type="button"
                        @click="
                            Swal.fire({
                                title: 'Batalkan Request?',
                                text: 'Apakah Anda yakin ingin membatalkan pekerjaan request ini?',
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: '#e11d48',
                                cancelButtonColor: '#64748b',
                                confirmButtonText: 'Ya, Batalkan',
                                cancelButtonText: 'Kembali'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    $wire.cancelRequest();
                                }
                            });
                        "
                        class="w-full py-2.5 text-xs text-slate-400 hover:text-red-500 font-semibold transition text-center">
                        Batalkan Request Ini
                    </button>
                </div>
            </div>

        <!-- Form to Start a New Request (When No Request is in-progress) -->
        @else
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm space-y-5">
                <div class="border-b border-slate-100 dark:border-slate-800 pb-4">
                    <span class="text-[10px] font-bold text-blue-600 bg-blue-50 dark:bg-blue-950/30 px-2.5 py-1 rounded-full uppercase">Pekerjaan Di Luar Rutinitas</span>
                    <h2 class="text-lg font-extrabold text-slate-900 dark:text-white mt-2">Buat Pekerjaan Request</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Catat dan mulai tugas ad-hoc atau permintaan yang tidak ada di daftar rutinitas harian.</p>
                </div>

                <!-- Request Activity Input -->
                <div class="space-y-2">
                    <label for="request-activity" class="block text-xs font-bold text-slate-700 dark:text-slate-350 uppercase tracking-wider">Nama Pekerjaan / Request</label>
                    <textarea id="request-activity" wire:model="requestActivityName" rows="3" 
                        placeholder="Contoh: Angkat galon air ke lantai 3, perbaiki kran wastafel pantri, belanja perlengkapan mendadak..." 
                        class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white"></textarea>
                    @error('requestActivityName') <p class="text-xs text-red-500 mt-1 font-semibold">{{ $message }}</p> @enderror
                </div>

                <!-- Mulai Kerja Button -->
                <button wire:click="startRequest" wire:loading.attr="disabled" wire:target="startRequest" type="button"
                    class="w-full flex items-center justify-center gap-2 py-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl font-extrabold text-lg shadow-md transition disabled:opacity-50">
                    <span class="flex items-center gap-2" wire:loading.remove wire:target="startRequest">
                        <span class="material-symbols-outlined text-2xl">play_circle</span> Mulai Kerja
                    </span>
                    <span class="flex items-center gap-2" wire:loading wire:target="startRequest">
                        <span class="material-symbols-outlined text-2xl animate-spin">sync</span> Memproses...
                    </span>
                </button>
            </div>
        @endif

        <!-- Today's Request History -->
        @if($todayRequests->isNotEmpty())
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-blue-600 text-lg">history</span>
                        <h3 class="text-xs font-extrabold text-slate-900 dark:text-white uppercase tracking-wider">Riwayat Request Hari Ini</h3>
                    </div>
                    <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded-full">
                        {{ $todayRequests->count() }} Request
                    </span>
                </div>

                <div class="space-y-3">
                    @foreach($todayRequests as $req)
                        @php
                            $hist = $req->dailyHistories->where('employee_whitelists_id', $whitelist?->id)->first();
                            $isDone = !empty($hist) && !empty($hist->finish_at);
                        @endphp
                        <div class="p-3.5 rounded-2xl border border-slate-200/80 dark:border-slate-800 {{ $isDone ? 'border-l-4 border-l-emerald-500 bg-slate-50/50 dark:bg-slate-850/40' : 'border-l-4 border-l-blue-500 bg-blue-50/20 dark:bg-blue-950/10' }} space-y-2">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <h4 class="text-xs font-bold text-slate-900 dark:text-white">{{ $req->activity_name }}</h4>
                                    @if($hist)
                                        <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">
                                            Mulai: {{ Carbon\Carbon::parse($hist->start_at)->setTimezone('Asia/Jakarta')->format('H:i') }}
                                            @if($hist->finish_at)
                                                | Selesai: {{ Carbon\Carbon::parse($hist->finish_at)->setTimezone('Asia/Jakarta')->format('H:i') }}
                                            @else
                                                | <span class="text-blue-600 font-bold">Sedang Berjalan</span>
                                            @endif
                                        </p>
                                    @endif
                                </div>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $isDone ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-400' : 'bg-blue-100 text-blue-800 dark:bg-blue-950/40 dark:text-blue-400' }}">
                                    {{ $isDone ? 'Selesai' : 'Berjalan' }}
                                </span>
                            </div>

                            @if($hist && $hist->note)
                                <p class="text-[11px] text-slate-600 dark:text-slate-400 bg-white dark:bg-slate-800/80 p-2.5 rounded-xl border border-slate-100 dark:border-slate-750 italic">
                                    "{{ $hist->note }}"
                                </p>
                            @endif

                            @if($hist && $hist->attachments && $hist->attachments->isNotEmpty())
                                <div class="flex gap-2 overflow-x-auto pt-1">
                                    @foreach($hist->attachments as $att)
                                        <a href="{{ asset('storage/' . $att->path) }}" target="_blank" class="size-12 rounded-lg border border-slate-200 overflow-hidden flex-shrink-0 bg-slate-100">
                                            <img src="{{ asset('storage/' . $att->path) }}" class="object-cover w-full h-full" alt="Lampiran Request">
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
