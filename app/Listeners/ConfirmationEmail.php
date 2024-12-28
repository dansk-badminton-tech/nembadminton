<?php

namespace App\Listeners;

use App\Events\CancellationCreated;
use App\Mail\CancellationConfirmationClubMail;
use App\Mail\CancellationConfirmationParticipantEmail;
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
            if($event->cancellation->cancellationCollector->email !== null){
                Mail::to($event->cancellation->cancellationCollector->email)
                    ->send(new CancellationConfirmationClubMail($event->cancellation));
            }
            Mail::to($event->cancellation->email)->send(new CancellationConfirmationParticipantEmail($event->cancellation));
        }
    }
}
