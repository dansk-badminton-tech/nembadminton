<?php

declare(strict_types=1);

namespace FlyCompany\BadmintonPlayerAPI\Commands;

use FlyCompany\BadmintonPlayerAPI\BadmintonPlayerAPI;
use Illuminate\Console\Command;

class CacheWarmUp extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'badmintonplayer-api:cache-warm-up';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetches data from badmintonplayer API and save it to cache';

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
        echo json_encode($matches, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
        return 0;
    }

}
