<?php


namespace App\Notifications;

use App\Models\Teams;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class TeamUpdated extends Notification
{

    use Queueable;

    /**
     * @var Teams
     */
    private Teams $team;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Teams $team)
    {
        $this->team = $team;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     *
     * @return array
     */
    public function via($notifiable)
    {
        return ['database', WebPushChannel::class];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'title'      => $this->getTitle(),
            'body'       => $this->getBody(),
            'action_url' => $this->getActionUrl(),
            'created'    => Carbon::now()->toIso8601String(),
        ];
    }

    private function getTitle() : string
    {
        return 'Ændring på ' . $this->team->name;
    }

    /**
     * @return string
     */
    protected function getBody() : string
    {
        return 'Klik her for at se holdet';
    }

    /**
     * @return string
     */
    protected function getActionUrl() : string
    {
        return url("/team-fight/{$this->team->id}/view");
    }

    /**
     * Get the web push representation of the notification.
     *
     * @param mixed $notifiable
     * @param mixed $notification
     *
     * @return WebPushMessage
     */
    public function toWebPush($notifiable, $notification)
    {
        return (new WebPushMessage)
            ->title($this->getTitle())
            ->icon('/notification-icon.png')
            ->body($this->getBody())
            ->action('View app', 'view_app')
            ->data(['id' => $notification->id]);
    }
}
