<?php

namespace FlyCompany\TeamFight;

use App\Enums\ActivityAction;
use App\Enums\TeamNotificationType;
use App\Enums\RecipientType;
use App\Mail\TeamMail;
use App\Models\TeamActivityLog;
use App\Models\Teams;
use App\Models\User;
use App\Notifications\SquadPublish;
use Illuminate\Support\Facades\Mail;

class Notifier
{

    public function sendPlatformNotifications(Teams $team, ?string $message, TeamNotificationType $action) : void
    {
        $totalNotified = [];
        $totalEmails = [];
        foreach ($team->squads as $squad) {
            $notified = [];
            foreach ($squad->categories as $category) {
                foreach ($category->players as $player) {
                    if ($player->user !== null && $player->user->clubhouse_id === $team->clubhouse_id && !in_array($player->user->id, $notified, true)) {
                        $notified[] = $player->user->id;
                        $totalNotified[] = $player->user->id;
                        if (!empty($player->user->email)) {
                            $totalEmails[] = $player->user->email;
                        }
                        $player->user->notifyNow(new SquadPublish($squad, $team, $this->resolveSubject($action)));
                    }
                }
            }
        }

        TeamActivityLog::create([
            'team_id' => $team->id,
            'action' => ActivityAction::from($action->value),
            'recipient_type' => RecipientType::PLATFORM_USERS,
            'recipient_count' => count($totalEmails),
            'recipients_summary' => count($totalEmails) . ' players notified',
            'message' => $message,
            'metadata' => [
                'user_ids' => array_values(array_unique($totalNotified)),
                'emails' => array_values(array_unique($totalEmails)),
            ],
            'user_id' => auth()->id(),
        ]);
    }

    public function sendManualEmails(Teams $team, array $emails, ?string $message, TeamNotificationType $notificationType) : void
    {
        Mail::bcc($emails)->send($this->getMailable($team, $notificationType));

        TeamActivityLog::create([
            'team_id' => $team->id,
            'action' => ActivityAction::from($notificationType->value),
            'recipient_type' => RecipientType::MANUAL_EMAILS,
            'recipient_count' => count($emails),
            'recipients_summary' => count($emails) . ' players notified',
            'message' => $message,
            'metadata' => [
                'user_ids' => [],
                'emails' => array_values(array_unique($emails)),
            ],
            'user_id' => auth()->id(),
        ]);
    }

    public function sendTestSelf(Teams $team, User $user, ?string $message, TeamNotificationType $notificationType) : void
    {
        Mail::bcc($user->email)->send($this->getMailable($team, $notificationType));

        TeamActivityLog::logTestEmailSent(
            $team->id,
            RecipientType::TEST_SELF,
            $message,
            [
                'user_ids' => [$user->id],
                'emails' => [$user->email],
            ],
            $user->id
        );
    }

    /**
     * @param Teams $team
     * @return TeamMail
     */
    public function getMailable(Teams $team, TeamNotificationType $type): TeamMail
    {
        return (new TeamMail($team))->subject($this->resolveSubject($type));
    }

    private function resolveSubject(TeamNotificationType $action) : string
    {
        return match ($action) {
            TeamNotificationType::TEAM_PUBLISH => 'Holdkamp offentliggjort',
            TeamNotificationType::TEAM_UPDATED => 'Holdkamp opdateret',
        };
    }

}
