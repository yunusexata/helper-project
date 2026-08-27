<div class="p-6 max-w-7xl mx-auto">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Manajemen Pengguna</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Kelola data pengguna aplikasi dan jabatannya.</p>
        </div>
        
        <div>
            @if(auth()->user()->hasPermissionTo(\App\Helpers\PermissionHelper::transform(\App\Helpers\PermissionHelper::ACCESS_USER, \App\Helpers\PermissionHelper::TYPE_CREATE)))
                <a href="{{ route('user.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-semibold shadow-sm transition">
                    <span class="material-symbols-outlined text-lg">add</span> Tambah Pengguna
                </a>
            @endif
        </div>
    </div>

    <!-- Filter & Table -->
    <div class="bg-white dark:bg-slate-950 dark:border-slate-850 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm p-6 space-y-6">
        <livewire:account.user.filter />
        <livewire:account.user.datatable />
    </div>
</div>
