<?php

namespace App\Listeners;

use App\Events\UserUpdate;
use App\Jobs\LoopsUpdateContact;
use App\Models\User;

class UpdateContactInLoops
{
    /**
     * Handle the event.
     */
    public function handle(UserUpdate $event): void
    {
        $user = $event->getUser();
        LoopsUpdateContact::dispatch($user)->onConnection('database');
    }
}
