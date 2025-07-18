<?php


namespace App\Listeners;

use App\Events\UserUpdate;
use App\Jobs\LoopsUpdateContact;
use App\Models\User;
use Illuminate\Auth\Events\Registered;

class CreateContactInLoops
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
        $user = $event->user;
        LoopsUpdateContact::dispatch($user)->onConnection('database');
    }

}
