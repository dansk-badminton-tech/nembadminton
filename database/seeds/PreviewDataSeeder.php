<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * A single BC37 Season for bin/preview, independent of browser-test fixtures.
 */
class PreviewDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(PreviewClubDataSeeder::class);
        $this->call(OAuthClientSeeder::class);
        $this->call(RolesAndPermissionsSeeder::class);
        $this->callWith(ClubhouseSeeder::class, ['params' => ['email' => 'testing@gmail.com', 'name' => 'Previewbruger']]);
        DB::table('clubhouses')->where('id', 1)->update(['name' => 'BC37 Amager · Eksempel', 'email' => 'testing@gmail.com']);
        $this->call(ModelHasRoleSeeder::class);
        DB::table('seasons')->insert(['id' => 2025, 'season_name' => '2025/2026']);
        $this->call(TournamentTierSeeder::class);
        $this->call(PreviewSeasonSeeder::class);
    }
}
