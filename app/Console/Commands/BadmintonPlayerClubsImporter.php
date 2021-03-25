<?php
declare(strict_types = 1);


namespace App\Console\Commands;

use App\Import\Import;
use FlyCompany\Scraper\BadmintonPlayer;
use Illuminate\Console\Command;

class BadmintonPlayerClubsImporter extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'badmintonplayer-import:clubs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import clubs from badmintonplayer.dk';

    /**
     * Execute the console command.
     *
     * @param Import $import
     *
     * @return int
     */
    public function handle(BadmintonPlayer $badmintonPlayer)
    {
        $clubs = $badmintonPlayer->getClubs();

        foreach ($clubs as $club) {
            $clubId = $club['id'];
            if (!is_numeric($clubId)) {
                continue;
            }
            \App\Models\Club::updateOrCreate([
                'id' => $clubId,
            ], [
                'name1' => $club['name'],
            ]);
        }

        return 0;
    }
}
