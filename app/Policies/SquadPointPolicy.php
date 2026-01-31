<?php

namespace App\Policies;

use App\Models\SquadPoint;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SquadPointPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {

    }

    public function view(User $user, SquadPoint $squadPoint): bool
    {
    }

    public function create(User $user): bool
    {
    }

    public function update(User $user, SquadPoint $squadPoint): bool
    {
        return $user->clubhouse_id === $squadPoint->member->category->squad->team->clubhouse_id;
    }

    public function delete(User $user, SquadPoint $squadPoint): bool
    {
    }

    public function restore(User $user, SquadPoint $squadPoint): bool
    {
    }

    public function forceDelete(User $user, SquadPoint $squadPoint): bool
    {
    }
}
