<?php

declare(strict_types=1);

namespace FlyCompany\TeamFight\GraphQL\Mutations;

use FlyCompany\TeamFight\Models\Player;
use FlyCompany\TeamFight\Models\SerializerHelper;
use FlyCompany\TeamFight\Models\Squad;
use FlyCompany\TeamFight\TeamValidator;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Support\Collection;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class Validate
{
    private TeamValidator $teamValidator;

    private Serializer $serializer;

    public function __construct(TeamValidator $teamValidator)
    {
        $this->serializer = SerializerHelper::getSerializer();
        $this->teamValidator = $teamValidator;
    }

    /**
     * @return array
     *
     * @throws ExceptionInterface
     */
    public function validateCrossSquads($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): Collection
    {
        $teams = new Collection($args['input']);

        $squads = $teams->pluck('squad');
        $squads = $this->deserializeSquads($squads);
        $playingToHigh = $this->teamValidator->validateCrossSquadsLeagueV3($squads);

        return $playingToHigh;
    }

    /**
     * @throws ExceptionInterface
     */
    public function validateSquads($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): array
    {
        $squads = new Collection($args['input']);
        $squads = $squads->pluck('squad');
        $squads = $this->deserializeSquads($squads);
        //        $this->teamValidator->validateSquads($squads);

        $playingToHigh = [];
        foreach ($squads as $squad) {
            $playingToHigh = array_merge($this->teamValidator->validateSquad($squad), $playingToHigh);
        }

        return $playingToHigh;
    }

    public function validateBasicSquads($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): Collection
    {
        $squads = new Collection($args['input']);
        $squads = $squads->pluck('squad');
        $squads = $this->deserializeSquads($squads);

        /** @var Squad[] $squads */
        return $this->teamValidator->validateBasicSquads($squads);
    }

    /**
     * @param  array{input: array<int, array<string, mixed>>, seasonStartYear: int}  $args
     * @return Player[]
     *
     * @throws ExceptionInterface
     */
    public function validateTooYoungPlayers(mixed $rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): array
    {
        $squads = (new Collection($args['input']))->pluck('squad');

        return $this->teamValidator->validateTooYoungPlayers($this->deserializeSquads($squads), $args['seasonStartYear']);
    }

    /**
     * @param  Collection<int|string, mixed>  $squads
     * @return Squad[]
     */
    private function deserializeSquads(Collection $squads): array
    {
        // GraphQL IDs arrive as strings, including numeric IDs backed by integer properties.
        return $this->serializer->denormalize($squads->toArray(), Squad::class.'[]', null, [
            AbstractObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true,
        ]);
    }
}
