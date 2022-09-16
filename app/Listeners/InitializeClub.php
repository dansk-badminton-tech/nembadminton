<?php
declare(strict_types = 1);


namespace App\Listeners;

use App\Jobs\BridgeToHorizon;
use App\Models\Club;
use App\Models\User;
use FlyCompany\BadmintonPlayer\Jobs\ImportMembers;
use FlyCompany\BadmintonPlayer\Jobs\ImportPoints;
use FlyCompany\BadmintonPlayerAPI\RankingPeriodType;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Bus;

class InitializeClub
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
        $clubId = $user->club->id;
        /** @var Club $club */
        $club = Club::query()->findOrFail($clubId);
        if(!$club->initialized){
            \FlyCompany\Club\Log::createLog($clubId, "Klub oprettet og import startet. Vent venligst...", 'system');
            BridgeToHorizon::dispatch($clubId)->onConnection('database');
        }
    }
}
