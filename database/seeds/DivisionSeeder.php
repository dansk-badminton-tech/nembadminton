<?php

namespace Database\Seeders;

use App\Models\Division;
use Illuminate\Database\Seeder;

class DivisionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $displayOrder = 1;

        // Badminton Danmark
        Division::create(['name' => 'Badmintonligaen', 'code' => 'BL', 'display_order' => $displayOrder++, 'created_by_system' => true]);
        Division::create(['name' => '1. division', 'code' => '1DIV', 'display_order' => $displayOrder++, 'created_by_system' => true]);
        Division::create(['name' => '2. division', 'code' => '2DIV', 'display_order' => $displayOrder++, 'created_by_system' => true]);
        Division::create(['name' => '3. division', 'code' => '3DIV', 'display_order' => $displayOrder++, 'created_by_system' => true]);
        Division::create(['name' => 'Danmarksserien', 'code' => 'DMS', 'display_order' => $displayOrder++, 'created_by_system' => true]);

        // Badminton Fyn
        Division::create(['name' => 'Kredsserien Vest', 'code' => 'KSV_FYN', 'display_order' => $displayOrder++, 'created_by_system' => true]);

        // Badminton København
        Division::create(['name' => 'Københavnsserien', 'code' => 'KHS', 'display_order' => $displayOrder++, 'created_by_system' => true]);
        Division::create(['name' => 'Københavnsserien – spilletider i slutspillet', 'code' => 'KHS_SLUT', 'display_order' => $displayOrder++, 'created_by_system' => true]);
        Division::create(['name' => '1. Serie', 'code' => '1SER_KBH', 'display_order' => $displayOrder++, 'created_by_system' => true]);
        Division::create(['name' => '2. Serie', 'code' => '2SER_KBH', 'display_order' => $displayOrder++, 'created_by_system' => true]);
        Division::create(['name' => '3. Serie', 'code' => '3SER_KBH', 'display_order' => $displayOrder++, 'created_by_system' => true]);
        Division::create(['name' => '31. Serie', 'code' => '31SER', 'display_order' => $displayOrder++, 'created_by_system' => true]);
        Division::create(['name' => '32. Serie', 'code' => '32SER', 'display_order' => $displayOrder++, 'created_by_system' => true]);

        // Badminton Midtjylland
        Division::create(['name' => 'Kredsserien Vest', 'code' => 'KSV_MIDTJYL', 'display_order' => $displayOrder++, 'created_by_system' => true]);

        // Badminton Nordjylland
        Division::create(['name' => 'Kredsserien Vest', 'code' => 'KSV_NORDJYL', 'display_order' => $displayOrder++, 'created_by_system' => true]);

        // Badminton Sjælland
        Division::create(['name' => 'Sjællandsserien', 'code' => 'SJS', 'display_order' => $displayOrder++, 'created_by_system' => true]);
        Division::create(['name' => 'Sjællandsserien Slutspil – Spilletider', 'code' => 'SJS_SLUT', 'display_order' => $displayOrder++, 'created_by_system' => true]);
        Division::create(['name' => 'Serie 1', 'code' => 'S1_SJL', 'display_order' => $displayOrder++, 'created_by_system' => true]);
        Division::create(['name' => 'Serie 1 Slutspil – Spilletider', 'code' => 'S1_SJL_SLUT', 'display_order' => $displayOrder++, 'created_by_system' => true]);
        Division::create(['name' => 'Serie 2', 'code' => 'S2_SJL', 'display_order' => $displayOrder++, 'created_by_system' => true]);
        Division::create(['name' => 'Serie 2 Slutspil – Spilletider', 'code' => 'S2_SJL_SLUT', 'display_order' => $displayOrder++, 'created_by_system' => true]);
        Division::create(['name' => 'Serie 3', 'code' => 'S3_SJL', 'display_order' => $displayOrder++, 'created_by_system' => true]);
        Division::create(['name' => '4+2', 'code' => '4P2_SJL', 'display_order' => $displayOrder++, 'created_by_system' => true]);
        Division::create(['name' => '4+2 – Slutspil – Spilletider', 'code' => '4P2_SJL_SLUT', 'display_order' => $displayOrder++, 'created_by_system' => true]);
        Division::create(['name' => '4 Spillere', 'code' => '4SP_SJL', 'display_order' => $displayOrder++, 'created_by_system' => true]);
        Division::create(['name' => '4 Spillere – Slutspil – Spilletider', 'code' => '4SP_SJL_SLUT', 'display_order' => $displayOrder++, 'created_by_system' => true]);

        // Badminton Sønderjylland
        Division::create(['name' => 'Kredsserien Vest', 'code' => 'KSV_SONDJYL', 'display_order' => $displayOrder++, 'created_by_system' => true]);
    }
}
