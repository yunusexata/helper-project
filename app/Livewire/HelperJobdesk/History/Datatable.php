<?php

namespace App\Livewire\HelperJobdesk\History;

use App\Models\EmployeeWhitelist;
use App\Models\HelperJobdeskDailyHistoryAttachment;
use App\Models\HelperJobdeskRequest;
use App\Models\HelperJobdeskRoutine;
use App\Models\User;
use App\Traits\Livewire\WithDatatable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Datatable extends Component
{
    use WithDatatable;

    /**
     * Selected Helper user ID.
     */
    public ?int $petugasId = null;

    /**
     * Selected date filter (YYYY-MM-DD).
     */
    public ?string $tanggal = null;

    /**
     * Mounted component hook.
     */
    public function onMount(): void
    {
        $this->keyword_filter = true;
        $this->sortBy = 'start_at';
        $this->sortDirection = 'asc';
        $this->length = 100;
    }

    /**
     * Get Employee Whitelist ID for the selected helper user ID.
     */
    protected function getWhitelistId(): ?int
    {
        if (! $this->petugasId) {
            return null;
        }

        $user = User::find($this->petugasId);
        if (! $user) {
            return null;
        }

        $whitelist = EmployeeWhitelist::where('email', $user->email)->first();

        return $whitelist?->id;
    }

    /**
     * Resolve the row CSS classes based on completion status.
     */
    protected function getRowClass($item): string
    {
        if (! empty($item->finish_at)) {
            return 'bg-emerald-50/40 dark:bg-emerald-950/10 border-emerald-100 dark:border-emerald-950/20';
        }

        if (! empty($item->start_at)) {
            return 'bg-blue-50/30 dark:bg-blue-950/10 border-blue-100 dark:border-blue-950/20';
        }

        return 'bg-amber-50/20 dark:bg-amber-950/5 border-amber-100 dark:border-amber-950/10';
    }

    /**
     * Define the datatable columns.
     */
    public function getColumns(): array
    {
        return [
            [
                'key' => 'task_type',
                'name' => 'Tipe / Kategori',
                'sortable' => true,
                'class' => fn ($item) => $this->getRowClass($item).' w-36 align-top',
                'render' => function ($item) {
                    if ($item->task_type === 'Request') {
                        return '<div class="space-y-1">
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-yellow-100 text-yellow-800 dark:bg-yellow-950/40 dark:text-blue-400">
                                <span class="material-symbols-outlined text-xs">assignment_add</span> Request
                            </span>
                        </div>';
                    }

                    return '<div class="space-y-1">
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                            <span class="material-symbols-outlined text-xs">checklist</span> Rutinitas
                        </span>
                    </div>';
                },
            ],
            [
                'key' => 'activity_name',
                'name' => 'Nama Aktivitas',
                'sortable' => true,
                'class' => fn ($item) => $this->getRowClass($item).' font-medium align-top min-w-[240px]',
                'render' => function ($item) {
                    if ($item->task_type === 'Rutinitas') {
                        $activities = explode('|||', $item->activity_name ?? '');
                        $html = '<div class="space-y-2">';
                        $html .= '<div class="text-xs font-extrabold uppercase tracking-wide text-slate-900 dark:text-white">Grup '.e($item->category).' ('.count($activities).' Aktivitas)</div>';
                        $html .= '<ul class="space-y-1 bg-slate-50/70 dark:bg-slate-800/50 p-2.5 rounded-xl border border-slate-200/60 dark:border-slate-700/60">';
                        foreach ($activities as $act) {
                            if (trim($act) === '') {
                                continue;
                            }
                            $html .= '<li class="flex items-start gap-1.5 text-xs text-slate-700 dark:text-slate-300">';
                            $html .= '<span class="material-symbols-outlined text-sm text-blue-500 flex-shrink-0 mt-0.5 select-none">task_alt</span>';
                            $html .= '<span>'.e(trim($act)).'</span>';
                            $html .= '</li>';
                        }
                        $html .= '</ul>';
                        $html .= '</div>';

                        return $html;
                    }

                    return '<div class="text-sm font-semibold text-slate-800 dark:text-slate-200">'.e($item->activity_name).'</div>';
                },
            ],
            [
                'name' => 'Status',
                'sortable' => false,
                'class' => fn ($item) => $this->getRowClass($item).' w-32 text-center align-top',
                'render' => function ($item) {
                    if (! empty($item->finish_at)) {
                        return '<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-400">
                            <span class="material-symbols-outlined text-sm">check_circle</span> Selesai
                        </span>';
                    }

                    if (! empty($item->start_at)) {
                        return '<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-950/30 dark:text-blue-400">
                            <span class="material-symbols-outlined text-sm animate-spin">sync</span> Berjalan
                        </span>';
                    }

                    return '<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-950/30 dark:text-amber-500">
                        <span class="material-symbols-outlined text-sm">hourglass_empty</span> Belum Selesai
                    </span>';
                },
            ],
            [
                'key' => 'start_at',
                'name' => 'Jam Mulai',
                'sortable' => true,
                'class' => fn ($item) => $this->getRowClass($item).' text-center w-28 align-top text-xs font-semibold',
                'render' => function ($item) {
                    return ! empty($item->start_at)
                        ? Carbon::parse($item->start_at)->format('H:i')
                        : '<span class="text-slate-400">-</span>';
                },
            ],
            [
                'key' => 'finish_at',
                'name' => 'Jam Selesai',
                'sortable' => true,
                'class' => fn ($item) => $this->getRowClass($item).' text-center w-28 align-top text-xs font-semibold',
                'render' => function ($item) {
                    if (! empty($item->finish_at)) {
                        return Carbon::parse($item->finish_at)->format('H:i');
                    }

                    if (! empty($item->start_at)) {
                        return '<span class="text-blue-600 font-bold text-[11px] flex items-center justify-center gap-1"><span class="material-symbols-outlined text-xs animate-spin">sync</span> Berjalan</span>';
                    }

                    return '<span class="text-slate-400">-</span>';
                },
            ],
            [
                'name' => 'Durasi',
                'sortable' => false,
                'class' => fn ($item) => $this->getRowClass($item).' text-center w-28 align-top text-xs font-semibold',
                'render' => function ($item) {
                    if (empty($item->start_at) || empty($item->finish_at)) {
                        return '<span class="text-slate-400">-</span>';
                    }

                    $start = Carbon::parse($item->start_at)->setTimezone('Asia/Jakarta');
                    $finish = Carbon::parse($item->finish_at)->setTimezone('Asia/Jakarta');

                    $diffInSeconds = max(0, $start->diffInSeconds($finish));
                    $hours = floor($diffInSeconds / 3600);
                    $minutes = floor(($diffInSeconds % 3600) / 60);

                    if ($hours > 0) {
                        $duration = "{$hours} jam {$minutes} mnt";
                    } elseif ($minutes > 0) {
                        $duration = "{$minutes} menit";
                    } else {
                        $seconds = $diffInSeconds % 60;
                        $duration = "{$seconds} dtk";
                    }

                    return '<span class="text-slate-700 dark:text-slate-200 font-bold">'.$duration.'</span>';
                },
            ],
            [
                'key' => 'logged_note',
                'name' => 'Catatan Laporan',
                'sortable' => false,
                'class' => fn ($item) => $this->getRowClass($item).' text-xs align-top max-w-[200px]',
                'render' => function ($item) {
                    if (! empty($item->logged_note)) {
                        return '<span class="italic text-slate-650 dark:text-slate-350">"'.e($item->logged_note).'"</span>';
                    }

                    return '<span class="text-slate-400">-</span>';
                },
            ],
            [
                'name' => 'Bukti Foto',
                'sortable' => false,
                'class' => fn ($item) => $this->getRowClass($item).' align-top w-36',
                'render' => function ($item) {
                    if (! empty($item->attachments) && $item->attachments->isNotEmpty()) {
                        $html = '<div class="flex gap-1.5 overflow-x-auto max-w-[140px]">';
                        foreach ($item->attachments as $att) {
                            $url = asset('storage/'.$att->path);
                            $html .= '<a href="'.$url.'" target="_blank" class="size-8 rounded-lg border border-slate-200 overflow-hidden flex-shrink-0 bg-slate-100 hover:opacity-80 transition">';
                            $html .= '<img src="'.$url.'" class="object-cover w-full h-full" alt="Bukti">';
                            $html .= '</a>';
                        }
                        $html .= '</div>';

                        return $html;
                    }

                    return '<span class="text-xs text-slate-400">-</span>';
                },
            ],
        ];
    }

    /**
     * Get the query builder instance executing the UNION between grouped routines and requests.
     */
    public function getQuery()
    {
        if (! $this->petugasId || ! $this->tanggal) {
            return DB::table('helper_jobdesk_routines')->whereRaw('1 = 0');
        }

        $whitelistId = $this->getWhitelistId() ?? 0;
        $dayName = strtolower(Carbon::parse($this->tanggal)->locale('id')->dayName);
        $driver = DB::connection()->getDriverName();
        $groupConcat = $driver === 'sqlite'
            ? "GROUP_CONCAT(r.activity_name, '|||')"
            : "STRING_AGG(r.activity_name, '|||')";

        // 1. Grouped Routines for the selected day (1 row per task_group)
        $routinesQuery = DB::table('helper_jobdesk_routines as r')
            ->leftJoin('helper_jobdesk_daily_histories as h', function ($join) use ($whitelistId) {
                $join->on('h.subject_id', '=', 'r.id')
                    ->where('h.subject_type', '=', HelperJobdeskRoutine::class)
                    ->where('h.employee_whitelists_id', '=', $whitelistId)
                    ->whereDate('h.created_at', '=', $this->tanggal)
                    ->whereNull('h.deleted_at');
            })
            ->where('r.day', '=', $dayName)
            ->whereNull('r.deleted_at')
            ->groupBy('r.task_group')
            ->select([
                DB::raw("'Rutinitas' as task_type"),
                'r.task_group as category',
                DB::raw("{$groupConcat} as activity_name"),
                DB::raw('MAX(h.id) as history_id'),
                DB::raw('MAX(h.start_at) as start_at'),
                DB::raw('MAX(h.finish_at) as finish_at'),
                DB::raw('MAX(h.note) as logged_note'),
                DB::raw('MIN(r.order) as sort_order'),
            ]);

        // 2. All Ad-hoc Requests logged for that helper on that date
        $requestsQuery = DB::table('helper_jobdesk_requests as req')
            ->leftJoin('helper_jobdesk_daily_histories as h', function ($join) use ($whitelistId) {
                $join->on('h.subject_id', '=', 'req.id')
                    ->where('h.subject_type', '=', HelperJobdeskRequest::class)
                    ->where('h.employee_whitelists_id', '=', $whitelistId)
                    ->whereDate('h.created_at', '=', $this->tanggal)
                    ->whereNull('h.deleted_at');
            })
            ->where('req.employee_whitelists_id', '=', $whitelistId)
            ->whereDate('req.created_at', '=', $this->tanggal)
            ->whereNull('req.deleted_at')
            ->select([
                DB::raw("'Request' as task_type"),
                DB::raw("'Request' as category"),
                'req.activity_name as activity_name',
                'h.id as history_id',
                'h.start_at as start_at',
                'h.finish_at as finish_at',
                'h.note as logged_note',
                DB::raw('9999 as sort_order'),
            ]);

        $unionQuery = $routinesQuery->unionAll($requestsQuery);

        return DB::query()->fromSub($unionQuery, 'combined_jobdesks');
    }

    /**
     * Process query with search and custom sorting.
     */
    public function datatableGetProcessedQuery()
    {
        $columns = $this->getColumns();
        $query = $this->getQuery();
        $search = $this->search;
        $sortBy = $this->sortBy;
        $sortDirection = $this->sortDirection ?: 'asc';
        $driver = DB::connection()->getDriverName();
        $likeOperator = $driver === 'pgsql' ? 'ILIKE' : 'LIKE';

        $query->when($search, function ($query) use ($search, $columns, $likeOperator) {
            $query->where(function ($query) use ($columns, $search, $likeOperator) {
                foreach ($columns as $col) {
                    if (
                        isset($col['key'])
                        && (! isset($col['searchable']) || (isset($col['searchable']) && $col['searchable']))
                    ) {
                        $query->orWhere($col['key'], $likeOperator, "%$search%");
                    }
                }
            });
        });

        $query->when($sortBy, function ($query) use ($sortBy, $sortDirection) {
            if ($sortBy === 'start_at') {
                $query->orderByRaw("CASE WHEN start_at IS NULL THEN 1 ELSE 0 END, start_at {$sortDirection}, sort_order ASC");
            } else {
                $query->orderBy($sortBy, $sortDirection);
            }
        });

        return $query;
    }

    /**
     * Fetch data and attach photo attachments in one batch query without calling non-existent parent method.
     */
    public function datatableGetData()
    {
        $paginated = $this->datatablePaginate($this->datatableGetProcessedQuery());
        $historyIds = collect($paginated->items())->pluck('history_id')->filter()->unique()->toArray();

        $attachments = ! empty($historyIds)
            ? HelperJobdeskDailyHistoryAttachment::whereIn('helper_jobdesk_daily_histories', $historyIds)->get()->groupBy('helper_jobdesk_daily_histories')
            : collect();

        foreach ($paginated->items() as $item) {
            $item->attachments = $item->history_id && isset($attachments[$item->history_id])
                ? $attachments[$item->history_id]
                : collect();
        }

        return $paginated;
    }

    /**
     * Get the template view file.
     */
    public function getView(): string
    {
        return 'livewire.helper-jobdesk.history.datatable';
    }
}
