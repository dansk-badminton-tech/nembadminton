<?php
declare(strict_types = 1);


namespace FlyCompany\TeamFight;

use App\Models\Member;
use App\Models\SquadMember;
use App\Models\SquadPoint;
use App\Models\Teams;
use Carbon\Carbon;
use Illuminate\Contracts\Database\Eloquent\Builder;

class TeamManager
{

    /**
     * @param string $name
     * @param Carbon $game_date
     * @param string $version
     *
     * @return Teams
     */
    public function createTeam(string $name, Carbon $game_date, string $version)
    {
        return new Teams(compact('name', 'game_date', 'version'));
    }

    public function copyTeam(Teams $team) : Teams
    {
        $copyTeam = $team->replicate();
        $copyTeam->name = "Kopi af $team->name";
        $copyTeam->save();

        return $copyTeam;
    }

}
