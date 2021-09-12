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

//        foreach ($negativeClubs as $club) {
//            $clubId = $club['id'];
//            $model = Club::query()->where('id', $clubId * -1)->first();
//            if($model !== null && $model->name1 === $club['name']){
//                \App\Models\Club::query()->updateOrCreate([
//                    'id'    => $clubId * -1,
//                ], [
//                    'name1'             => $club['name'],
//                    'badmintonPlayerId' => $club['id'],
//                ]);
//            }
//            $this->info("Update/Creates {$club['name']}");
//        }

        return 0;
    }
}
