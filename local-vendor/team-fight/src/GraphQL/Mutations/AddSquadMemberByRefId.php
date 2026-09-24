<?php

namespace FlyCompany\TeamFight\GraphQL\Mutations;

use App\Models\SquadMember;
use FlyCompany\TeamFight\SquadManager;
use Nuwave\Lighthouse\Execution\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class AddSquadMemberByRefId
{
    public function __construct(private readonly SquadManager $squadManager) {}

    public function __invoke($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): SquadMember
    {

        $version = $args['version'];
        $categoryId = $args['categoryId'];
        $refId = $args['refId'];

        return $this->squadManager->addPlayerToSquadByRefId($refId, $categoryId, $version);
    }
}
