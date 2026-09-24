<?php

namespace App\Listeners;

use App\Events\ClubhouseCreated;
use App\Jobs\LoopsUpdateContact;

class UpdateClubNameInLoops
{
    /**
     * Handle the event.
     */
    public function handle(ClubhouseCreated $event): void
    {
        $user = auth()->user();
        LoopsUpdateContact::dispatch($user)->onConnection('database');
    }
}
