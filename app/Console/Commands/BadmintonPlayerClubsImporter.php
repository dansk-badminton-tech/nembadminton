<?php
declare(strict_types = 1);


namespace App\Console\Commands;

use App\Import\Import;
use App\Models\Club;
use FlyCompany\Scraper\BadmintonPlayer;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

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

        $positiveClubs = array_filter($clubs, static function ($club) {
            return $club['id'] > 0;
        });
        $negativeClubs = array_filter($clubs, static function ($club) {
            return $club['id'] < 0;
        });

        foreach ($positiveClubs as $club) {
            \App\Models\Club::query()->updateOrCreate([
                'id' => $club['id'],
            ], [
                'name1'             => $club['name'],
                'badmintonPlayerId' => $club['id'],
            ]);
            $this->info("Update/Creates {$club['name']}");
        }

        foreach ($negativeClubs as $club) {
            $clubId = $club['id'] * -1;
            $clubId = (int)str_pad((string)$clubId, 6, '0');
            \App\Models\Club::query()->updateOrCreate([
                'id' => $clubId,
            ], [
                'name1'             => $club['name'],
                'badmintonPlayerId' => $club['id'],
            ]);
            $this->info("Update/Creates {$club['name']}");
        }

        return 0;
    }
}
