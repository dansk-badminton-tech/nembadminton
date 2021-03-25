<?php


namespace App\Console\Commands;

use App\Jobs\BadmintonPlayerImportMembers;
use FlyCompany\Members\PointsManager;
use FlyCompany\Scraper\BadmintonPlayer;
use Illuminate\Console\Command;

class BadmintonPlayerMembersImporter extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'badmintonplayer-import:members {club-id : BadmintonPlayer club id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importing members for a club from badmintonplayer.dk rankinglist';

    /**
     * Execute the console command.
     *
     * @param BadmintonPlayer $scraper
     * @param PointsManager   $pointsManager
     *
     * @return int
     * @throws \JsonException
     */
    public function handle() : int
    {
        BadmintonPlayerImportMembers::dispatchNow([$this->argument('club-id')]);

        return 0;
    }

}
