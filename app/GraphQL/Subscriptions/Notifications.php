<?php declare(strict_types = 1);


namespace App\GraphQL\Subscriptions;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Nuwave\Lighthouse\Execution\ResolveInfo;
use Nuwave\Lighthouse\Schema\Types\GraphQLSubscription;
use Nuwave\Lighthouse\Subscriptions\Subscriber;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

final class Notifications extends GraphQLSubscription
{

    /** Check if subscriber is allowed to listen to the subscription. */
    public function authorize(Subscriber $subscriber, Request $request) : bool
    {
        $user = $subscriber->context->user();

        if($user === null) {
            return false;
        }

        return $user->getAuthIdentifier() === $subscriber->args['userId'];
    }

    /**
     * @param Subscriber           $subscriber
     * @param DatabaseNotification $root
     *
     * @return bool
     */
    public function filter(Subscriber $subscriber, mixed $root) : bool
    {
        $args = $subscriber->args;

        return $args['userId'] === $root->notifiable_id;
    }

    /**
     * @param DatabaseNotification $root
     * @param array                $args
     * @param GraphQLContext       $context
     * @param ResolveInfo          $resolveInfo
     *
     * @return array
     */
    public function resolve(mixed $root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo) : array
    {
        return $root->getAttributes();
    }
}
