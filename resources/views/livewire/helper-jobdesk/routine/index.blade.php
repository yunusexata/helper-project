<div class="p-6 max-w-4xl mx-auto">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Rencana Kerja Harian (Routine)</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Daftar aktivitas rutin harian dari awal sampai akhir kerja.</p>
        </div>
        
        <div class="flex items-center gap-3">
            <div class="flex items-center gap-2">
                <label for="day-select" class="text-sm font-medium text-slate-700 dark:text-slate-300">Pilih Hari:</label>
                <select id="day-select" wire:model.live="day" 
                    class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:focus:border-blue-500">
                    <option value="senin">Senin</option>
                    <option value="selasa">Selasa</option>
                    <option value="rabu">Rabu</option>
                    <option value="kamis">Kamis</option>
                    <option value="jumat">Jumat</option>
                    <option value="sabtu">Sabtu</option>
                    <option value="minggu">Minggu</option>
                </select>
            </div>
            
            <flux:button wire:click="openAddModal" variant="primary" icon="plus" size="sm">
                Tambah
            </flux:button>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50 px-6 py-4 flex items-center justify-between">
            <h2 class="font-semibold text-slate-900 dark:text-white capitalize">Aktivitas Hari {{ $day }}</h2>
            <span class="text-xs text-slate-500 dark:text-slate-400">Tarik & lepas untuk mengurutkan</span>
        </div>
        
        @if($routines->isEmpty())
            <div class="p-8 text-center text-slate-500 dark:text-slate-400">
                Tidak ada aktivitas rutin untuk hari ini.
            </div>
        @else
            <!-- Custom Table Header -->
            <div class="hidden sm:grid grid-cols-12 gap-4 px-6 py-3 bg-slate-50/50 dark:bg-slate-800/30 text-xs font-semibold text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800 uppercase tracking-wider">
                <div class="col-span-2 text-center">Urutan</div>
                <div class="col-span-8">Nama Aktivitas</div>
                <div class="col-span-2 text-right">Aksi</div>
            </div>

            <!-- Sortable List Wrapper -->
            <ul wire:sort="updateOrder" class="divide-y divide-slate-200 dark:divide-slate-800">
                @foreach($routines as $routine)
                    <li wire:key="routine-{{ $routine->id }}" wire:sort:item="{{ $routine->id }}" 
                        class="grid grid-cols-12 gap-4 px-6 py-4 items-center hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors group cursor-grab active:cursor-grabbing">
                        
                        <!-- Order Badge and Drag Handle -->
                        <div class="col-span-2 flex items-center justify-center gap-2">
                            <!-- Drag Handle Icon -->
                            <svg class="w-4 h-4 text-slate-400 dark:text-slate-600 opacity-0 group-hover:opacity-100 transition-opacity flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                            
                            <div class="flex items-center justify-center w-8 h-8 rounded-full bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400 text-sm font-semibold">
                                {{ $routine->order }}
                            </div>
                        </div>

                        <!-- Activity Name & Note -->
                        <div class="col-span-8">
                            <h3 class="font-medium text-slate-900 dark:text-white">{{ $routine->activity_name }}</h3>
                            @if($routine->note)
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $routine->note }}</p>
                            @endif
                        </div>

                        <!-- Edit Button Column -->
                        <div class="col-span-2 text-right">
                            <flux:button wire:click="openEditModal({{ $routine->id }})" variant="ghost" size="sm" icon="pencil-square" class="hover:text-blue-600">
                                Edit
                            </flux:button>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    <!-- Creation/Editing Modal -->
    <x-ui.modal modalId="routine-modal" class="max-w-md p-6 sm:p-8" :showCloseButton="true">
        <div class="space-y-6">
            <div>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ $editingRoutineId ? 'Ubah Rutinitas' : 'Tambah Rutinitas' }}</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Masukkan detail rencana kerja harian.</p>
            </div>

            <flux:field>
                <flux:label>Nama Aktivitas</flux:label>
                <flux:input wire:model="activity_name" placeholder="Contoh: Mengisi air minum" />
                <flux:error name="activity_name" />
            </flux:field>

            <flux:field>
                <flux:label>Catatan (Opsional)</flux:label>
                <flux:textarea wire:model="note" placeholder="Contoh: Menggunakan air galon baru jika habis" />
                <flux:error name="note" />
            </flux:field>

            <flux:field>
                <flux:label>Urutan (Order)</flux:label>
                <flux:input type="number" wire:model="order" min="1" />
                <flux:error name="order" />
            </flux:field>

            <div class="flex space-x-2 justify-end pt-4">
                <flux:button wire:click="closeModal" variant="ghost">Batal</flux:button>
                <flux:button wire:click="saveRoutine" variant="primary">Simpan</flux:button>
            </div>
        </div>
    </x-ui.modal>
</div>
