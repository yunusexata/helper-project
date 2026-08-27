<?php

use App\Livewire\Account\User\Detail;
use App\Livewire\Dashboard;
use App\Livewire\HelperJobdesk\History\Index;
use App\Livewire\HelperJobdesk\Routine\Index as RoutineIndex;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', Dashboard::class)->name('dashboard');
    Route::get('daily-jobdesk', RoutineIndex::class)->name('helper_jobdesk_routine.index');
    Route::get('daily-jobdesk/history', Index::class)->name('helper_jobdesk_history.index');

    // User Management
    Route::get('account/user', App\Livewire\Account\User\Index::class)->name('user.index');
    Route::get('account/user/create', Detail::class)->name('user.create');
    Route::get('account/user/{objId}/edit', Detail::class)->name('user.edit');

    // Role Management
    Route::get('account/role', App\Livewire\Account\Role\Index::class)->name('role.index');
    Route::get('account/role/create', App\Livewire\Account\Role\Detail::class)->name('role.create');
    Route::get('account/role/{objId}/edit', App\Livewire\Account\Role\Detail::class)->name('role.edit');

    // Permission Management
    Route::get('account/permission', App\Livewire\Account\Permission\Index::class)->name('permission.index');
    Route::get('account/permission/create', App\Livewire\Account\Permission\Detail::class)->name('permission.create');
    Route::get('account/permission/{objId}/edit', App\Livewire\Account\Permission\Detail::class)->name('permission.edit');
});

require __DIR__.'/settings.php';
