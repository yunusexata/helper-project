<?php

namespace App\Repositories\Account;

use App\Models\User;
use App\Repositories\MasterDataRepository;
use Illuminate\Database\Eloquent\Builder;

class UserRepository extends MasterDataRepository
{
    /**
     * Get the class name of the associated Eloquent model.
     */
    protected static function className(): string
    {
        return User::class;
    }

    /**
     * Get the currently authenticated user.
     */
    public static function authenticatedUser(): ?User
    {
        return auth()->user();
    }

    /**
     * Find a user by their email address.
     */
    public static function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    /**
     * Get the query builder for users datatable, optionally filtered by role.
     */
    public static function datatable(?string $roleName = null): Builder
    {
        $query = User::query()->with('roles');

        if ($roleName) {
            $query->role($roleName);
        }

        return $query;
    }
}
