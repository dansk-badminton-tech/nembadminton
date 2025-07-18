<?php


namespace App\Listeners;

use App\Events\ClubhouseCreated;
use App\Jobs\LoopsUpdateContact;

class UpdateClubNameInLoops
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
        $user = auth()->user();
        LoopsUpdateContact::dispatch($user)->onConnection('database');
    }

}
