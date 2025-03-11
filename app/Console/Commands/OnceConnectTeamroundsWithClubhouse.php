<?php

namespace App\Console\Commands;

use App\Models\CancellationCollector;
use App\Models\Teams;
use App\Models\User;
use Illuminate\Console\Command;

class OnceConnectTeamroundsWithClubhouse extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:once-connect-teamrounds-with-clubhouse';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        foreach (Teams::all() as $team) {
            $user = $team->user;
            if($user !== null){
                $clubhouse = $user->clubhouse;
                $team->clubhouse()->associate($clubhouse);
                $team->save();
            }
        }
    }
}
