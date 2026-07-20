<?php
declare(strict_types = 1);

namespace FlyCompany\TeamFight\GraphQL\Mutations;

use App\Enums\TeamNotificationType;
use App\Enums\RecipientType;
use App\Models\TeamReceivers;
use App\Models\TeamRound;
use App\Models\User;
use FlyCompany\TeamFight\Notifier;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class SendTeamNotification
{
    private Notifier $notifier;

    public function __construct(Notifier $notifier)
    {
        $this->notifier = $notifier;
    }

    /**
     * @param                $rootValue
     * @param array $args
     * @param GraphQLContext $context
     * @param ResolveInfo $resolveInfo
     * @return array
     */
    public function __invoke($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo) : array
    {
        $message = $args['message'];
        $receivers = $args['receivers'];
        $type = $args['type'];

        /** @var TeamRound $team */
        $team = TeamRound::query()->findOrFail($args['id']);

        $method = RecipientType::from($receivers['method']);
        $teamNotificationType = TeamNotificationType::from($type);

        if ($receivers['saveEmails'] ?? false) {
            TeamReceivers::upsert(
                [
                    'team_round_id' => $team->id,
                    'emails' => json_encode($receivers['emails'], JSON_THROW_ON_ERROR)
                ],
                ['team_round_id']
            );
        }

        $sentCount = 0;
        $skippedPlayers = [];

        if ($method === RecipientType::MANUAL_EMAILS) {
            $emails = $receivers['emails'] ?? [];
            $this->notifier->sendManualEmails($team, $emails, $message, $teamNotificationType);
            $sentCount = count($emails);
        }

        if ($method === RecipientType::TEST_SELF) {
            /** @var User $user */
            $user = $context->user();
            $this->notifier->sendTestSelf($team, $user, $message, $teamNotificationType);
            $sentCount = 1;
        }

        if ($method === RecipientType::PLATFORM) {
            $result = $this->notifier->sendToPlatformPlayers($team, $message, $teamNotificationType);
            $sentCount = $result['sentCount'];
            $skippedPlayers = $result['skippedPlayers'];
        }

        return [
            'teamRound' => $team,
            'sentCount' => $sentCount,
            'skippedPlayers' => $skippedPlayers,
        ];
    }
}
