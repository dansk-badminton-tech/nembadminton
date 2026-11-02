<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * The database of bin/preview: the browser tests' data (TestingDataSeeder), without the youth TeamRounds, plus showcases that would change what the
 * browser tests see, such as Teams and TeamRounds in the current Season.
 */
class PreviewDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->callWith(TestingDataSeeder::class, ['includeYouthTeamRounds' => false]);

        $this->call(PaperSquadSeasonSeeder::class);
    }
}
