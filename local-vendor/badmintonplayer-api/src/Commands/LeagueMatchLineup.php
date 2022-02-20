<?php

declare(strict_types=1);

namespace FlyCompany\BadmintonPlayerAPI\Commands;

use FlyCompany\BadmintonPlayerAPI\BadmintonPlayerAPI;
use FlyCompany\BadmintonPlayerAPI\Models\TeamMatchLineup;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class LeagueMatchLineup extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'badmintonplayer-api:league-matches-lineup {--limit= : limit how many is shown} {--clubId= : filter by club id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show played league matches lineups the last 14 days';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(BadmintonPlayerAPI $badmintonPlayerAPI) : int
    {
        $matches = $badmintonPlayerAPI->getPlayedLeagueMatches();

        $clubId = $this->option('clubId');
        if($clubId !== null){
            $clubId = (int)$clubId;
            $matches = $matches->getByClubId($clubId);
        }

        $limit = $this->option('limit');
        if($limit !== null){
            $matches = $matches->slice(0, (int)$limit);
        }
        echo json_encode($matches, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
        return 0;
    }

}
