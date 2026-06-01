<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Team;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TeamPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Team $team): bool
    {
        return $user->clubhouse_id === $team->clubhouse_id && $user->hasPermissionTo(Permission::VIEW_TEAMS);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::CREATE_TEAMS);
    }

    public function update(User $user, Team $team): bool
    {
        return $user->clubhouse_id === $team->clubhouse_id && $user->hasPermissionTo(Permission::EDIT_TEAMS);
    }

    public function delete(User $user, Team $team): bool
    {
        return $user->clubhouse_id === $team->clubhouse_id && $user->hasPermissionTo(Permission::DELETE_TEAMS);
    }
}
