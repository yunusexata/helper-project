<div class="p-2 sm:p-6 max-w-5xl mx-auto space-y-4 sm:space-y-6">
    <!-- Header (Only shown when accessed directly via /dashboard, not duplicated on landing page) -->
    @if(!request()->routeIs('home'))
        <div class="px-1 sm:px-0">
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white">Monitoring Progres Harian</h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">Pantau status pengerjaan rutinitas dan permintaan tugas petugas harian secara real-time.</p>
        </div>
    @endif

    <!-- Filter Card -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs p-4 sm:p-6 space-y-4">
        @auth
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
                <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                    Filter Monitoring
                </div>
                <button 
                    type="button" 
                    wire:click="openExportModal"
                    class="inline-flex items-center justify-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs transition shrink-0 cursor-pointer">
                    <span class="material-symbols-outlined text-base">file_download</span>
                    <span>Export Excel</span>
                </button>
            </div>
        @endauth

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-6">
            <!-- Petugas Filter -->
            <div>
                <label for="admin-petugas-select" class="block text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                    Pilih Petugas (Helper)
                </label>
                <select id="admin-petugas-select" wire:model.live="adminSelectedHelperId" 
                    class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs sm:text-sm text-slate-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                    <option value="">-- Pilih Petugas --</option>
                    @foreach($helpersList as $helper)
                        <option value="{{ $helper->id }}">{{ $helper->name }} ({{ $helper->email }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Tanggal Filter -->
            <div>
                <label for="admin-tanggal-input" class="block text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                    Pilih Tanggal
                </label>
                <input id="admin-tanggal-input" type="date" wire:model.live="adminSelectedTanggal" 
                    class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs sm:text-sm text-slate-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white">
            </div>
        </div>
    </div>

    <!-- Datatable Component -->
    @if($adminSelectedHelperId && $adminSelectedTanggal)
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs p-3.5 sm:p-6">
            <livewire:helper-jobdesk.history.datatable 
                :petugasId="$adminSelectedHelperId" 
                :tanggal="$adminSelectedTanggal" 
                wire:key="admin-dt-{{ $adminSelectedHelperId }}-{{ $adminSelectedTanggal }}" />
        </div>
    @else
        <div class="bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-dashed border-slate-200 dark:border-slate-700 p-8 text-center text-xs sm:text-sm text-slate-500 dark:text-slate-400">
            Silakan pilih Petugas dan Tanggal untuk menampilkan monitoring progres.
        </div>
    @endif

    @auth
        <!-- Export Excel Modal -->
        <x-ui.modal modalId="export-jobdesk-modal" class="max-w-md p-6 sm:p-8" :showCloseButton="true"
            @open-export-modal.window="open = true"
            @close-export-modal.window="open = false">
            <div class="space-y-5">
                <div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <span class="material-symbols-outlined text-emerald-600 dark:text-emerald-400">table_view</span>
                        <span>Export Laporan Excel</span>
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Pilih petugas dan rentang tanggal untuk mengunduh riwayat jobdesk harian dalam format Excel (.xlsx).
                    </p>
                </div>

                <div class="space-y-4">
                    <!-- Petugas Select -->
                    <div>
                        <label for="export-petugas-select" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                            Pilih Petugas (Helper) <span class="text-rose-500">*</span>
                        </label>
                        <select id="export-petugas-select" wire:model="exportPetugasId"
                            class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs sm:text-sm text-slate-900 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                            <option value="">-- Pilih Petugas --</option>
                            @foreach($helpersList as $helper)
                                <option value="{{ $helper->id }}">{{ $helper->name }} ({{ $helper->email }})</option>
                            @endforeach
                        </select>
                        @error('exportPetugasId')
                            <p class="text-[11px] text-rose-600 dark:text-rose-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Tanggal Awal & Akhir -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label for="export-tanggal-awal" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                Tanggal Awal <span class="text-rose-500">*</span>
                            </label>
                            <input id="export-tanggal-awal" type="date" wire:model="exportTanggalAwal"
                                class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs sm:text-sm text-slate-900 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                            @error('exportTanggalAwal')
                                <p class="text-[11px] text-rose-600 dark:text-rose-400 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="export-tanggal-akhir" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                Tanggal Akhir <span class="text-rose-500">*</span>
                            </label>
                            <input id="export-tanggal-akhir" type="date" wire:model="exportTanggalAkhir"
                                class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs sm:text-sm text-slate-900 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                            @error('exportTanggalAkhir')
                                <p class="text-[11px] text-rose-600 dark:text-rose-400 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" wire:click="closeExportModal"
                        class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer">
                        Batal
                    </button>
                    <button type="button" wire:click="exportExcel" wire:loading.attr="disabled"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white shadow-xs transition cursor-pointer">
                        <span wire:loading.remove wire:target="exportExcel" class="material-symbols-outlined text-sm">download</span>
                        <span wire:loading wire:target="exportExcel" class="material-symbols-outlined text-sm animate-spin">progress_activity</span>
                        <span>Download Excel</span>
                    </button>
                </div>
            </div>
        </x-ui.modal>
    @endauth
</div>
