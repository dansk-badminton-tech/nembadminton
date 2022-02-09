<?php

namespace FlyCompany\BadmintonPlayerImport\Commands;

use App\Jobs\BadmintonPlayerImportPoints;
use FlyCompany\BadmintonPlayerImport\Jobs\ImportPoints;
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
        ImportPoints::dispatchSync($this->argument('club-id'));

        return 0;
    }

}