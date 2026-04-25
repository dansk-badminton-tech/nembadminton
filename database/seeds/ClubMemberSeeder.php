<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClubMemberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $club_member_relation = require __DIR__ . '/club_member_relation_data.php';

        DB::table('club_member')->insert($club_member_relation);
    }
}
