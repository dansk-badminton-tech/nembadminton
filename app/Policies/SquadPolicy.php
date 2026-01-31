<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Squad;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SquadPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {

    }

    public function view(User $user, Squad $squad): bool
    {
    }

    public function create(User $user): bool
    {
        return $user->hasRole([Role::CLUB_ADMIN, Role::COACH, Role::SUPERADMIN]);
    }

    public function update(User $user, Squad $squad): bool
    {
        return $user->clubhouse_id === $squad->team->clubhouse_id;
    }

    public function delete(User $user, Squad $squad): bool
    {
        return $user->clubhouse_id === $squad->team->clubhouse_id;
    }

    public function restore(User $user, Squad $squad): bool
    {
    }

    public function forceDelete(User $user, Squad $squad): bool
    {
    }
}
