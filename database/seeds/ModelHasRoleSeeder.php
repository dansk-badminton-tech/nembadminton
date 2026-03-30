<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModelHasRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $model_has_role = require __DIR__ . '/model_has_role_data.php';

        DB::table('model_has_roles')->insert($model_has_role);
    }
}
