<?php

namespace App\Livewire\Account\Permission;

use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Manajemen Hak Akses')]
class Index extends Component
{
    public function render()
    {
        return view('livewire.account.permission.index');
    }
}
