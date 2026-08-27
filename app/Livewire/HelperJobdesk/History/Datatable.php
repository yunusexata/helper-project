<?php

namespace App\Livewire\HelperJobdesk\History;

use App\Models\EmployeeWhitelist;
use App\Models\HelperJobdeskDailyHistory;
use App\Models\HelperJobdeskRoutine;
use App\Models\User;
use App\Traits\Livewire\WithDatatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
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
        $this->keyword_filter = false;
        $this->sortBy = 'order';
        $this->sortDirection = 'asc';
        $this->length = 100;
    }

    /**
     * Check if the routine has a logged history for the selected employee and date.
     */
    protected function hasHistory(int $routineId): bool
    {
        if (! $this->petugasId || ! $this->tanggal) {
            return false;
        }

        $user = User::find($this->petugasId);
        if (! $user) {
            return false;
        }

        $whitelist = EmployeeWhitelist::where('email', $user->email)->first();
        if (! $whitelist) {
            return false;
        }

        return HelperJobdeskDailyHistory::where('employee_whitelists_id', $whitelist->id)
            ->where('subject_id', $routineId)
            ->where('subject_type', HelperJobdeskRoutine::class)
            ->whereDate('created_at', $this->tanggal)
            ->exists();
    }

    /**
     * Resolve the row CSS classes based on completion status.
     */
    protected function getRowClass($item): string
    {
        $completed = $this->hasHistory($item->id);

        if ($completed) {
            return 'bg-emerald-50/60 dark:bg-emerald-950/10 border-emerald-100 dark:border-emerald-950/20';
        }

        return 'bg-rose-50/60 dark:bg-rose-950/10 border-rose-100 dark:border-rose-950/20';
    }

    /**
     * Define the datatable columns.
     */
    public function getColumns(): array
    {
        return [
            [
                'key' => 'order',
                'name' => 'Urutan',
                'sortable' => true,
                'class' => fn ($item) => $this->getRowClass($item).' text-center font-semibold w-16',
            ],
            [
                'key' => 'activity_name',
                'name' => 'Nama Aktivitas',
                'sortable' => true,
                'class' => fn ($item) => $this->getRowClass($item).' font-medium',
            ],
            [
                'key' => 'note',
                'name' => 'Catatan',
                'sortable' => false,
                'class' => fn ($item) => $this->getRowClass($item).' text-slate-500 dark:text-slate-400',
            ],
            [
                'name' => 'Status',
                'sortable' => false,
                'class' => fn ($item) => $this->getRowClass($item).' w-32',
                'render' => function ($item) {
                    $completed = $this->hasHistory($item->id);

                    if ($completed) {
                        return '<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-400">
                            <span class="material-symbols-outlined text-base align-middle">check_circle</span> Selesai
                        </span>';
                    }

                    return '<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-800 dark:bg-rose-950/30 dark:text-rose-400">
                        <span class="material-symbols-outlined text-base align-middle">cancel</span> Belum Selesai
                    </span>';
                },
            ],
        ];
    }

    /**
     * Get the query builder instance.
     */
    public function getQuery(): Builder
    {
        if (! $this->tanggal) {
            return HelperJobdeskRoutine::where('day', 'none');
        }

        $dayName = strtolower(Carbon::parse($this->tanggal)->locale('id')->dayName);

        return HelperJobdeskRoutine::where('day', $dayName);
    }

    /**
     * Get the template view file.
     */
    public function getView(): string
    {
        return 'livewire.helper-jobdesk.history.datatable';
    }
}
