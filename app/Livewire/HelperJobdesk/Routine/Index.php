<?php

namespace App\Livewire\HelperJobdesk\Routine;

use App\Models\HelperJobdeskRoutine;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Daily Jobdesk Management')]
class Index extends Component
{
    /**
     * The active day.
     */
    public string $day = 'senin';

    /**
     * The ID of the routine being edited.
     */
    public ?int $editingRoutineId = null;

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
    public function openAddModal(): void
    {
        $this->resetErrorBag();
        $this->editingRoutineId = null;
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
     * Save the routine (create or update) and normalize orders.
     */
    public function saveRoutine(): void
    {
        $this->validate();

        if ($this->editingRoutineId) {
            $routine = HelperJobdeskRoutine::findOrFail($this->editingRoutineId);
            $routine->update([
                'activity_name' => $this->activity_name,
                'note' => $this->note,
                'order' => $this->order,
            ]);
        } else {
            $routine = HelperJobdeskRoutine::create([
                'day' => $this->day,
                'activity_name' => $this->activity_name,
                'note' => $this->note,
                'order' => $this->order,
            ]);
        }

        // Fetch all other routines to dynamically splice and re-sequence
        $routines = HelperJobdeskRoutine::where('day', $this->day)
            ->where('id', '!=', $routine->id)
            ->orderBy('order')
            ->get()
            ->all();

        $targetIndex = max(0, min(count($routines), $this->order - 1));
        array_splice($routines, $targetIndex, 0, [$routine]);

        // Normalize orders consecutively
        foreach ($routines as $index => $item) {
            $item->update(['order' => $index + 1]);
        }

        $this->closeModal();
    }

    /**
     * Handle drag and drop reordering and normalize orders consecutively.
     */
    public function updateOrder(int $id, int $position): void
    {
        $routine = HelperJobdeskRoutine::findOrFail($id);

        $routines = HelperJobdeskRoutine::where('day', $routine->day)
            ->orderBy('order')
            ->get();

        $items = $routines->reject(fn ($r) => $r->id === $id)->values()->all();

        array_splice($items, $position, 0, [$routine]);

        // Save and normalize orders
        foreach ($items as $index => $item) {
            $newOrder = $index + 1;
            if ($item->order !== $newOrder) {
                $item->update(['order' => $newOrder]);
            }
        }
    }

    /**
     * Get the routines for the current day, ordered sequentially.
     *
     * @return Collection<int, HelperJobdeskRoutine>
     */
    public function getRoutinesProperty(): Collection
    {
        return HelperJobdeskRoutine::where('day', $this->day)
            ->orderBy('order')
            ->get();
    }

    /**
     * Render the component view.
     */
    public function render(): View
    {
        return view('livewire.helper-jobdesk.routine.index', [
            'routines' => $this->getRoutinesProperty(),
        ]);
    }
}
