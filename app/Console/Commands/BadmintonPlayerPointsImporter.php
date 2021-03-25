<?php


namespace App\Console\Commands;

use App\Jobs\BadmintonPlayerImportPoints;
use Carbon\Carbon;
use FlyCompany\Members\PointsManager;
use FlyCompany\Scraper\BadmintonPlayer;
use Illuminate\Console\Command;

class BadmintonPlayerPointsImporter extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'badmintonplayer-import:points {club-id : BadmintonPlayer club id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importing points for a club from badmintonplayer.dk rankinglist';

    /**
     * Execute the console command.
     *
     * @param BadmintonPlayer $scraper
     * @param PointsManager   $pointsManager
     *
     * @return int
     * @throws \JsonException
     */
    public function handle(BadmintonPlayer $scraper, PointsManager $pointsManager) : int
    {
        BadmintonPlayerImportPoints::dispatchNow($this->argument('club-id'));

        return 0;
    }

}
