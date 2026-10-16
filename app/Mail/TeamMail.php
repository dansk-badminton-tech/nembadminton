<?php

namespace App\Mail;

use App\Models\Squad;
use App\Models\TeamRound;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TeamMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(private TeamRound $team, private ?string $message)
    {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.TeamPublish',
            with: [
                'teamName' => $this->team->name,
                'message' => $this->message ?? 'Ingen besked',
                'url' => url("/app/team-fight/{$this->team->id}/public-view"),
                'paperSquads' => $this->paperSquadLabels(),
            ]
        );
    }

    /**
     * The round's Paper Squads (papirhold) as "Hold 2 (Team name)", in Squad order.
     *
     * @return list<string>
     */
    private function paperSquadLabels(): array
    {
        return Squad::query()->where('team_round_id', $this->team->id)->with('team')->orderBy('order')->get()->values()
            ->filter(fn (Squad $squad) => $squad->is_paper_squad)
            ->map(fn (Squad $squad, int $index) => 'Hold '.($index + 1).' ('.($squad->team->name ?? $squad->name).')')
            ->values()
            ->all();
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
