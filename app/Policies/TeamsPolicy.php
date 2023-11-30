<?php

namespace App\Policies;

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
        //
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        //
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Teams $teams): bool
    {
        return $teams->user_id === $user->getAuthIdentifier();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Teams $teams): bool
    {
        //
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
