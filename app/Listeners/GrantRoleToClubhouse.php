<?php

namespace App\Listeners;

use App\Enums\Role;
use App\Events\ClubhouseCreated;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class GrantRoleToClubhouse
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ClubhouseCreated $event): void
    {
        setPermissionsTeamId($event->clubhouse->id);
        /** @var User $user */
        $user = auth()->user();
        $user->assignRole(Role::CLUB_ADMIN->value);
    }
}
