<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Teams;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TeamsPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        //
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Teams $teams): bool
    {
        return $user->clubhouse_id === $teams->clubhouse_id && $user->hasPermissionTo(Permission::VIEW_TEAMROUNDS);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::CREATE_TEAMROUNDS);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Teams $teams): bool
    {
        return $user->clubhouse_id === $teams->clubhouse_id && $user->hasPermissionTo(Permission::EDIT_TEAMROUNDS);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Teams $teams): bool
    {
        return $user->clubhouse_id === $teams->clubhouse_id && $user->hasPermissionTo(Permission::DELETE_TEAMROUNDS);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Teams $teams): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Teams $teams): bool
    {
        //
    }
}
