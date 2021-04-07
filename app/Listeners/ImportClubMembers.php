<?php
declare(strict_types = 1);


namespace App\Listeners;

use App\Jobs\BadmintonPlayerImportMembers;
use App\Jobs\BadmintonPlayerImportPoints;
use App\Models\User;
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
            new BadmintonPlayerImportMembers([(string)$clubId]),
            new BadmintonPlayerImportPoints($clubId),
        ])->dispatch();
    }
}
