<div class="p-6 max-w-5xl mx-auto space-y-6">
    <!-- Top Header Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Rencana Kerja Harian (Daily Routine)</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Kelola daftar tugas dan aktivitas rutin harian berdasarkan kelompok kerja (Task Group).</p>
        </div>
        
        <div class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2">
                <label for="day-select" class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Hari:</label>
                <select id="day-select" wire:model.live="day" 
                    class="rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-sm font-semibold text-slate-800 shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:focus:border-blue-500">
                    <option value="senin">Senin</option>
                    <option value="selasa">Selasa</option>
                    <option value="rabu">Rabu</option>
                    <option value="kamis">Kamis</option>
                    <option value="jumat">Jumat</option>
                    <option value="sabtu">Sabtu</option>
                    <option value="minggu">Minggu</option>
                </select>
            </div>
            
            <flux:button wire:click="openAddModal" variant="primary" icon="plus" class="shadow-sm font-semibold">
                Tambah Rutinitas
            </flux:button>
        </div>
    </div>

    <!-- Quick Stats Bar -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm flex items-center gap-3.5">
            <div class="size-10 rounded-xl bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                <span class="material-symbols-outlined text-xl">folder_managed</span>
            </div>
            <div>
                <div class="text-xl font-extrabold text-slate-900 dark:text-white">{{ $totalGroups }} Grup</div>
                <div class="text-xs text-slate-500 dark:text-slate-400">Total Kelompok Tugas Hari {{ ucfirst($day) }}</div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm flex items-center gap-3.5">
            <div class="size-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                <span class="material-symbols-outlined text-xl">checklist</span>
            </div>
            <div>
                <div class="text-xl font-extrabold text-slate-900 dark:text-white">{{ $totalActivities }} Aktivitas</div>
                <div class="text-xs text-slate-500 dark:text-slate-400">Total Sub-Aktivitas Kerja</div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm flex items-center gap-3.5">
            <div class="size-10 rounded-xl bg-purple-50 dark:bg-purple-950/40 text-purple-600 dark:text-purple-400 flex items-center justify-center">
                <span class="material-symbols-outlined text-xl">calendar_today</span>
            </div>
            <div>
                <div class="text-xl font-extrabold text-slate-900 dark:text-white capitalize">{{ $day }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400">Jadwal Aktif Terpilih</div>
            </div>
        </div>
    </div>

    <!-- Grouped Cards Container -->
    @if($groupedRoutines->isEmpty())
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-12 text-center space-y-4 shadow-sm">
            <div class="size-14 mx-auto rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400">
                <span class="material-symbols-outlined text-3xl">playlist_add</span>
            </div>
            <div>
                <h3 class="font-bold text-slate-800 dark:text-slate-200 text-base">Belum Ada Rutinitas untuk Hari {{ ucfirst($day) }}</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto">Mulai tambahkan kelompok tugas pertama beserta daftar aktivitas rutinnya.</p>
            </div>
            <flux:button wire:click="openAddModal" variant="primary" icon="plus" size="sm">
                Tambah Kelompok Pertama
            </flux:button>
        </div>
    @else
        <div class="space-y-5">
            @foreach($groupedRoutines as $groupName => $activities)
                <div wire:key="group-card-{{ $day }}-{{ Str::slug($groupName) }}" 
                    class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden transition hover:border-slate-300 dark:hover:border-slate-700">
                    
                    <!-- Card Group Header -->
                    <div class="bg-slate-50/80 dark:bg-slate-800/50 px-5 py-3.5 border-b border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex items-center justify-center size-8 rounded-xl bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400 font-extrabold text-xs">
                                <span class="material-symbols-outlined text-base">folder</span>
                            </span>
                            <div>
                                <h3 class="font-extrabold text-slate-900 dark:text-white text-sm tracking-tight">{{ $groupName }}</h3>
                                <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                    <span class="font-semibold text-blue-600 dark:text-blue-400">{{ $activities->count() }} Aktivitas</span>
                                    <span>•</span>
                                    <span>Urutan #{{ $activities->min('order') }} @if($activities->count() > 1) - #{{ $activities->max('order') }} @endif</span>
                                </div>
                            </div>
                        </div>

                        <!-- Header Quick Actions -->
                        <div class="flex items-center gap-2 self-end sm:self-center">
                            <button wire:click="openAddModal('{{ $groupName }}')" type="button" 
                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold text-blue-700 bg-blue-50 hover:bg-blue-100 dark:bg-blue-950/40 dark:text-blue-300 dark:hover:bg-blue-900/40 transition">
                                <span class="material-symbols-outlined text-xs">add</span> Tambah Aktivitas
                            </button>

                            <button type="button" 
                                @click="
                                    Swal.fire({
                                        title: 'Hapus Kelompok Tugas?',
                                        text: 'Apakah Anda yakin ingin menghapus seluruh kelompok \'{{ addslashes($groupName) }}\' beserta {{ $activities->count() }} aktivitas di dalamnya?',
                                        icon: 'warning',
                                        showCancelButton: true,
                                        confirmButtonColor: '#e11d48',
                                        cancelButtonColor: '#64748b',
                                        confirmButtonText: 'Ya, Hapus Grup',
                                        cancelButtonText: 'Batal'
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            $wire.deleteGroup('{{ addslashes($groupName) }}');
                                        }
                                    });
                                "
                                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition">
                                <span class="material-symbols-outlined text-xs">delete</span> Hapus Grup
                            </button>
                        </div>
                    </div>

                    <!-- Sub-Activities List -->
                    <ul class="divide-y divide-slate-150 dark:divide-slate-800">
                        @foreach($activities as $activity)
                            <li wire:key="routine-item-{{ $activity->id }}" 
                                class="px-5 py-3 flex items-center justify-between gap-4 hover:bg-slate-50/50 dark:hover:bg-slate-850/30 transition">
                                
                                <div class="flex items-start gap-3 min-w-0">
                                    <!-- Order Badge -->
                                    <span class="flex-shrink-0 size-6 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-bold text-xs flex items-center justify-center mt-0.5">
                                        {{ $activity->order }}
                                    </span>

                                    <!-- Activity Title & Note -->
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-200 leading-snug break-words">
                                            {{ $activity->activity_name }}
                                        </p>
                                        @if($activity->note)
                                            <p class="text-xs text-slate-500 dark:text-slate-400 italic mt-0.5">
                                                "{{ $activity->note }}"
                                            </p>
                                        @endif
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex items-center gap-1.5 flex-shrink-0">
                                    <button wire:click="openEditModal({{ $activity->id }})" type="button" 
                                        class="p-1.5 text-slate-500 hover:text-blue-600 dark:text-slate-400 dark:hover:text-blue-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition"
                                        title="Ubah Aktivitas">
                                        <span class="material-symbols-outlined text-base">edit</span>
                                    </button>

                                    <button type="button" 
                                        @click="
                                            Swal.fire({
                                                title: 'Hapus Aktivitas?',
                                                text: 'Yakin ingin menghapus aktivitas \'{{ addslashes($activity->activity_name) }}\'?',
                                                icon: 'warning',
                                                showCancelButton: true,
                                                confirmButtonColor: '#e11d48',
                                                cancelButtonColor: '#64748b',
                                                confirmButtonText: 'Ya, Hapus',
                                                cancelButtonText: 'Batal'
                                            }).then((result) => {
                                                if (result.isConfirmed) {
                                                    $wire.deleteRoutine({{ $activity->id }});
                                                }
                                            });
                                        "
                                        class="p-1.5 text-slate-400 hover:text-rose-600 dark:text-slate-500 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/20 rounded-lg transition"
                                        title="Hapus Aktivitas">
                                        <span class="material-symbols-outlined text-base">delete</span>
                                    </button>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Creation/Editing Modal -->
    <x-ui.modal modalId="routine-modal" class="max-w-md p-6 sm:p-8" :showCloseButton="true">
        <div class="space-y-5">
            <div>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">
                    {{ $editingRoutineId ? 'Ubah Aktivitas Rutinitas' : 'Tambah Aktivitas Rutinitas' }}
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Masukkan rincian aktivitas dan kelompok tugas untuk hari {{ ucfirst($day) }}.</p>
            </div>

            <!-- Task Group Field with Auto-Complete Suggestions -->
            <flux:field>
                <flux:label>Kelompok Tugas (Task Group)</flux:label>
                <input list="existing-groups-list" wire:model="task_group" 
                    placeholder="Contoh: Grup 1, Pantry, Lantai 1, dsb." 
                    class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:focus:border-blue-500">
                <datalist id="existing-groups-list">
                    @foreach($existingGroups as $grp)
                        <option value="{{ $grp }}"></option>
                    @endforeach
                </datalist>
                <flux:error name="task_group" />
                <p class="text-[11px] text-slate-400 mt-1">Pilih dari grup yang ada atau ketik nama grup baru.</p>
            </flux:field>

            <!-- Activity Name Field -->
            <flux:field>
                <flux:label>Nama Aktivitas</flux:label>
                <flux:input wire:model="activity_name" placeholder="Contoh: Sapu Teras dan Halaman" />
                <flux:error name="activity_name" />
            </flux:field>

            <!-- Note Field -->
            <flux:field>
                <flux:label>Catatan (Opsional)</flux:label>
                <flux:textarea wire:model="note" placeholder="Contoh: Pastikan tempat sampah luar dikosongkan" rows="2" />
                <flux:error name="note" />
            </flux:field>

            <!-- Order Field -->
            <flux:field>
                <flux:label>Urutan Eksekusi (Order)</flux:label>
                <flux:input type="number" wire:model="order" min="1" />
                <flux:error name="order" />
            </flux:field>

            <!-- Modal Footer Buttons -->
            <div class="flex space-x-2 justify-end pt-4 border-t border-slate-100 dark:border-slate-800">
                <flux:button wire:click="closeModal" variant="ghost">Batal</flux:button>
                <flux:button wire:click="saveRoutine" variant="primary">Simpan</flux:button>
            </div>
        </div>
    </x-ui.modal>
</div>
