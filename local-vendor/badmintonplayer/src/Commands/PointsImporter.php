<?php

namespace FlyCompany\BadmintonPlayer\Commands;

use App\Jobs\BadmintonPlayerImportPoints;
use FlyCompany\BadmintonPlayer\Jobs\ImportPoints;
use FlyCompany\BadmintonPlayerAPI\RankingPeriodType;
use FlyCompany\Members\PointsManager;
use FlyCompany\Scraper\BadmintonPlayer;
use Illuminate\Console\Command;

class PointsImporter extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'badmintonplayer-api-import:points {club-id : BadmintonPlayer club id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importing points from badmintonplayer.dk public api';

    /**
     * Execute the console command.
     *
     * @return int
     * @throws \JsonException
     */
    public function handle() : int
    {
        ImportPoints::dispatch((int)$this->argument('club-id'), RankingPeriodType::CURRENT);
        ImportPoints::dispatch((int)$this->argument('club-id'), RankingPeriodType::PREVIOUS);

        return 0;
    }

}
