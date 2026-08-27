<?php

namespace App\Livewire\Account\User;

use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Manajemen Pengguna')]
class Index extends Component
{
    public function render()
    {
        return view('livewire.account.user.index');
    }
}
