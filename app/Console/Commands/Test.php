<?php


namespace App\Console\Commands;

use FlyCompany\TeamFight\Models\SerializerHelper;
use FlyCompany\TeamFight\Models\Squad;
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
    public function handle(TeamValidator $teamValidator)
    {
        $serializer = SerializerHelper::getSerializer();
        $data = require __DIR__.'/cross-team-check-two-wrong-players-in-two-categories.php';
        $data1 = require __DIR__.'/squads-cross-team-player-in-one-categories.php';
        $data2 = require __DIR__.'/squads-cross-team-player-in-two-categories.php';
        $run = function($data) use ($teamValidator, $serializer) {
            $this->info("Running ...");
            $squads = (new Collection($data))->pluck('squad');
            $squads = $serializer->denormalize($squads->toArray(), Squad::class . '[]');
            $oldResult = $teamValidator->validateCrossSquads($squads);
            dump($teamValidator->validateCrossSquadsV2($squads));
        };
        $run($data);
        $run($data1);
        $run($data2);
        return 0;
    }
}
