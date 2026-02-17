<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TestingDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clubs = require __DIR__ . '/clubs_data.php';

        foreach ($clubs as $club) {
            \App\Models\Club::query()->updateOrCreate([
                'id' => $club['id'],
            ], $club);
        }

        $members = require __DIR__ . '/members_data.php';

        foreach ($members as $member) {
            \App\Models\Member::query()->updateOrCreate([
                'id' => $member['id'],
            ], $member);
        }

        $club_member_relation = require __DIR__ . '/club_member_relation_data.php';

        DB::table('club_member')->insert($club_member_relation);

    }
}
