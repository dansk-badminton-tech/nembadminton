<?php
declare(strict_types = 1);

namespace FlyCompany\TeamFight\GraphQL\Mutations;

use App\Enums\TeamNotificationType;
use App\Enums\RecipientType;
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
        $team->publish = true;
        $team->message = $message;
        $team->saveOrFail();

        $method = RecipientType::from($receivers['method']);
        $teamNotificationType = TeamNotificationType::from($type);

        if ($method === RecipientType::PLATFORM_USERS) {
            $this->notifier->sendPlatformNotifications($team, $message, $teamNotificationType);
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
