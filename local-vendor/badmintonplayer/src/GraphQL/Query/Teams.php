<?php

declare(strict_types=1);

namespace FlyCompany\BadmintonPlayer\GraphQL\Query;

use FlyCompany\BadmintonPlayerAPI\BadmintonPlayerAPI;
use FlyCompany\BadmintonPlayerAPI\Models\TeamMatchLineup;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Support\Arr;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class Teams
{
    private BadmintonPlayerAPI $badmintonPlayerAPI;

    public function __construct(BadmintonPlayerAPI $badmintonPlayerAPI)
    {
        $this->badmintonPlayerAPI = $badmintonPlayerAPI;
    }

    /**
     * Return a value for the field.
     *
     * @param  @param  null  $root Always null, since this field has no parent.
     * @param array<string, mixed> $args        The field arguments passed by the client.
     * @param GraphQLContext       $context     Shared between all fields.
     * @param ResolveInfo          $resolveInfo Metadata for advanced query resolution.
     *
     * @return mixed
     */
    public function __invoke($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $matches = $this->badmintonPlayerAPI->getPlayedLeagueMatches()->getByClubId($args['clubId']);
        $matches = $matches->groupBy(function(TeamMatchLineup $matchLineup){
            return $matchLineup->match->divisionName.' '.$matchLineup->match->groupName;
        });

        $collection = [];
        /** @var TeamMatchLineup $currentMatches */
        foreach ($matches as $divisionName => $currentMatches){
            $data = [];
            $data['divisionName'] = $divisionName;
            $data['lineups'] = $currentMatches;
            $collection[] = $data;
        }

        return $collection;
    }

}
