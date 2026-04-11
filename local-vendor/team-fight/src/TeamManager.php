<?php
declare(strict_types = 1);


namespace FlyCompany\TeamFight;

use App\Models\Member;
use App\Models\SquadMember;
use App\Models\SquadPoint;
use App\Models\TeamRound;
use Carbon\Carbon;
use Illuminate\Contracts\Database\Eloquent\Builder;

class TeamManager
{

    /**
     * @param string $name
     * @param Carbon $game_date
     * @param string $version
     *
     * @return TeamRound
     */
    public function createTeam(string $name, Carbon $game_date, string $version)
    {
        return new TeamRound(compact('name', 'game_date', 'version'));
    }

    public function copyTeam(TeamRound $team) : TeamRound
    {
        $copyTeam = $team->replicate();
        $copyTeam->name = "Kopi af $team->name";
        $copyTeam->save();

        return $copyTeam;
    }

}
