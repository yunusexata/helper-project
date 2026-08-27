<?php

namespace App\Livewire\HelperJobdesk\History;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Riwayat Jobdesk')]
class Index extends Component
{
    /**
     * Selected Helper user ID.
     */
    public ?int $petugasId = null;

    /**
     * Selected date filter (YYYY-MM-DD).
     */
    public ?string $tanggal = null;

    /**
     * List of all helpers.
     */
    public Collection $petugasList;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->petugasList = User::role('Helper')->get();
        $this->petugasId = $this->petugasList->first()?->id;
        $this->tanggal = now()->format('Y-m-d');
    }

    /**
     * Render the component view.
     */
    public function render(): View
    {
        return view('livewire.helper-jobdesk.history.index');
    }
}
