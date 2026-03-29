<?php

namespace Database\Seeders;

use App\Models\Club;
use App\Models\Clubhouse;
use App\Models\User;
use Illuminate\Database\Seeder;

class ClubhouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(array $params): void
    {
        Clubhouse::factory()
            ->count(1)
            ->create()
            ->each(function(Clubhouse $clubhouse) use ($params) {
                $clubhouse->clubs()->save(Club::query()->findOrFail(1087));
                $user = User::factory()->state($params)->createOne();
                $user->clubhouse()->associate($clubhouse);
                $clubhouse->users()->save($user);
            });
    }
}
