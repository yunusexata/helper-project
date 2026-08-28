<div class="p-6 max-w-5xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Riwayat Kerja Harian</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Laporan riwayat aktivitas rutin harian petugas.</p>
    </div>

    <!-- Filter Card -->
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Petugas Filter -->
            <div>
                <label for="petugas-select" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Petugas</label>
                <select id="petugas-select" wire:model.live="petugasId" 
                    class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:focus:border-blue-500">
                    <option value="">-- Pilih Petugas --</option>
                    @foreach($petugasList as $helper)
                        <option value="{{ $helper->id }}">{{ $helper->name }} ({{ $helper->email }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Tanggal Filter -->
            <div>
                <label for="tanggal-input" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Tanggal</label>
                <input id="tanggal-input" type="date" wire:model.live="tanggal" 
                    class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:focus:border-blue-500">
            </div>
        </div>
    </div>

    <!-- Datatable Component -->
    @if($petugasId && $tanggal)
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm p-6">
            <livewire:helper-jobdesk.history.datatable 
                :petugasId="$petugasId" 
                :tanggal="$tanggal" 
                wire:key="history-dt-{{ $petugasId }}-{{ $tanggal }}" />
        </div>
    @else
        <div class="bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-dashed border-slate-200 dark:border-slate-700 p-8 text-center text-slate-500 dark:text-slate-400">
            Silakan pilih Petugas dan Tanggal untuk menampilkan riwayat.
        </div>
    @endif
</div>
