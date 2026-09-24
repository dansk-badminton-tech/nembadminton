<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\ClubhouseCreated;
use App\Jobs\BridgeToHorizon;
use App\Models\Club;
use FlyCompany\Club\Log;

class InitializeClub
{
    /**
     * Handle the event.
     */
    public function handle(ClubhouseCreated $event): void
    {
        $event->clubhouse->clubs()->get()->each(function (Club $club) {
            if (! $club->initialized) {
                Log::createLog($club->id, 'Klub oprettet og import startet. Vent venligst...', 'system');
                BridgeToHorizon::dispatch($club->id)->onConnection('database');
            }
        });
    }
}
