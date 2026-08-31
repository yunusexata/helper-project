<div class="p-2 sm:p-6 max-w-5xl mx-auto space-y-4 sm:space-y-6">
    <!-- Header (Only shown when accessed directly via /dashboard, not duplicated on landing page) -->
    @if(!request()->routeIs('home'))
        <div class="px-1 sm:px-0">
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white">Monitoring Progres Harian</h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">Pantau status pengerjaan rutinitas dan permintaan tugas petugas harian secara real-time.</p>
        </div>
    @endif

    <!-- Filter Card -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs p-4 sm:p-6">
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
</div>
