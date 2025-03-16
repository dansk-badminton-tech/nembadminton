<?php


namespace FlyCompany\TeamFight\GraphQL\Mutations;

use App\Models\Teams;
use FlyCompany\TeamFight\SquadManager;
use FlyCompany\TeamFight\TeamManager;
use Nuwave\Lighthouse\Execution\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class CopyTeam
{

    public function __construct(private readonly TeamManager $teamManager, private readonly SquadManager $squadManager)
    {}

    /**
     * @param                $rootValue
     * @param array          $args
     * @param GraphQLContext $context
     * @param ResolveInfo    $resolveInfo
     */
    public function copyTeam($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo) : Teams
    {
        /** @var Teams $sourceTeam */
        $sourceTeam = Teams::query()
             ->where('clubhouse_id', $context->user()->clubhouse_id)
             ->where('id', $args['id'])->lockForUpdate()->firstOrFail();
        $newTeam = $this->teamManager->copyTeam($sourceTeam);
        foreach ($sourceTeam->squads as $squad){
            $this->squadManager->copySquad($squad, $newTeam);
        }
        return $newTeam;
    }
}
