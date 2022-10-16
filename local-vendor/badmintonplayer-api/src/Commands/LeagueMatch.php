<?php

declare(strict_types=1);

namespace FlyCompany\BadmintonPlayerAPI\Commands;

use FlyCompany\BadmintonPlayerAPI\BadmintonPlayerAPI;
use FlyCompany\BadmintonPlayerAPI\Models\TeamMatch;
use Illuminate\Console\Command;

class LeagueMatch extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'badmintonplayer-api:league-matches {--limit= : limit how many league match that is shown} {--club-id= : only show matches for this club}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show league matches for the current season';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(BadmintonPlayerAPI $badmintonPlayerAPI) : int
    {
        $matches = $badmintonPlayerAPI->getCurrentLeagueMatches();
        $limit = $this->option('limit');
        if($limit !== null){
            $matches = array_slice($matches, 0, (int)$limit);
        }
        $clubId = $this->option('club-id');
        if($clubId !== null){
            $matches = array_filter($matches, static fn(TeamMatch $match) => $match->clubId1 == $clubId || $match->clubId2 == $clubId);
        }
        echo json_encode($matches, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
        return 0;
    }

}
