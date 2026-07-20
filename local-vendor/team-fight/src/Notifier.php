<?php

namespace FlyCompany\TeamFight;

use App\Enums\ActivityAction;
use App\Enums\TeamNotificationType;
use App\Enums\RecipientType;
use App\Mail\TeamMail;
use App\Models\SquadMember;
use App\Models\TeamActivityLog;
use App\Models\TeamRound;
use App\Models\User;
use App\Notifications\TeamPublish;
use App\Notifications\TeamUpdated;
use Illuminate\Notifications\Notification as LaravelNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class Notifier
{

    public function sendManualEmails(TeamRound $team, array $emails, ?string $message, TeamNotificationType $notificationType) : void
    {
        Mail::bcc($emails)->queue($this->getMailable($team, $message, $notificationType));

        TeamActivityLog::create([
            'team_round_id' => $team->id,
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

    public function sendTestSelf(TeamRound $team, User $user, ?string $message, TeamNotificationType $notificationType) : void
    {
        Mail::bcc($user->email)->queue($this->getMailable($team, $message, $notificationType));

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
     * Notify every player on the team round who has a linked user account.
     * Players without a linked user are skipped and returned by name.
     *
     * @return array{sentCount:int, skippedPlayers:array<int,string>}
     */
    public function sendToPlatformPlayers(TeamRound $team, ?string $message, TeamNotificationType $notificationType) : array
    {
        $team->loadMissing('squads.categories.players');

        /** @var array<string,string> $refIdToName ref id => player name */
        $refIdToName = [];
        foreach ($team->squads as $squad) {
            foreach ($squad->categories as $category) {
                foreach ($category->players as $player) {
                    /** @var SquadMember $player */
                    $refIdToName[$player->member_ref_id] = $player->name;
                }
            }
        }

        $users = User::query()
            ->whereIn('player_id', array_keys($refIdToName))
            ->where('clubhouse_id', $team->clubhouse_id)
            ->get();

        $reachableRefIds = array_map('strval', $users->pluck('player_id')->all());
        $skippedPlayers = [];
        foreach ($refIdToName as $refId => $name) {
            if (!in_array((string) $refId, $reachableRefIds, true)) {
                $skippedPlayers[] = $name;
            }
        }

        $notification = $this->buildNotification($team, $message, $notificationType);
        Notification::send($users, $notification);

        TeamActivityLog::create([
            'team_round_id' => $team->id,
            'action' => ActivityAction::from($notificationType->value),
            'recipient_type' => RecipientType::PLATFORM,
            'recipient_count' => $users->count(),
            'recipients_summary' => $users->count() . ' spillere notificeret, ' . count($skippedPlayers) . ' sprunget over',
            'message' => $message,
            'metadata' => [
                'user_ids' => $users->pluck('id')->all(),
                'skipped_players' => array_values($skippedPlayers),
            ],
            'user_id' => auth()->id(),
        ]);

        return [
            'sentCount' => $users->count(),
            'skippedPlayers' => array_values($skippedPlayers),
        ];
    }

    private function buildNotification(TeamRound $team, ?string $message, TeamNotificationType $notificationType) : LaravelNotification
    {
        return match ($notificationType) {
            TeamNotificationType::TEAM_PUBLISH => new TeamPublish($team, $message),
            TeamNotificationType::TEAM_UPDATED => new TeamUpdated($team, $message),
        };
    }

    /**
     * @param TeamRound $team
     * @param string|null $message
     * @param TeamNotificationType $type
     * @return TeamMail
     */
    public function getMailable(TeamRound $team, ?string $message, TeamNotificationType $type): TeamMail
    {
        return (new TeamMail($team, $message))->subject($this->resolveSubject($type));
    }

    private function resolveSubject(TeamNotificationType $action) : string
    {
        return match ($action) {
            TeamNotificationType::TEAM_PUBLISH => 'Holdrunden er klar! 📢',
            TeamNotificationType::TEAM_UPDATED => 'Ændringer til holdrunden! 🔄',
        };
    }

}
