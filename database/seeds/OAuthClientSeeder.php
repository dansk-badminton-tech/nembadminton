<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OAuthClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $oauth_clients = require __DIR__ . '/oauth_clients_data.php';

        DB::table('oauth_clients')->insert($oauth_clients);
    }
}
