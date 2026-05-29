<?php
declare(strict_types = 1);


namespace FlyCompany\TeamFight\GraphQL\Mutations;

use App\Models\Point;
use App\Models\SquadCategory;
use App\Models\SquadMember;
use App\Models\SquadPoint;
use App\Models\TeamRound;
use App\Models\Squad;
use FlyCompany\TeamFight\Models\SerializerHelper;
use FlyCompany\TeamFight\SquadManager;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Symfony\Component\Serializer\Serializer;

/**
 * Class UpdateTeam
 *
 * @package App\GraphQL\Mutation
 */
class UpdateTeamRound
{

    /**
     * @var SquadManager
     */
    private SquadManager $squadManager;


    public function __construct(SquadManager $squadManager)
    {
        $this->serializer = SerializerHelper::getSerializer();
        $this->squadManager = $squadManager;
    }

    /**
     * @var Serializer
     */
    private Serializer $serializer;


    /**
     * @param                $rootValue
     * @param array          $args
     * @param GraphQLContext $context
     * @param ResolveInfo    $resolveInfo
     */
    public function updatePointsOnAllSquadsInTeamRound($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo) : TeamRound
    {
        $teamId = $args['id'];
        $version = $args['version'];

        $teamRound = TeamRound::query()->where('id', $teamId)->firstOrFail();

        return $this->squadManager->updatePointsOnAllSquadsInTeamRound($teamRound, $version);
    }

    /**
     * @param                $rootValue
     * @param array          $args
     * @param GraphQLContext $context
     * @param ResolveInfo    $resolveInfo
     *
     * @return \App\Models\Squad
     */
    public function updatePointsOnSquad($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo) : \App\Models\Squad
    {
        $squadId = (int)$args['id'];
        $version = $args['version'];

        return $this->squadManager->updatePoints($squadId, $version);
    }
}
