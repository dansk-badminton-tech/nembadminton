<?php

namespace App\Notifications;

use App\Models\Squad;
use App\Models\SquadCategory;
use App\Models\SquadMember;
use App\Models\Teams;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SquadPublish extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        private Squad $squad,
        private Teams $team,
        private ?string $customSubject = null
    )
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(User $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->customSubject ?? 'Holdrunden er klar!')
            ->markdown('mail.SquadPublish', [
                'team' => $this->team,
                'squad' => $this->squad,
                'url' => url("/app/team-fight/{$this->team->id}/public-view")
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
