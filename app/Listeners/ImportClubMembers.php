<?php
declare(strict_types = 1);


namespace App\Listeners;

use App\Jobs\BadmintonPlayerImportMembers;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Bus;

class ImportClubMembers
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
        ])->dispatch();
    }
}
