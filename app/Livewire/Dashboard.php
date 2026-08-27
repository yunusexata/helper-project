<?php

namespace App\Livewire;

use App\Models\EmployeeWhitelist;
use App\Models\HelperJobdeskDailyHistory;
use App\Models\HelperJobdeskDailyHistoryAttachment;
use App\Models\HelperJobdeskRoutine;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Title('Dashboard')]
class Dashboard extends Component
{
    use WithFileUploads;

    /**
     * Helper specific properties.
     */
    public Collection $routines;

    public ?int $selectedRoutineId = null;

    public string $note = '';

    public array $attachments = [];

    public ?EmployeeWhitelist $whitelist = null;

    /**
     * Admin/Staff specific properties.
     */
    public ?int $adminSelectedHelperId = null;

    public Collection $helpersList;

    public Collection $adminRoutines;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->routines = collect();
        $this->adminRoutines = collect();
        $this->helpersList = collect();

        $user = auth()->user();

        if ($user->hasRole('Helper')) {
            $this->whitelist = EmployeeWhitelist::where('email', $user->email)->first();
            $this->loadHelperRoutines();
            $this->selectNextIncomplete();
        } else {
            $this->helpersList = User::role('Helper')->get();
            $this->adminSelectedHelperId = $this->helpersList->first()?->id;
            $this->loadAdminRoutines();
        }
    }

    /**
     * Load today's routines with completion statuses for the logged-in helper.
     */
    public function loadHelperRoutines(): void
    {
        if (! $this->whitelist) {
            return;
        }

        $todayDayName = strtolower(now()->locale('id')->dayName);
        $routines = HelperJobdeskRoutine::where('day', $todayDayName)
            ->orderBy('order')
            ->get();

        foreach ($routines as $routine) {
            $history = HelperJobdeskDailyHistory::where('employee_whitelists_id', $this->whitelist->id)
                ->where('subject_id', $routine->id)
                ->where('subject_type', HelperJobdeskRoutine::class)
                ->whereDate('created_at', today())
                ->first();

            $routine->is_completed = ! empty($history) && ! empty($history->finish_at);
            $routine->is_in_progress = ! empty($history) && empty($history->finish_at);
            $routine->history_id = $history?->id;
            $routine->start_at = $history?->start_at;
            $routine->finish_at = $history?->finish_at;
            $routine->logged_note = $history?->note;
            $routine->attachments = $history ? $history->attachments : collect();
        }

        $this->routines = $routines;
    }

    /**
     * Load today's routines progress for the admin-selected helper.
     */
    public function loadAdminRoutines(): void
    {
        if (! $this->adminSelectedHelperId) {
            $this->adminRoutines = collect();

            return;
        }

        $selectedHelper = User::find($this->adminSelectedHelperId);
        if (! $selectedHelper) {
            $this->adminRoutines = collect();

            return;
        }

        $helperWhitelist = EmployeeWhitelist::where('email', $selectedHelper->email)->first();
        if (! $helperWhitelist) {
            $this->adminRoutines = collect();

            return;
        }

        $todayDayName = strtolower(now()->locale('id')->dayName);
        $routines = HelperJobdeskRoutine::where('day', $todayDayName)
            ->orderBy('order')
            ->get();

        foreach ($routines as $routine) {
            $history = HelperJobdeskDailyHistory::where('employee_whitelists_id', $helperWhitelist->id)
                ->where('subject_id', $routine->id)
                ->where('subject_type', HelperJobdeskRoutine::class)
                ->whereDate('created_at', today())
                ->first();

            $routine->is_completed = ! empty($history) && ! empty($history->finish_at);
            $routine->start_at = $history?->start_at;
            $routine->finish_at = $history?->finish_at;
            $routine->logged_note = $history?->note;
            $routine->attachments = $history ? $history->attachments : collect();
        }

        $this->adminRoutines = $routines;
    }

    /**
     * Helper list updated trigger.
     */
    public function updatedAdminSelectedHelperId(): void
    {
        $this->loadAdminRoutines();
    }

    /**
     * Select a specific routine to view.
     */
    public function selectRoutine(int $id): void
    {
        $this->selectedRoutineId = $id;
        $this->note = '';
        $this->attachments = [];
        $this->resetErrorBag();
    }

    /**
     * Automatically select the first incomplete routine on the list.
     */
    public function selectNextIncomplete(): void
    {
        $firstIncomplete = $this->routines->first(fn ($r) => ! $r->is_completed);

        if ($firstIncomplete) {
            $this->selectRoutine($firstIncomplete->id);
        } elseif ($this->routines->isNotEmpty()) {
            $this->selectRoutine($this->routines->first()->id);
        }
    }

    /**
     * Start the selected task (Mulai Kerja).
     */
    public function startTask(int $id): void
    {
        if (! $this->whitelist) {
            return;
        }

        HelperJobdeskDailyHistory::create([
            'employee_whitelists_id' => $this->whitelist->id,
            'employee_whitelists_name' => $this->whitelist->name,
            'subject_id' => $id,
            'subject_type' => HelperJobdeskRoutine::class,
            'start_at' => now(),
            'finish_at' => null,
        ]);

        $this->loadHelperRoutines();
        $this->selectRoutine($id);
    }

    /**
     * Complete the selected task (Selesai Kerja).
     */
    public function completeTask(int $id, bool $direct = false): void
    {
        if (! $this->whitelist) {
            return;
        }

        $this->validate([
            'note' => 'nullable|string|max:1000',
            'attachments.*' => 'nullable|image|max:5120', // Limit to 5MB images
        ]);

        if ($direct) {
            // Direct completion setting start_at == finish_at == now
            $history = HelperJobdeskDailyHistory::create([
                'employee_whitelists_id' => $this->whitelist->id,
                'employee_whitelists_name' => $this->whitelist->name,
                'subject_id' => $id,
                'subject_type' => HelperJobdeskRoutine::class,
                'start_at' => now(),
                'finish_at' => now(),
                'note' => $this->note ?: null,
            ]);
        } else {
            // Completed after started, update the existing in-progress history row
            $history = HelperJobdeskDailyHistory::where('employee_whitelists_id', $this->whitelist->id)
                ->where('subject_id', $id)
                ->where('subject_type', HelperJobdeskRoutine::class)
                ->whereNull('finish_at')
                ->whereDate('created_at', today())
                ->first();

            if ($history) {
                $history->update([
                    'finish_at' => now(),
                    'note' => $this->note ?: null,
                ]);
            } else {
                // Fallback direct insert if in-progress row was not found
                $history = HelperJobdeskDailyHistory::create([
                    'employee_whitelists_id' => $this->whitelist->id,
                    'employee_whitelists_name' => $this->whitelist->name,
                    'subject_id' => $id,
                    'subject_type' => HelperJobdeskRoutine::class,
                    'start_at' => now(),
                    'finish_at' => now(),
                    'note' => $this->note ?: null,
                ]);
            }
        }

        // Save uploaded photos
        foreach ($this->attachments as $attachment) {
            $path = $attachment->store('daily-history-attachments', 'public');
            HelperJobdeskDailyHistoryAttachment::create([
                'helper_jobdesk_daily_histories' => $history->id,
                'disk' => 'public',
                'path' => $path,
            ]);
        }

        $this->loadHelperRoutines();
        $this->selectNextIncomplete();
    }

    /**
     * Render the dashboard layout view.
     */
    public function render(): View
    {
        if (auth()->user()->hasRole('Helper')) {
            return view('livewire.helper-dashboard');
        }

        return view('livewire.default-dashboard');
    }
}
