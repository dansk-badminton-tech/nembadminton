<?php
declare(strict_types = 1);


namespace FlyCompany\TeamFight\GraphQL\Mutations;

use FlyCompany\TeamFight\Models\SerializerHelper;
use FlyCompany\TeamFight\Models\Squad;
use FlyCompany\TeamFight\TeamValidator;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Support\Collection;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Symfony\Component\Serializer\Serializer;

class Validate
{

    /**
     * @var TeamValidator
     */
    private TeamValidator $teamValidator;

    /**
     * @var Serializer
     */
    private Serializer $serializer;

    public function __construct(TeamValidator $teamValidator)
    {
        $this->serializer = SerializerHelper::getSerializer();
        $this->teamValidator = $teamValidator;
    }

    /**
     * @param                $rootValue
     * @param array          $args
     * @param GraphQLContext $context
     * @param ResolveInfo    $resolveInfo
     *
     * @return array
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function validateCrossSquads($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo) : Collection
    {
        $teams = new Collection($args['input']);

        [$leagueAnd1DivSquads, $squadsBelowFirstDiv] = ValidateHelper::splitTeams($teams);

        $squads = $leagueAnd1DivSquads->pluck('squad');
        $squads = $this->serializer->denormalize($squads->toArray(), Squad::class . '[]');
        $playingTOHighLeagueAndFirstDiv = $this->teamValidator->validateCrossSquadsLeague($squads);

        $squads = $squadsBelowFirstDiv->pluck('squad');
        $squads = $this->serializer->denormalize($squads->toArray(), Squad::class . '[]');
        $playingToHighBelowFirstDiv = $this->teamValidator->validateCrossSquads($squads);

        return $playingTOHighLeagueAndFirstDiv->merge($playingToHighBelowFirstDiv);
    }

    /**
     * @param $rootValue
     * @param array $args
     * @param GraphQLContext $context
     * @param ResolveInfo $resolveInfo
     * @return array
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function validateSquads($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): array
    {
        $squads = new Collection($args['input']);
        $squads = $squads->pluck('squad');
        $squads = $this->serializer->denormalize($squads->toArray(), Squad::class . '[]');
        $playingToHigh = [];
        foreach ($squads as $squad) {
            $playingToHigh = array_merge($this->teamValidator->validateSquad($squad), $playingToHigh);
        }

        return $playingToHigh;
    }

}
