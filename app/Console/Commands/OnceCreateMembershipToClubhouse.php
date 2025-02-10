<?php

namespace App\Console\Commands;

use App\Models\Clubhouse;
use App\Models\User;
use Illuminate\Console\Command;

class OnceCreateMembershipToClubhouse extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:once-create-membership-to-clubhouse';

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
        /** @var User $user */
        foreach (User::all() as $user){
            /** @var Clubhouse $clubHouse */
            $clubHouse = Clubhouse::query()->firstOrCreate([
                'email' => $user->email
            ], [
                'name' => $user->club()->first()->name1
            ]);

            $clubHouse->users()->sync($user);
            $user->clubhouse()->associate($clubHouse)->save();

            $clubHouse->clubs()->sync($user->clubs);
        }
    }
}
