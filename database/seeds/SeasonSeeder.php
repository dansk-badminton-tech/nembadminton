<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Season;
use Illuminate\Database\Seeder;

class SeasonSeeder extends Seeder
{
    /**
     * Seed a baseline of badminton seasons. Season id is the calendar year
     * in which the season starts (July cutoff); season_name follows the
     * "YYYY/YYYY+1" convention used elsewhere.
     *
     * Idempotent.
     */
    public function run(): void
    {
        foreach (range(2020, 2030) as $year) {
            Season::query()->updateOrCreate(
                ['id' => $year],
                ['season_name' => $year . '/' . ($year + 1)],
            );
        }
    }
}
