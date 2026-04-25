<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ClubSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $clubs = require __DIR__ . '/clubs_data.php';

        foreach ($clubs as $club) {
            \App\Models\Club::query()->updateOrCreate([
                'id' => $club['id'],
            ], $club);
        }
    }
}
