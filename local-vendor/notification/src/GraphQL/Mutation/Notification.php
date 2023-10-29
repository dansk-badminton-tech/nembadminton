<?php


namespace FlyCompany\Notification\GraphQL\Mutation;

use App\Models\User;
use GraphQL\Type\Definition\ResolveInfo;
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

}
