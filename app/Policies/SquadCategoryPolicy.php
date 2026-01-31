<?php

namespace App\Policies;

use App\Models\SquadCategory;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SquadCategoryPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {

    }

    public function view(User $user, SquadCategory $squadCategory): bool
    {
    }

    public function create(User $user): bool
    {

    }

    public function update(User $user, SquadCategory $squadCategory): bool
    {
        return $user->clubhouse_id === $squadCategory->squad->team->clubhouse_id;
    }

    public function delete(User $user, SquadCategory $squadCategory): bool
    {
        return $user->clubhouse_id === $squadCategory->squad->team->clubhouse_id;
    }

    public function restore(User $user, SquadCategory $squadCategory): bool
    {
    }

    public function forceDelete(User $user, SquadCategory $squadCategory): bool
    {
    }
}
