<?php

namespace App\Livewire\Account\Role;

use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Manajemen Jabatan')]
class Index extends Component
{
    public function render()
    {
        return view('livewire.account.role.index');
    }
}
