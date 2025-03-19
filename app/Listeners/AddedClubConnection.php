<?php

namespace App\Listeners;

use App\Events\ClubhouseUpdated;
use App\Events\UserUpdate;
use App\Jobs\BridgeToHorizon;
use App\Models\Club;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class AddedClubConnection
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param UserUpdate $event
     *
     * @return void
     */
    public function handle(ClubhouseUpdated $event)
    {
        /** @var Club[] $clubs */
        $clubs = $event->clubhouse->clubs()->get();
        foreach ($clubs as $club){
            if($club->initialized === false){
                BridgeToHorizon::dispatch($club->id)->onConnection('database');
            }
        }
    }
}
