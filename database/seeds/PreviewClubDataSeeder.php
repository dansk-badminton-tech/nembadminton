<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/** Public Member/ranking fields only; never imports production accounts or contacts. */
class PreviewClubDataSeeder extends Seeder
{
    public static function fixture(): array
    {
        return json_decode(file_get_contents(__DIR__.'/fixtures/preview_bc37_2025.json'), true, flags: JSON_THROW_ON_ERROR);
    }

    public function run(): void
    {
        $data = self::fixture();
        DB::table('clubs')->insert($data['club']);
        $memberIds = [];
        foreach ($data['members'] as $member) {
            $id = DB::table('members')->insertGetId([
                ...$member,
                'playable' => true,
                'inactive' => false,
            ]);
            $memberIds[$member['refId']] = $id;
            // Historical participants who since changed club remain available in this historical showcase.
            DB::table('club_member')->insert(['club_id' => 1087, 'member_id' => $id]);
        }

        foreach (array_chunk($data['points'], 200) as $points) {
            DB::table('points')->insert(array_map(function (array $point) use ($memberIds): array {
                $point['member_id'] = $memberIds[$point['refId']];
                unset($point['refId']);

                return $point;
            }, $points));
        }
    }
}
