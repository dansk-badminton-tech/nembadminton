<?php

namespace App\Listeners;

use App\Events\ClubhouseCreated;
use App\Events\InvitationCreated;
use App\Mail\Invitation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendInvitationEmail
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(InvitationCreated $event): void
    {
        // Queued, so an unreachable mail server does not fail the invite mutation
        Mail::to($event->invitation->invitee_email)->queue(new Invitation($event->invitation));
    }
}
