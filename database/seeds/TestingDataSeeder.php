<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TestingDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call(ClubSeeder::class);

        $this->call(MemberSeeder::class);

        $this->call(ClubMemberSeeder::class);

        $this->call(OAuthClientSeeder::class);

        $this->call(RolesAndPermissionsSeeder::class);

        $this->callWith(ClubhouseSeeder::class, ['params' => ['email' => 'testing@gmail.com']]);

        $this->call(ModelHasRoleSeeder::class);

        $this->call(PointSeeder::class);

        $this->call(TeamFightSeeder::class);
    }
}
