<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\TeamRound;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TeamRoundPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {

    }

    public function view(User $user, TeamRound $teamRound): bool
    {
        return $user->clubhouse_id === $teamRound->clubhouse_id && $user->hasPermissionTo(Permission::VIEW_TEAMROUNDS);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::CREATE_TEAMROUNDS);
    }

    public function update(User $user, TeamRound $teamRound): bool
    {
        return $user->clubhouse_id === $teamRound->clubhouse_id && $user->hasPermissionTo(Permission::EDIT_TEAMROUNDS);
    }

    public function delete(User $user, TeamRound $teamRound): bool
    {
        return $user->clubhouse_id === $teamRound->clubhouse_id && $user->hasPermissionTo(Permission::DELETE_TEAMROUNDS);
    }

    public function restore(User $user, TeamRound $teamRound): bool
    {
    }

    public function forceDelete(User $user, TeamRound $teamRound): bool
    {
    }
}
