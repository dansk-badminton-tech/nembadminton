<?php

namespace App\Policies;

use App\Models\TeamActivityLog;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TeamActivityLogPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {

    }

    public function view(User $user, TeamActivityLog $teamActivityLog): bool
    {
        return $user->clubhouse_id === $teamActivityLog->team->clubhouse_id;
    }

    public function create(User $user): bool
    {
    }

    public function update(User $user, TeamActivityLog $teamActivityLog): bool
    {
    }

    public function delete(User $user, TeamActivityLog $teamActivityLog): bool
    {
    }

    public function restore(User $user, TeamActivityLog $teamActivityLog): bool
    {
    }

    public function forceDelete(User $user, TeamActivityLog $teamActivityLog): bool
    {
    }
}
