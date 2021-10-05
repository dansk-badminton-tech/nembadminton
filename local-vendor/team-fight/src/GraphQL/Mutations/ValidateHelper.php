<?php

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
        $leagueAnd1DivTeams = new Collection();
        $teamsBelowFirstDiv = new Collection();
        foreach ($teams as $team){
            if(isset($team['league']) && Str::contains($team['league'], ['ligaen', '1. division'])){
                $leagueAnd1DivTeams->push($team);
            }else{
                $teamsBelowFirstDiv->push($team);
            }
        }

        if($teamsBelowFirstDiv->isNotEmpty() && $leagueAnd1DivTeams->isNotEmpty() && $leagueAnd1DivTeams->count() < 2){
            $leagueAnd1DivTeams->push($teamsBelowFirstDiv->get(0));
        }

        return [$leagueAnd1DivTeams, $teamsBelowFirstDiv];
    }

}
