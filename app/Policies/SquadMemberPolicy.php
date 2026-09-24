<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\SquadMember;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class SquadMemberPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @return Response|bool
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the model.
     *
     * @return Response|bool
     */
    public function view(User $user, SquadMember $squadMember)
    {
        return $user->clubhouse_id === $squadMember->category->squad->teamRound->clubhouse_id;
    }

    /**
     * Determine whether the user can create models.
     *
     * @return Response|bool
     */
    public function create(User $user) {}

    /**
     * Determine whether the user can update the model.
     *
     * @return Response|bool
     */
    public function update(User $user, SquadMember $squadMember)
    {
        return $user->clubhouse_id === $squadMember->category->squad->teamRound->clubhouse_id && $user->hasPermissionTo(Permission::EDIT_TEAMROUNDS);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @return Response|bool
     */
    public function delete(User $user, SquadMember $squadMember)
    {
        return $user->clubhouse_id === $squadMember->category->squad->teamRound->clubhouse_id && $user->hasPermissionTo(Permission::EDIT_TEAMROUNDS);
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @return Response|bool
     */
    public function restore(User $user, SquadMember $squadMember)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @return Response|bool
     */
    public function forceDelete(User $user, SquadMember $squadMember)
    {
        //
    }
}
