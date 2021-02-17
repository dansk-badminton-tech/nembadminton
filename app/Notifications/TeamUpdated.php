<?php


namespace App\Notifications;

use App\Models\Teams;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class TeamUpdated extends Notification implements ShouldQueue
{

    use Queueable;

    /**
     * @var Teams
     */
    private Teams $team;

    /**
     * Create a new notification instance.
     *
     * @param Teams $team
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
    public function via(User $notifiable)
    {
        $channels = ['database', WebPushChannel::class];
        if ($notifiable->subscriptionSettings()->exists() && $notifiable->subscriptionSettings->email) {
            $channels[] = 'mail';
        }

        return $channels;
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
        return 'Ny opdatering på ' . $this->team->name;
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
        return url($this->getPath());
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
            ->tag($notification->id)
            ->action('Vis holdkamp', 'view_teamfight')
            ->data(['id' => $notification->id, 'path' => $this->getPath(), 'action' => 'view_teamfight']);
    }

    public function toMail($notifiable)
    {
        return (new MailMessage())
            ->greeting("Hej, $notifiable->name")
            ->subject($this->getTitle())
            ->line('Der er sket ændringer på ' . $this->team->name)
            ->line('Se ændringerne her')
            ->line("[{$this->getActionUrl()}]({$this->getActionUrl()})")
            ->salutation('Hilsen ....');
    }

    /**
     * @return string
     */
    protected function getPath() : string
    {
        return "/team-fight/{$this->team->id}/view";
    }
}
