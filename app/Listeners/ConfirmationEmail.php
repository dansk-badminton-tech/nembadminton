<?php

namespace App\Listeners;

use App\Events\CancellationCreated;
use App\Mail\ConfirmationClubMail;
use App\Mail\ConfirmationParticipantEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class ConfirmationEmail
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
    public function handle(CancellationCreated $event): void
    {
        if($event->cancellation->cancellationCollector !== null){
            Mail::to($event->cancellation->cancellationCollector->email)->send(new ConfirmationClubMail($event->cancellation));
            Mail::to($event->cancellation->email)->send(new ConfirmationParticipantEmail($event->cancellation));
        }
    }
}
