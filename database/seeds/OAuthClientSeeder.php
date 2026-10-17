<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class OAuthClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $oauth_clients = array_map(
            static fn (array $client) => ['secret' => Hash::make($client['secret'])] + $client,
            require __DIR__ . '/oauth_clients_data.php'
        );

        DB::table('oauth_clients')->insert($oauth_clients);
    }
}
