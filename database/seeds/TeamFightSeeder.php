<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TeamFightSeeder extends Seeder
{
    /**
     * Seed 4 premade team fights (holdrunder) with 3x 13-kamps hold each:
     *
     *   1. "3x13 Kamps - Valid"              — all squads correctly filled
     *   2. "3x13 Kamps - Ufuldstændigt hold" — squad 3 has empty 4.HS slot
     *   3. "3x13 Kamps - Forkert kategori"   — within-squad ranking violation
     *   4. "3x13 Kamps - Forkert hold"       — cross-squad ranking violation
     */
    public function run(): void
    {
        $data = require __DIR__ . '/team_fights_data.php';

        DB::table('team_rounds')->insert($data['teams']);

        foreach (array_chunk($data['squads'], 100) as $chunk) {
            DB::table('squads')->insert($chunk);
        }

        foreach (array_chunk($data['squad_categories'], 100) as $chunk) {
            DB::table('squad_categories')->insert($chunk);
        }

        foreach (array_chunk($data['squad_members'], 100) as $chunk) {
            DB::table('squad_members')->insert($chunk);
        }

        foreach (array_chunk($data['squad_points'], 100) as $chunk) {
            DB::table('squad_points')->insert($chunk);
        }
    }
}
