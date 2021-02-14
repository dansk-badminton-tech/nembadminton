<?php


namespace App\GraphQL\Mutations;

use App\Models\User;
use App\Notifications\TeamUpdated;
use GraphQL\Type\Definition\ResolveInfo;
use NotificationChannels\WebPush\PushSubscription;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class Subscription
{

    /**
     * @param                      $rootValue
     * @param array<string, mixed> $args
     * @param GraphQLContext       $context
     * @param ResolveInfo          $resolveInfo
     *
     * @return PushSubscription
     */
    public function subscribe($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo) : PushSubscription
    {
        /** @var User $user */
        $user = $context->user();
        $input = $args['input'];
        $endpoint = $input['endpoint'];
        $key = $input['publicKey'];
        $token = $input['authToken'];
        $contentEncoding = $input['contentEncoding'];

        return $user->updatePushSubscription($endpoint, $key, $token, $contentEncoding);
    }

    /**
     * @param                      $rootValue
     * @param array<string, mixed> $args
     * @param GraphQLContext       $context
     * @param ResolveInfo          $resolveInfo
     *
     * @return bool
     */
    public function unsubscribe($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo) : bool
    {
        /** @var User $user */
        $user = $context->user();
        $input = $args['input'];
        $endpoint = $input['endpoint'];
        $user->deletePushSubscription($endpoint);

        return true;
    }

    public function subscribeEmail($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo) : bool
    {

    }

    /**
     * @param                      $rootValue
     * @param array<string, mixed> $args
     * @param GraphQLContext       $context
     * @param ResolveInfo          $resolveInfo
     *
     * @return bool
     */
    public function sendNotification($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo) : bool
    {
        /** @var User $user */
        $user = $context->user();
        $user->notify(new TeamUpdated());

        return true;
    }
}
