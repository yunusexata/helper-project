<?php

namespace App\Repositories\Account;

use App\Repositories\MasterDataRepository;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Permission;

class PermissionRepository extends MasterDataRepository
{
    /**
     * Get the class name of the associated Eloquent model.
     */
    protected static function className(): string
    {
        return Permission::class;
    }

    /**
     * Get a collection of permissions containing only ID and Name.
     */
    public static function getIdAndNames()
    {
        return Permission::select('id', 'name')->get();
    }

    /**
     * Find a permission by its name.
     */
    public static function findByName(string $name): ?Permission
    {
        return Permission::where('name', $name)->first();
    }

    /**
     * Get the query builder for permissions datatable.
     */
    public static function datatable(): Builder
    {
        return Permission::query();
    }
}
