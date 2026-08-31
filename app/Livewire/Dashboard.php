<?php

namespace App\Livewire;

use App\Helpers\Alert;
use App\Models\EmployeeWhitelist;
use App\Models\HelperJobdeskDailyHistory;
use App\Models\HelperJobdeskDailyHistoryAttachment;
use App\Models\HelperJobdeskRequest;
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
     * Helper specific routine properties.
     */
    public Collection $groupedRoutines;

    public ?string $selectedGroupName = null;

    public string $note = '';

    public array $attachments = [];

    public ?EmployeeWhitelist $whitelist = null;

    /**
     * Helper specific request properties.
     */
    public string $requestActivityName = '';

    public string $requestNote = '';

    public array $requestAttachments = [];

    public ?HelperJobdeskRequest $activeRequest = null;

    public ?HelperJobdeskDailyHistory $activeRequestHistory = null;

    public Collection $todayRequests;

    /**
     * Admin/Staff specific properties.
     */
    public ?int $adminSelectedHelperId = null;

    public string $adminSelectedTanggal = '';

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
        $this->todayRequests = collect();
        $this->adminSelectedTanggal = now()->format('Y-m-d');

        $user = auth()->user();
        $isHelper = false;

        try {
            $isHelper = $user && $user->hasRole('Helper');
        } catch (\Throwable) {
            $isHelper = false;
        }

        if ($isHelper) {
            $this->whitelist = EmployeeWhitelist::where('email', $user->email)->first();
            $this->loadHelperRoutines();
            $this->selectNextIncomplete();
            $this->loadHelperRequests();
        } else {
            try {
                $this->helpersList = User::role('Helper')->get();
            } catch (\Throwable) {
                $this->helpersList = collect();
            }
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
     * Load today's requests and any currently in-progress request for the helper.
     */
    public function loadHelperRequests(): void
    {
        if (! $this->whitelist) {
            $this->activeRequest = null;
            $this->activeRequestHistory = null;
            $this->todayRequests = collect();

            return;
        }

        $requests = HelperJobdeskRequest::where('employee_whitelists_id', $this->whitelist->id)
            ->whereDate('created_at', today())
            ->with(['dailyHistories.attachments'])
            ->latest()
            ->get();

        $activeReq = null;
        $activeHist = null;

        foreach ($requests as $req) {
            $inProgressHistory = $req->dailyHistories
                ->whereNull('finish_at')
                ->where('employee_whitelists_id', $this->whitelist->id)
                ->first();

            if ($inProgressHistory && ! $activeReq) {
                $activeReq = $req;
                $activeHist = $inProgressHistory;
            }
        }

        $this->activeRequest = $activeReq;
        $this->activeRequestHistory = $activeHist;
        $this->todayRequests = $requests;
    }

    /**
     * Start a new ad-hoc jobdesk request.
     */
    public function startRequest(): void
    {
        if (! $this->whitelist) {
            return;
        }

        // Prevent starting a new request if one is currently in-progress
        if ($this->activeRequest) {
            Alert::information($this, 'Ada pekerjaan request yang sedang berjalan.', 2500, Alert::ICON_WARNING);

            return;
        }

        $this->validate([
            'requestActivityName' => 'required|string|min:3|max:1000',
        ], [
            'requestActivityName.required' => 'Nama pekerjaan request wajib diisi.',
            'requestActivityName.min' => 'Nama pekerjaan minimal 3 karakter.',
        ]);

        $request = HelperJobdeskRequest::create([
            'day' => strtolower(now()->locale('id')->dayName),
            'activity_name' => trim($this->requestActivityName),
            'note' => null,
            'employee_whitelists_id' => $this->whitelist->id,
            'employee_whitelists_name' => $this->whitelist->name,
        ]);

        HelperJobdeskDailyHistory::create([
            'employee_whitelists_id' => $this->whitelist->id,
            'employee_whitelists_name' => $this->whitelist->name,
            'subject_id' => $request->id,
            'subject_type' => HelperJobdeskRequest::class,
            'start_at' => now(),
            'finish_at' => null,
        ]);

        $this->requestActivityName = '';
        $this->requestNote = '';
        $this->requestAttachments = [];
        $this->resetErrorBag();

        $this->loadHelperRequests();
        Alert::information($this, 'Pekerjaan request dimulai. Timer sedang berjalan.');
    }

    /**
     * Complete the currently active jobdesk request.
     */
    public function completeRequest(): void
    {
        if (! $this->whitelist || ! $this->activeRequest || ! $this->activeRequestHistory) {
            return;
        }

        $this->validate([
            'requestNote' => 'nullable|string|max:1000',
            'requestAttachments.*' => 'nullable|image|max:5120',
        ]);

        $this->activeRequestHistory->update([
            'finish_at' => now(),
            'note' => $this->requestNote ? trim($this->requestNote) : null,
        ]);

        // Save uploaded photos
        foreach ($this->requestAttachments as $attachment) {
            $path = $attachment->store('daily-history-attachments', 'public');
            HelperJobdeskDailyHistoryAttachment::create([
                'helper_jobdesk_daily_histories' => $this->activeRequestHistory->id,
                'disk' => 'public',
                'path' => $path,
            ]);
        }

        $this->requestNote = '';
        $this->requestAttachments = [];
        $this->resetErrorBag();

        $this->loadHelperRequests();
        Alert::information($this, 'Pekerjaan request berhasil diselesaikan.');
    }

    /**
     * Cancel the currently active jobdesk request.
     */
    public function cancelRequest(): void
    {
        if (! $this->whitelist || ! $this->activeRequest || ! $this->activeRequestHistory) {
            return;
        }

        $this->activeRequestHistory->delete();
        $this->activeRequest->delete();

        $this->requestNote = '';
        $this->requestAttachments = [];
        $this->resetErrorBag();

        $this->loadHelperRequests();
        Alert::information($this, 'Pekerjaan request telah dibatalkan.');
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
        Alert::information($this, 'Tugas dimulai. Timer sedang berjalan.');
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
        Alert::information($this, 'Laporan tugas berhasil disimpan.');
    }

    /**
     * Render the dashboard.
     */
    public function render(): View
    {
        $isHelper = false;
        try {
            $isHelper = auth()->user()?->hasRole('Helper') ?? false;
        } catch (\Throwable) {
            $isHelper = false;
        }

        if ($isHelper) {
            return view('livewire.helper-dashboard');
        }

        return view('livewire.default-dashboard');
    }
}
