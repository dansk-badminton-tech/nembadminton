<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class OneTimeMigrateClubToClubs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'one-time:migrate-club-data-to-clubs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        /** @var User[] $users */
        $users = User::all();
        foreach ($users as $user){
            $this->info("Creating connection to club for {$user->email} to {$user->organization_id}");
            $user->clubs()->syncWithoutDetaching([$user->organization_id]);
        }
        return 0;
    }
}
