<?php

namespace App\Notifications;

use App\Mail\TeamMail;
use App\Models\TeamRound;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

/**
 * Shared behaviour for team round notifications.
 *
 * Concrete notifications only differ in their title/body wording. All delivery
 * channels (database, web push, mail) and their payloads live here so every
 * team round notification is delivered consistently.
 */
abstract class TeamRoundNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected TeamRound $team, protected ?string $message = null)
    {
    }

    /**
     * The notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(User $notifiable) : array
    {
        return ['database', WebPushChannel::class, 'mail'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray($notifiable) : array
    {
        return [
            'title'      => $this->getTitle(),
            'body'       => $this->message ?: $this->getBody(),
            'action_url' => $this->getActionUrl(),
            'created'    => Carbon::now()->toIso8601String(),
        ];
    }

    public function toWebPush($notifiable, $notification) : WebPushMessage
    {
        return (new WebPushMessage)
            ->title($this->getTitle())
            ->icon('/notification-icon.png')
            ->body($this->message ?: $this->getBody())
            ->tag($notification->id)
            ->action('Vis holdkamp', 'view_teamfight')
            ->data(['id' => $notification->id, 'path' => $this->getPath(), 'action' => 'view_teamfight']);
    }

    /**
     * Render the mail using the shared branded TeamMail template so platform,
     * manual and test-self emails all look identical. A raw Mailable is not
     * auto-addressed by the mail channel, so we address it to the notifiable.
     */
    public function toMail($notifiable) : TeamMail
    {
        return (new TeamMail($this->team, $this->message))
            ->subject($this->getTitle())
            ->to($notifiable->routeNotificationFor('mail', $this));
    }

    protected function getActionUrl() : string
    {
        return url($this->getPath());
    }

    protected function getPath() : string
    {
        return "/team-fight/{$this->team->id}/public-view";
    }

    /**
     * The notification title, shown as the mail subject, push title and
     * database title.
     */
    abstract protected function getTitle() : string;

    /**
     * The fallback body used when no custom message was provided.
     */
    abstract protected function getBody() : string;
}
