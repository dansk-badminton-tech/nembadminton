<?php


namespace App\Listeners;

use App\Jobs\BridgeToHorizon;
use App\Models\Club;
use App\Models\User;
use Illuminate\Auth\Events\Registered;

class EnsureClubConnection
{

    /**
     * Handle the event.
     *
     * @param Registered $event
     *
     * @return void
     */
    public function handle(Registered $event) : void
    {
        /** @var User $user */
        $user = $event->user;
        $user->clubs()->attach($user->club->id);
    }

}
