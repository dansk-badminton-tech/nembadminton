<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class MemberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $members = require __DIR__ . '/members_data.php';

        foreach ($members as $member) {
            \App\Models\Member::query()->updateOrCreate([
                'id' => $member['id'],
            ], $member);
        }
    }
}
