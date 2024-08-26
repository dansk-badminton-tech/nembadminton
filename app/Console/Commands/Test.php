<?php


namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\Release;
use FlyCompany\Scraper\BadmintonPlayer;
use FlyCompany\Scraper\BadmintonPlayerHelper;
use FlyCompany\TeamFight\Models\SerializerHelper;
use FlyCompany\TeamFight\Models\Squad;
use FlyCompany\TeamFight\TeamManager;
use FlyCompany\TeamFight\TeamValidator;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class Test extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:name';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     *
     * @return int
     * @throws \JsonException
     */
    public function handle(TeamManager $teamManager ,BadmintonPlayer $scraper)
    {
        $versions = $scraper->getVersions(BadmintonPlayerHelper::getCurrentSeason());
        $rankingMonths = BadmintonPlayerHelper::filterToRankingMonths($versions);

        dd($rankingMonths);
    }
}
