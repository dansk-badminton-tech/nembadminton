<?php

namespace FlyCompany\TeamFight\GraphQL\Mutations;

use App\Models\Squad;
use Nuwave\Lighthouse\Execution\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class SquadOrdering
{
    public function __construct() {}

    public function moveSquadOrderUp($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): Squad
    {
        /** @var Squad $squad */
        $squad = Squad::query()->where('id', $args['id'])->firstOrFail();
        $squad->moveOrderUp();

        return $squad;
    }

    public function moveSquadOrderDown($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): Squad
    {
        /** @var Squad $squad */
        $squad = Squad::query()->where('id', $args['id'])->firstOrFail();
        $squad->moveOrderDown();

        return $squad;
    }
}
