<?php
declare(strict_types = 1);


namespace App\Listeners;

use App\Models\User;
use FlyCompany\BadmintonPlayer\Jobs\ImportMembers;
use FlyCompany\BadmintonPlayer\Jobs\ImportPoints;
use FlyCompany\BadmintonPlayerAPI\RankingPeriodType;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Bus;

class ImportClubMembers
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
        Bus::chain([
            new ImportMembers([$clubId]),
            new ImportPoints($clubId, RankingPeriodType::CURRENT),
        ])->dispatch();
    }
}
