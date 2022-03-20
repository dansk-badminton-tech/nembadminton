<?php

declare(strict_types=1);

namespace FlyCompany\BadmintonPlayerAPI\Collections;

use Carbon\Carbon;
use FlyCompany\BadmintonPlayerAPI\Models\TeamMatchLineup;
use Illuminate\Support\Collection;

class TeamMatchLineupCollection extends Collection
{

    /**
     * @var TeamMatchLineup[]
     */
    protected $items;

    public function getByClubId(int $clubId) : static {
        return $this->filter(static function(TeamMatchLineup $lineup) use ($clubId) {
            return $lineup->match->clubId1 === $clubId || $lineup->match->clubId2 === $clubId;
        });
    }

    public function onlyAfter(Carbon $after){
        return $this->filter(static function(TeamMatchLineup $lineup) use ($after){
            return $lineup->match->getMatchTimeCarbon() > $after;
        });
    }

    /**
     * @param array|integer[] $matchIds
     * @return $this
     */
    public function getByMatchIds(array $matchIds) : static{
        return $this->filter(static function(TeamMatchLineup $lineup) use ($matchIds) {
            return in_array($lineup->match->leagueMatchId, $matchIds, true);
        });
    }

}
