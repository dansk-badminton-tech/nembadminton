<?php

namespace App\Policies;

use App\Models\TeamReceivers;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TeamReceiversPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {

    }

    public function view(User $user, TeamReceivers $teamReceivers): bool
    {
        return $user->clubhouse_id === $teamReceivers->teams->clubhouse_id;
    }

    public function create(User $user): bool
    {
    }

    public function update(User $user, TeamReceivers $teamReceivers): bool
    {
    }

    public function delete(User $user, TeamReceivers $teamReceivers): bool
    {
    }

    public function restore(User $user, TeamReceivers $teamReceivers): bool
    {
    }

    public function forceDelete(User $user, TeamReceivers $teamReceivers): bool
    {
    }
}
