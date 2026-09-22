<?php


namespace FlyCompany\TeamFight\GraphQL\Mutations;

use App\Models\TeamRound;
use FlyCompany\TeamFight\SquadManager;
use FlyCompany\TeamFight\TeamRoundManager;
use Illuminate\Support\Facades\DB;
use Nuwave\Lighthouse\Execution\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class CopyTeam
{

    public function __construct(private readonly TeamRoundManager $teamManager, private readonly SquadManager $squadManager)
    {}

    /**
     * @param                $rootValue
     * @param array          $args
     * @param GraphQLContext $context
     * @param ResolveInfo    $resolveInfo
     */
    public function copyTeam($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo) : TeamRound
    {
        return DB::transaction(function () use ($args, $context) {
            /** @var TeamRound $sourceTeam */
            $sourceTeam = TeamRound::query()
                ->where('clubhouse_id', $context->user()->clubhouse_id)
                ->where('id', $args['id'])
                ->lockForUpdate()
                ->firstOrFail();
            $newTeam = $this->teamManager->copyTeam($sourceTeam);

            // Scenarios are planning alternatives, not part of a copied TeamRound.
            // Pass the selected official scenario so each Squad copies only its active lineup.
            foreach ($sourceTeam->squads as $squad) {
                $this->squadManager->copySquad($squad, $newTeam, $sourceTeam->officialScenario?->id);
            }

            return $newTeam;
        });
    }
}
