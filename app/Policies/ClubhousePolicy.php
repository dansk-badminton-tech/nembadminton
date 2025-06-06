<?php

namespace App\Policies;

use App\Models\Clubhouse;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ClubhousePolicy
{

    public function viewUsers(User $actor, Clubhouse $target): bool
    {
        return $actor->hasPermissionTo("edit clubhouse");
    }

    public function viewInvitations(User $actor, Clubhouse $target): bool
    {
        return $actor->hasPermissionTo("edit clubhouse");
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Clubhouse $clubhouse): bool
    {
        return $clubhouse->id === $user->clubhouse_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Clubhouse $clubhouse): bool
    {
        return $user->clubhouse_id === $clubhouse->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Clubhouse $clubhouse): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Clubhouse $clubhouse): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Clubhouse $clubhouse): bool
    {
        return false;
    }
}
