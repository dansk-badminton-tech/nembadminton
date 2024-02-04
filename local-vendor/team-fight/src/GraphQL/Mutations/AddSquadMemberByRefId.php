<?php


namespace FlyCompany\TeamFight\GraphQL\Mutations;

use App\Models\Teams;
use FlyCompany\TeamFight\SquadManager;
use FlyCompany\TeamFight\TeamManager;
use Nuwave\Lighthouse\Execution\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class AddSquadMemberByRefId
{

    public function __construct(private readonly SquadManager $squadManager)
    {}

    /**
     * @param                $rootValue
     * @param array          $args
     * @param GraphQLContext $context
     * @param ResolveInfo    $resolveInfo
     */
    public function __invoke($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo) : \App\Models\SquadMember
    {

        $version = $args['version'];
        $categoryId = $args['categoryId'];
        $refId = $args['refId'];
        return $this->squadManager->addPlayerToSquadByRefId($refId, $categoryId, $version);
    }
}
