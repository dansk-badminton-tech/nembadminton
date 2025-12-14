<?php

namespace App\Notifications;

use App\Models\Teams;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TeamPublish extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(private Teams $team, private User $user)
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
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->user->name.', så er holdrunden klar!')
            ->markdown('mail.TeamPublish', [
                'teamName' => $this->team->name,
                'squadName' => 'Hold 1',
                'categories' => [
                    [
                        'name' => 'Herre double',
                        'partner' => 'Mikkel Hansen',
                    ],
                    [
                        'name' => 'Herre single',
                        'partner' => null,
                    ]
                ],
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
