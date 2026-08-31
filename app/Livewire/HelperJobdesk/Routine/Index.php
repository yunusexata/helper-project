<?php

namespace App\Livewire\HelperJobdesk\Routine;

use App\Helpers\Alert;
use App\Models\HelperJobdeskRoutine;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Daily Jobdesk Management')]
class Index extends Component
{
    /**
     * The active day filter.
     */
    public string $day = 'senin';

    /**
     * The ID of the routine being edited.
     */
    public ?int $editingRoutineId = null;

    /**
     * Form field: task group name.
     */
    public string $task_group = '';

    /**
     * Form field: activity name.
     */
    public string $activity_name = '';

    /**
     * Form field: activity note.
     */
    public string $note = '';

    /**
     * Form field: routine sequence order.
     */
    public int $order = 1;

    /**
     * Validation rules.
     */
    protected array $rules = [
        'task_group' => 'required|string|max:100',
        'activity_name' => 'required|string|max:255',
        'note' => 'nullable|string|max:1000',
        'order' => 'required|integer|min:1',
    ];

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->day = strtolower(now()->locale('id')->dayName);
    }

    /**
     * Open the modal in create mode.
     */
    public function openAddModal(?string $group = null): void
    {
        $this->resetErrorBag();
        $this->editingRoutineId = null;
        $this->task_group = $group ?? '';
        $this->activity_name = '';
        $this->note = '';

        $maxOrder = HelperJobdeskRoutine::where('day', $this->day)->max('order') ?? 0;
        $this->order = $maxOrder + 1;
        $this->dispatch('open-regular-modal');
    }

    /**
     * Open the modal in edit mode.
     */
    public function openEditModal(int $id): void
    {
        $this->resetErrorBag();
        $routine = HelperJobdeskRoutine::findOrFail($id);

        $this->editingRoutineId = $id;
        $this->task_group = $routine->task_group ?? '';
        $this->activity_name = $routine->activity_name;
        $this->note = $routine->note ?? '';
        $this->order = $routine->order;
        $this->dispatch('open-regular-modal');
    }

    /**
     * Close the active modal.
     */
    public function closeModal(): void
    {
        $this->dispatch('close-regular-modal');
    }

    /**
     * Save the routine (create or update) and normalize orders consecutively.
     */
    public function saveRoutine(): void
    {
        $this->validate();

        $data = [
            'day' => $this->day,
            'task_group' => trim($this->task_group),
            'activity_name' => trim($this->activity_name),
            'note' => $this->note ? trim($this->note) : null,
            'order' => $this->order,
        ];

        if ($this->editingRoutineId) {
            $routine = HelperJobdeskRoutine::findOrFail($this->editingRoutineId);
            $routine->update($data);
        } else {
            $routine = HelperJobdeskRoutine::create($data);
        }

        // Fetch all other routines on this day to dynamically splice and re-sequence
        $routines = HelperJobdeskRoutine::where('day', $this->day)
            ->where('id', '!=', $routine->id)
            ->orderBy('order')
            ->get()
            ->all();

        $targetIndex = max(0, min(count($routines), $this->order - 1));
        array_splice($routines, $targetIndex, 0, [$routine]);

        // Normalize orders consecutively
        foreach ($routines as $index => $item) {
            if ($item->order !== $index + 1) {
                $item->update(['order' => $index + 1]);
            }
        }

        $this->closeModal();
        Alert::information($this, 'Aktivitas rutinitas berhasil disimpan.');
    }

    /**
     * Delete an individual routine activity and normalize order.
     */
    public function deleteRoutine(int $id): void
    {
        $routine = HelperJobdeskRoutine::findOrFail($id);
        $day = $routine->day;
        $routine->delete();

        // Normalize remaining orders
        $remaining = HelperJobdeskRoutine::where('day', $day)->orderBy('order')->get();
        foreach ($remaining as $index => $item) {
            if ($item->order !== $index + 1) {
                $item->update(['order' => $index + 1]);
            }
        }

        Alert::information($this, 'Aktivitas berhasil dihapus.');
    }

    /**
     * Delete an entire task group on the current day.
     */
    public function deleteGroup(string $groupName): void
    {
        HelperJobdeskRoutine::where('day', $this->day)
            ->where('task_group', $groupName)
            ->delete();

        // Normalize remaining orders
        $remaining = HelperJobdeskRoutine::where('day', $this->day)->orderBy('order')->get();
        foreach ($remaining as $index => $item) {
            if ($item->order !== $index + 1) {
                $item->update(['order' => $index + 1]);
            }
        }

        Alert::information($this, "Grup {$groupName} dan seluruh aktivitasnya berhasil dihapus.");
    }

    /**
     * Get the routines for the current day grouped by task_group.
     */
    public function getGroupedRoutinesProperty(): Collection|\Illuminate\Support\Collection
    {
        return HelperJobdeskRoutine::where('day', $this->day)
            ->orderBy('order')
            ->get()
            ->groupBy('task_group');
    }

    /**
     * Get unique task_group names for auto-complete suggestions.
     */
    public function getExistingGroupsProperty(): array
    {
        return HelperJobdeskRoutine::where('day', $this->day)
            ->whereNotNull('task_group')
            ->distinct()
            ->pluck('task_group')
            ->sort()
            ->values()
            ->toArray();
    }

    /**
     * Render the component view.
     */
    public function render(): View
    {
        $grouped = $this->groupedRoutines;
        $totalActivities = $grouped->flatten()->count();
        $totalGroups = $grouped->count();

        return view('livewire.helper-jobdesk.routine.index', [
            'groupedRoutines' => $grouped,
            'existingGroups' => $this->existingGroups,
            'totalActivities' => $totalActivities,
            'totalGroups' => $totalGroups,
        ]);
    }
}
