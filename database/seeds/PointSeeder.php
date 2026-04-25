<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PointSeeder extends Seeder
{
    /**
     * Seed points data exported from the local database.
     * Covers 293 members across 6 ranking versions.
     */
    public function run(): void
    {
        $points = require __DIR__ . '/points_data.php';

        // Insert in chunks to avoid hitting MySQL max packet size
        foreach (array_chunk($points, 500) as $chunk) {
            DB::table('points')->insert($chunk);
        }
    }
}
