<?php


namespace FlyCompany\Notification\GraphQL\Mutation;

use App\Models\User;
use App\Notifications\Release;
use FlyCompany\Notification\Enum\NotificationType;
use FlyCompany\Notification\Enum\SubscriptionField;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;
use Nuwave\Lighthouse\Execution\Utils\Subscription;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class Notification
{

    /**
     * @param                      $rootValue
     * @param array<string, mixed> $args
     * @param GraphQLContext       $context
     * @param ResolveInfo          $resolveInfo
     *
     * @return bool
     */
    public function read($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo) : bool
    {
        /** @var User $user */
        $user = $context->user();
        $user->unreadNotifications()->update(['read_at' => now()]);
        return true;
    }

    /**
     * @param                      $rootValue
     * @param array<string, mixed> $args
     * @param GraphQLContext       $context
     * @param ResolveInfo          $resolveInfo
     *
     * @return true
     * @throws AuthorizationException
     */
    public function send($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo) : true
    {
        if(!Gate::allows("admin")){
            throw new AuthorizationException();
        }

        $input = $args['input'];
        $all = $input['receivers']['all'] ?? false;
        $users = $input['receivers']['users'] ?? [];

        $type = NotificationType::from($input['message']['type']);
        $title = $input['message']['title'];
        $message = $input['message']['body'];

        /** @var User[] $receivers */
        if($all){
            $receivers = User::query()->get();
        }else{
            $receivers = User::query()->whereIn('id', $users)->get();
        }

        $notification = match ($type){
            NotificationType::Release => new Release($title, $message)
        };

        \Illuminate\Support\Facades\Notification::send($receivers, $notification);

        foreach ($receivers as $receiver){
            $notification = $receiver->notifications()->first();
            Subscription::broadcast(SubscriptionField::Notifications->value, $notification);
        }

        return true;
    }
}
