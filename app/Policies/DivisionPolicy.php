<?php

namespace App\Policies;

use App\Models\Division;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DivisionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Everyone can view divisions
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Division $division): bool
    {
        return true; // Everyone can view individual divisions
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only authenticated users with a clubhouse can create divisions
        return $user->clubhouse_id !== null;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Division $division): bool
    {
        // System divisions can only be updated by system (return false for now)
        if ($division->created_by_system) {
            return false;
        }

        // Clubhouse divisions can only be updated by their creator
        return $user->clubhouse_id === $division->clubhouse_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Division $division): bool
    {
        // System divisions cannot be deleted
        if ($division->created_by_system) {
            return false;
        }

        // Clubhouse divisions can only be deleted by their creator
        return $user->clubhouse_id === $division->clubhouse_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Division $division): bool
    {
        // Same logic as delete
        return $this->delete($user, $division);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Division $division): bool
    {
        // Same logic as delete
        return $this->delete($user, $division);
    }
}
