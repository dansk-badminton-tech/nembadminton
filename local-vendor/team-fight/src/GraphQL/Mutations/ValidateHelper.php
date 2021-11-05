<?php

declare(strict_types=1);

namespace FlyCompany\TeamFight\GraphQL\Mutations;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ValidateHelper
{

    /**
     * @param  Collection  $teams
     * @return Collection[]
     */
    public static function splitTeams(Collection $teams): array
    {
        $leagueSearchName = 'LIGA';
        $leagueAndBelowTeamPair = self::getPair($teams, $leagueSearchName);

        $firstDivisionSearchName = 'FIRSTDIVISION';
        $firstDivisionAndBelowTeamPair = self::getPair($teams, $firstDivisionSearchName);

        $rest = $teams->filter(static function(array $team) {
            return Str::contains($team['squad']['league'], 'OTHER');
        });

        return [$leagueAndBelowTeamPair, $firstDivisionAndBelowTeamPair, $rest];
    }

    /**
     * @param  Collection  $teams
     */
    private static function getPair(Collection $teams, string $leagueSearch): Collection
    {
        $leagueKey = $teams->search(static function ($team) use ($leagueSearch) {
            return isset($team['squad']['league']) && Str::contains($team['squad']['league'], $leagueSearch);
        });
        $leagueAndBelowTeamPair = new Collection();
        $belowTeamKey = $leagueKey + 1;
        if ($leagueKey !== false && $teams->has($belowTeamKey)) {
            $leagueAndBelowTeamPair->push($teams->get($leagueKey));
            $leagueAndBelowTeamPair->push($teams->get($belowTeamKey));
        }
        return $leagueAndBelowTeamPair;
    }

}
