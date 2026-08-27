<?php

namespace App\Repositories\Account;

use App\Repositories\MasterDataRepository;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Role;

class RoleRepository extends MasterDataRepository
{
    /**
     * Get the class name of the associated Eloquent model.
     */
    protected static function className(): string
    {
        return Role::class;
    }

    /**
     * Get a collection of roles containing only ID and Name.
     */
    public static function getIdAndNames()
    {
        return Role::select('id', 'name')->get();
    }

    /**
     * Get the query builder for roles datatable.
     */
    public static function datatable(): Builder
    {
        return Role::query()->withCount('permissions');
    }
}
