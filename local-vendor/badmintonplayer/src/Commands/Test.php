<?php

namespace FlyCompany\BadmintonPlayer\Commands;

use App\Jobs\BadmintonPlayerImportPoints;
use FlyCompany\BadmintonPlayer\Jobs\ImportPoints;
use FlyCompany\BadmintonPlayerAPI\BadmintonPlayerAPI;
use FlyCompany\BadmintonPlayerAPI\Models\TeamMatch;
use FlyCompany\BadmintonPlayerAPI\Models\TeamMatchLineup;
use FlyCompany\Members\PointsManager;
use FlyCompany\Scraper\BadmintonPlayer;
use Illuminate\Console\Command;

class Test extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'badmintonplayer-api-import:test {club-id : BadmintonPlayer club id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test';

    /**
     * Execute the console command.
     *
     * @return int
     * @throws \JsonException
     */
    public function handle(BadmintonPlayerAPI $badmintonPlayerAPI) : int
    {
        $matches = $badmintonPlayerAPI->getPlayedLeagueMatches()->getByClubId($this->argument('club-id'));
        $matches = $matches->groupBy(function(TeamMatchLineup $matchLineup){
            return $matchLineup->match->divisionName.' '.$matchLineup->match->groupName;
        });
        /** @var TeamMatchLineup[] $groupMatches */
        foreach ($matches as $group => $groupMatches){
            $this->line($group);
            foreach ($groupMatches as $match){
                $this->line("     {$match->match->getMatchTimeCarbon()} {$match->match->teamName1} - {$match->match->teamName2}");
            }
        }

        return 0;
    }

}
