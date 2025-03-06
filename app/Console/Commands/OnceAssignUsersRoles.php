<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class OnceAssignUsersRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:once-assign-users-roles';

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
        //User::query()->where('email', 'danielflynygaard@gmail.com')->first()->assignRole('super-admin');

        foreach (User::all() as $user) {
            setPermissionsTeamId($user->clubhouse_id);
            $user->assignRole('coach');
            $user->assignRole('club-admin');
        }
    }
}
