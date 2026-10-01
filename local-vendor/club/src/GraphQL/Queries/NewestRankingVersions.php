<?php

declare(strict_types=1);

namespace FlyCompany\Club\GraphQL\Queries;

use App\Models\User;
use FlyCompany\Club\RankingVersionUtil;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class NewestRankingVersions
{
    /**
     * @param  null  $root  Always null, since this field has no parent.
     * @param  array<string, mixed>  $args  The field arguments passed by the client.
     * @return string[]
     */
    public function __invoke($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): array
    {
        /** @var User $user */
        $user = $context->user();

        return RankingVersionUtil::getNewestRankingVersionsByClubs($user->clubhouse->clubs->pluck('id')->all());
    }
}
