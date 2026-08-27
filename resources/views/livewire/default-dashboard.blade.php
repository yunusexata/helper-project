<div class="p-6 max-w-5xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Monitoring Progres Harian</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Pantau status pengerjaan rutinitas harian petugas hari ini.</p>
    </div>

    <!-- Filter Card -->
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm p-6 mb-6">
        <div class="max-w-md">
            <label for="admin-petugas-select" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Pilih Petugas (Helper)</label>
            <select id="admin-petugas-select" wire:model.live="adminSelectedHelperId" 
                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:focus:border-blue-500">
                <option value="">-- Pilih Petugas --</option>
                @foreach($helpersList as $helper)
                    <option value="{{ $helper->id }}">{{ $helper->name }} ({{ $helper->email }})</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Today's Checklist Table -->
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50 px-6 py-4 flex items-center justify-between">
            <h2 class="font-semibold text-slate-900 dark:text-white">Rutinitas Hari {{ ucfirst(now()->locale('id')->dayName) }}</h2>
            <span class="text-xs text-slate-500 dark:text-slate-400">Diperbarui secara real-time</span>
        </div>

        @if(!$adminSelectedHelperId)
            <div class="p-8 text-center text-slate-500 dark:text-slate-400">
                Silakan pilih petugas untuk melihat progres hari ini.
            </div>
        @elseif($adminRoutines->isEmpty())
            <div class="p-8 text-center text-slate-500 dark:text-slate-400">
                Tidak ada rutinitas terdaftar untuk hari ini.
            </div>
        @else
            <!-- Responsive Table Wrapper -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm whitespace-nowrap border-collapse">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-800/30 text-xs font-semibold text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800 uppercase tracking-wider">
                            <th class="px-6 py-3 w-16 text-center">Urutan</th>
                            <th class="px-6 py-3">Nama Aktivitas</th>
                            <th class="px-6 py-3">Catatan Rencana</th>
                            <th class="px-6 py-3">Bukti Gambar</th>
                            <th class="px-6 py-3 w-32 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @foreach($adminRoutines as $routine)
                            @php
                                $rowClass = $routine->is_completed 
                                    ? 'bg-emerald-50/60 dark:bg-emerald-950/10 text-slate-900 dark:text-slate-100' 
                                    : 'bg-rose-50/60 dark:bg-rose-950/10 text-slate-900 dark:text-slate-100';
                            @endphp
                            <tr class="{{ $rowClass }} transition-colors">
                                <td class="px-6 py-4 text-center font-semibold">{{ $routine->order }}</td>
                                <td class="px-6 py-4 font-medium">
                                    {{ $routine->activity_name }}
                                    @if($routine->is_completed && $routine->logged_note)
                                        <p class="text-xs text-slate-500 mt-1 italic">Note Petugas: "{{ $routine->logged_note }}"</p>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-slate-500 dark:text-slate-400">{{ $routine->note ?: '-' }}</td>
                                <td class="px-6 py-4">
                                    @if($routine->is_completed && $routine->attachments->isNotEmpty())
                                        <div class="flex gap-1.5 overflow-x-auto max-w-[200px]">
                                            @foreach($routine->attachments as $file)
                                                <a href="{{ asset('storage/' . $file->path) }}" target="_blank" class="flex-shrink-0 w-8 h-8 rounded border border-slate-300 overflow-hidden bg-slate-150">
                                                    <img src="{{ asset('storage/' . $file->path) }}" class="object-cover w-full h-full">
                                                </a>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-xs text-slate-450">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($routine->is_completed)
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-400">
                                            <span class="material-symbols-outlined text-base align-middle">check_circle</span> Selesai
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-800 dark:bg-rose-950/30 dark:text-rose-400">
                                            <span class="material-symbols-outlined text-base align-middle">cancel</span> Belum Selesai
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
