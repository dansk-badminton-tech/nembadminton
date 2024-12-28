<?php

namespace App\Policies;

use App\Models\Cancellation;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CancellationPolicy
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
    public function view(User $user, Cancellation $cancellation): bool
    {
        if ($cancellation->team !== null) {
            return $cancellation->team->user_id === $user->getAuthIdentifier();
        }

        if($cancellation->cancellationCollector !== null) {
            return $cancellation->cancellationCollector->user_id === $user->getAuthIdentifier();
        }

        return false;
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
    public function update(User $user, Cancellation $cancellation): bool
    {
        //
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Cancellation $cancellation): bool
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Cancellation $cancellation): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Cancellation $cancellation): bool
    {
        //
    }
}