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
        private Teams $team
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
     * @param SquadCategory $category
     * @param User $user
     * @return SquadMember|null
     */
    public function findPartner(\App\Models\SquadCategory $category, User $user): SquadMember|null
    {
        return $category->players->first(function (SquadMember $squadMember) use ($user) {
            return $squadMember->member_ref_id !== $user->player_id;
        });
    }

    /**
     * @param User $user
     * @return array|SquadCategory[]
     */
    private function resolveCategories(User $user): array
    {
        $categories = $this->squad->playingIn($user);
        return array_map(function ($category) use ($user) {
            $squadMember = $this->findPartner($category, $user);
            return [
                'name' => $category->name,
                'partner' => $squadMember !== null ? $squadMember->name : '-',
            ];
        }, $categories);
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(User $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Så er holdrunden klar!')
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
