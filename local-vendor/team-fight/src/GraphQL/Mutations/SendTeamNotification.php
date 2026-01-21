<?php
declare(strict_types = 1);

namespace FlyCompany\TeamFight\GraphQL\Mutations;

use App\Enums\TeamNotificationType;
use App\Enums\RecipientType;
use App\Models\TeamReceivers;
use App\Models\Teams;
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
     * @return Teams
     */
    public function __invoke($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo) : Teams
    {
        $team = $args['id'];
        $message = $args['message'];
        $receivers = $args['receivers'];
        $type = $args['type'];

        /** @var Teams $team */
        $team = Teams::query()->findOrFail($team);

        $method = RecipientType::from($receivers['method']);
        $teamNotificationType = TeamNotificationType::from($type);

        if($receivers['saveEmails'] ?? false){
            TeamReceivers::upsert(
                [
                    'team_id' => $team->id,
                    'emails' => json_encode($receivers['emails'], JSON_THROW_ON_ERROR)
                ],
                ['team_id']
            );
        }

        if ($method === RecipientType::MANUAL_EMAILS) {
            $this->notifier->sendManualEmails($team, $receivers['emails'], $message, $teamNotificationType);
        }

        if ($method === RecipientType::TEST_SELF) {
            /** @var User $user */
            $user = $context->user();
            $this->notifier->sendTestSelf($team, $user, $message, $teamNotificationType);
        }

        return $team;
    }
}
