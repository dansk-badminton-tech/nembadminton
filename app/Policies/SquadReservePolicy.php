<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permission;
use App\Models\SquadReserve;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SquadReservePolicy
{
    use HandlesAuthorization;

    public function delete(User $user, SquadReserve $reserve): bool
    {
        return $user->clubhouse_id === $reserve->squad->teamRound->clubhouse_id && $user->hasPermissionTo(Permission::EDIT_TEAMROUNDS);
    }
}
