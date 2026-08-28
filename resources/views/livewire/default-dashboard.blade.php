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
                            <th class="px-6 py-3 w-32">Kategori Grup</th>
                            <th class="px-6 py-3">Daftar Aktivitas Rutinitas</th>
                            <th class="px-6 py-3 w-56">Catatan Laporan</th>
                            <th class="px-6 py-3 w-36">Bukti Foto</th>
                            <th class="px-6 py-3 w-32 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @foreach($adminRoutines as $groupName => $group)
                            @php
                                $rowClass = $group->is_completed 
                                    ? 'bg-emerald-50/60 dark:bg-emerald-950/10 text-slate-900 dark:text-slate-100' 
                                    : 'bg-amber-50/40 dark:bg-amber-950/5 text-slate-900 dark:text-slate-100';
                            @endphp
                            <tr class="{{ $rowClass }} border-b border-slate-200 dark:border-slate-800 transition-colors">
                                <td class="px-6 py-4 font-bold text-xs uppercase text-slate-650 dark:text-slate-300 align-top pt-5">
                                    {{ $groupName }}
                                </td>
                                <td class="px-6 py-4 align-top">
                                    <ul class="space-y-1.5">
                                        @foreach($group->activities as $activity)
                                            <li class="flex items-start gap-2 text-xs">
                                                <span class="material-symbols-outlined text-blue-500 text-sm mt-0.5 select-none">task_alt</span>
                                                <span class="font-medium text-slate-800 dark:text-slate-200">{{ $activity->activity_name }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-600 dark:text-slate-400 align-top pt-5">
                                    @if($group->is_completed && $group->logged_note)
                                        <p class="italic">"{{ $group->logged_note }}"</p>
                                    @elseif($group->is_completed)
                                        <span class="text-slate-400 font-medium">Selesai tanpa catatan</span>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 align-top pt-4">
                                    @if($group->is_completed && $group->attachments->isNotEmpty())
                                        <div class="flex gap-1.5 overflow-x-auto max-w-[150px]">
                                            @foreach($group->attachments as $file)
                                                <a href="{{ asset('storage/' . $file->path) }}" target="_blank" class="flex-shrink-0 w-8 h-8 rounded border border-slate-300 overflow-hidden bg-slate-150">
                                                    <img src="{{ asset('storage/' . $file->path) }}" class="object-cover w-full h-full">
                                                </a>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-xs text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center align-top pt-4">
                                    @if($group->is_completed)
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-400">
                                            <span class="material-symbols-outlined text-base align-middle">check_circle</span> Selesai
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-950/30 dark:text-amber-500">
                                            <span class="material-symbols-outlined text-base align-middle">hourglass_empty</span> Belum Selesai
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
