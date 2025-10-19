<?php
declare(strict_types = 1);


namespace App\Listeners;

use App\Events\ClubhouseCreated;
use App\Jobs\BridgeToHorizon;
use App\Models\Club;

class InitializeClub
{

    /**
     * Handle the event.
     *
     * @param ClubhouseCreated $event
     *
     * @return void
     */
    public function handle(ClubhouseCreated $event) : void
    {
        $event->clubhouse->clubs()->get()->each(function (Club $club) {
            if(!$club->initialized){
                \FlyCompany\Club\Log::createLog($club->id, "Klub oprettet og import startet. Vent venligst...", 'system');
                BridgeToHorizon::dispatch($club->id)->onConnection('database');
            }
        });
    }
}
