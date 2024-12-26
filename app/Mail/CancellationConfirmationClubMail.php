<?php

namespace App\Mail;

use App\Models\Cancellation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CancellationConfirmationClubMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(private readonly Cancellation $cancellation)
    {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: 'afbud@nembadminton.dk',
            subject: 'Du har modtaget et afbud fra '.$this->cancellation->member->name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.confirmation-club-mail',
            with: [
                'name' => $this->cancellation->member->name,
                'unavailableDates' => $this->cancellation->dates->pluck('date')->sort(),
                'optionalMessage' => $this->cancellation->message
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
