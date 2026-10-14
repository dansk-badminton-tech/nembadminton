<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\TeamRound;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TeamRoundPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool {}

    public function view(User $user, TeamRound $teamRound): bool
    {
        return $user->clubhouse_id === $teamRound->clubhouse_id && $user->hasPermissionTo(Permission::VIEW_TEAMROUNDS);
    }

    /**
     * The Season History shows the lineups of the Clubhouse's other TeamRounds, so it needs more than the public link:
     * a User of the Clubhouse who can see the TeamRound, which for a player means standing in its Official Lineup.
     */
    public function viewSeasonHistory(User $user, TeamRound $teamRound): bool
    {
        return $this->view($user, $teamRound) && TeamRound::query()->visibleToUser()->whereKey($teamRound->id)->exists();
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

    public function restore(User $user, TeamRound $teamRound): bool {}

    public function forceDelete(User $user, TeamRound $teamRound): bool {}
}
