<?php

namespace App\Livewire\HelperJobdesk\Routine;

use App\Helpers\Alert;
use App\Repositories\Account\UserRepository;
use App\Repositories\HelperJobdeskRoutine\HelperJobdeskRoutineRepository;
use App\Repositories\ListPosting\TemplatePostingRepository;
use App\Traits\Livewire\WithDatatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Crypt;
use Livewire\Attributes\On;
use Livewire\Component;

class Datatable extends Component
{
    use WithDatatable;

    public $isCanUpdate;

    public $isCanDelete;

    public $isCanUpdateDetail;

    // Delete Dialog
    public $targetDeleteId;

    public function onMount()
    {
        // $authUser = UserRepository::authenticatedUser();
        $this->isCanUpdate = true;
        $this->isCanDelete = true;

        $this->sortBy = 'created_at';
        $this->sortDirection = 'DESC';
    }

    #[On('on-delete-dialog-confirm')]
    public function onDialogDeleteConfirm()
    {
        if (! $this->isCanDelete || $this->targetDeleteId == null) {
            return;
        }

        // TemplatePostingRepository::delete($this->targetDeleteId);
        Alert::success($this, 'Berhasil', 'Data berhasil dihapus');
    }

    #[On('on-delete-dialog-cancel')]
    public function onDialogDeleteCancel()
    {
        $this->targetDeleteId = null;
    }

    public function showDeleteDialog($id)
    {
        $this->targetDeleteId = $id;

        Alert::confirmation(
            $this,
            Alert::ICON_QUESTION,
            'Hapus Data',
            'Apakah Anda Yakin Ingin Menghapus Data Ini ?',
            'on-delete-dialog-confirm',
            'on-delete-dialog-cancel',
            'Hapus',
            'Batal',
        );
    }

    #[On('refresh-table')]
    public function refreshTable()
    {
        $this->resetPage();
    }

    public function getColumns(): array
    {
        return [
            [
                'name' => 'Action',
                'sortable' => false,
                'searchable' => false,
                'render' => function ($item) {
                    $editHtml = '';

                    $id = Crypt::encrypt($item->id);

                    $destroyHtml = '';
                    $destroyHtml = '';
                    if ($this->isCanDelete) {
                        $destroyHtml = "<div class='col-auto'>
                            <button type='button' class='p-0 hover:bg-error/10 text-error rounded transition-colors' wire:click=\"showDeleteDialog($item->id)\">
                                <span class='material-symbols-outlined text-lg' data-icon='delete'>delete</span>
                            </button>
                        </div>";
                    }

                    $html = "<div class='row p-0 m-0 d-flex justify-content-start flex-nowrap'>
                        $editHtml $destroyHtml
                    </div>";

                    return $html;
                },
            ],
            [
                'key' => 'activity_name',
                'name' => 'Nama Aktivitas',
            ],
        ];
    }

    public function getQuery(): Builder
    {
        return HelperJobdeskRoutineRepository::datatable();
    }

    public function getView(): string
    {
        return 'livewire.helper-jobdesk.routine.datatable';
    }
}
