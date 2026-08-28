<?php

namespace App\Livewire;

use App\Helpers\Alert;
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
    public Collection $groupedRoutines;

    public ?string $selectedGroupName = null;

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
        $this->groupedRoutines = collect();
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
     * Load today's routines grouped by task_group for the helper.
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

        $grouped = $routines->groupBy('task_group');
        $processedGroups = collect();

        foreach ($grouped as $groupName => $activities) {
            $firstActivity = $activities->first();

            $history = HelperJobdeskDailyHistory::where('employee_whitelists_id', $this->whitelist->id)
                ->where('subject_id', $firstActivity->id)
                ->where('subject_type', HelperJobdeskRoutine::class)
                ->whereDate('created_at', today())
                ->first();

            $isCompleted = ! empty($history) && ! empty($history->finish_at);
            $isInProgress = ! empty($history) && empty($history->finish_at);

            $processedGroups->put($groupName, (object) [
                'name' => $groupName,
                'activities' => $activities,
                'is_completed' => $isCompleted,
                'is_in_progress' => $isInProgress,
                'history_id' => $history?->id,
                'start_at' => $history?->start_at,
                'finish_at' => $history?->finish_at,
                'logged_note' => $history?->note,
                'attachments' => $history ? $history->attachments : collect(),
                'first_activity_id' => $firstActivity->id,
            ]);
        }

        $this->groupedRoutines = $processedGroups;
    }

    /**
     * Load today's routines progress grouped by task_group for the admin view.
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

        $grouped = $routines->groupBy('task_group');
        $processedGroups = collect();

        foreach ($grouped as $groupName => $activities) {
            $firstActivity = $activities->first();

            $history = HelperJobdeskDailyHistory::where('employee_whitelists_id', $helperWhitelist->id)
                ->where('subject_id', $firstActivity->id)
                ->where('subject_type', HelperJobdeskRoutine::class)
                ->whereDate('created_at', today())
                ->first();

            $isCompleted = ! empty($history) && ! empty($history->finish_at);

            $processedGroups->put($groupName, (object) [
                'name' => $groupName,
                'activities' => $activities,
                'is_completed' => $isCompleted,
                'start_at' => $history?->start_at,
                'finish_at' => $history?->finish_at,
                'logged_note' => $history?->note,
                'attachments' => $history ? $history->attachments : collect(),
            ]);
        }

        $this->adminRoutines = $processedGroups;
    }

    /**
     * Trigger when the selected helper changes in the admin dropdown.
     */
    public function updatedAdminSelectedHelperId(): void
    {
        $this->loadAdminRoutines();
    }

    /**
     * Select a specific group to view details.
     */
    public function selectGroup(string $groupName): void
    {
        $this->selectedGroupName = $groupName;
        $this->note = '';
        $this->attachments = [];
        $this->resetErrorBag();
    }

    /**
     * Automatically select the first incomplete group.
     */
    public function selectNextIncomplete(): void
    {
        $firstIncomplete = $this->groupedRoutines->first(fn ($g) => ! $g->is_completed);

        if ($firstIncomplete) {
            $this->selectGroup($firstIncomplete->name);
        } elseif ($this->groupedRoutines->isNotEmpty()) {
            $this->selectGroup($this->groupedRoutines->keys()->first());
        }
    }

    /**
     * Start the group task (Mulai Kerja).
     */
    public function startTask(int $firstActivityId): void
    {
        if (! $this->whitelist) {
            return;
        }

        // Prevent duplicate starts
        $exists = HelperJobdeskDailyHistory::where('employee_whitelists_id', $this->whitelist->id)
            ->where('subject_id', $firstActivityId)
            ->where('subject_type', HelperJobdeskRoutine::class)
            ->whereDate('created_at', today())
            ->exists();

        if ($exists) {
            return;
        }

        HelperJobdeskDailyHistory::create([
            'employee_whitelists_id' => $this->whitelist->id,
            'employee_whitelists_name' => $this->whitelist->name,
            'subject_id' => $firstActivityId,
            'subject_type' => HelperJobdeskRoutine::class,
            'start_at' => now(),
            'finish_at' => null,
        ]);

        $this->loadHelperRoutines();

        $group = $this->groupedRoutines->first(fn ($g) => $g->first_activity_id === $firstActivityId);
        if ($group) {
            $this->selectGroup($group->name);
        }
        Alert::success($this, 'Berhasil', 'Tugas dimulai. Timer sedang berjalan.');
    }

    /**
     * Complete the group task (Selesai Kerja).
     */
    public function completeTask(int $firstActivityId, bool $direct = false): void
    {
        if (! $this->whitelist) {
            return;
        }

        // Prevent duplicate completions
        if ($direct) {
            $exists = HelperJobdeskDailyHistory::where('employee_whitelists_id', $this->whitelist->id)
                ->where('subject_id', $firstActivityId)
                ->where('subject_type', HelperJobdeskRoutine::class)
                ->whereDate('created_at', today())
                ->first();

            if ($exists && $exists->finish_at) {
                return;
            }
        }

        $this->validate([
            'note' => 'nullable|string|max:1000',
            'attachments.*' => 'nullable|image|max:5120',
        ]);

        if ($direct) {
            $history = HelperJobdeskDailyHistory::create([
                'employee_whitelists_id' => $this->whitelist->id,
                'employee_whitelists_name' => $this->whitelist->name,
                'subject_id' => $firstActivityId,
                'subject_type' => HelperJobdeskRoutine::class,
                'start_at' => now(),
                'finish_at' => now(),
                'note' => $this->note ?: null,
            ]);
        } else {
            $history = HelperJobdeskDailyHistory::where('employee_whitelists_id', $this->whitelist->id)
                ->where('subject_id', $firstActivityId)
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
                $history = HelperJobdeskDailyHistory::create([
                    'employee_whitelists_id' => $this->whitelist->id,
                    'employee_whitelists_name' => $this->whitelist->name,
                    'subject_id' => $firstActivityId,
                    'subject_type' => HelperJobdeskRoutine::class,
                    'start_at' => now(),
                    'finish_at' => now(),
                    'note' => $this->note ?: null,
                ]);
            }
        }

        // Save attachments
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
        Alert::success($this, 'Berhasil', 'Laporan tugas berhasil disimpan.');
    }

    /**
     * Render the dashboard.
     */
    public function render(): View
    {
        if (auth()->user()->hasRole('Helper')) {
            return view('livewire.helper-dashboard');
        }

        return view('livewire.default-dashboard');
    }
}
