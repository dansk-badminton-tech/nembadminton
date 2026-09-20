<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permission;
use App\Models\TeamRoundScenario;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TeamRoundScenarioPolicy
{
    use HandlesAuthorization;

    public function view(User $user, TeamRoundScenario $scenario): bool
    {
        return $user->clubhouse_id === $scenario->teamRound->clubhouse_id && $user->hasPermissionTo(Permission::VIEW_TEAMROUNDS);
    }

    public function update(User $user, TeamRoundScenario $scenario): bool
    {
        return $user->clubhouse_id === $scenario->teamRound->clubhouse_id && $user->hasPermissionTo(Permission::EDIT_TEAMROUNDS);
    }

    public function delete(User $user, TeamRoundScenario $scenario): bool
    {
        return $user->clubhouse_id === $scenario->teamRound->clubhouse_id && $user->hasPermissionTo(Permission::EDIT_TEAMROUNDS);
    }
}
