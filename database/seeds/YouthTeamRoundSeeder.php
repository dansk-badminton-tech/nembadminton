<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class YouthTeamRoundSeeder extends Seeder
{
    public const CONFLICT_ROUND = 'Ungdom - 3. runde (konflikter)';

    private const RANKING_VERSION = '2025-07-02';

    private const SEASON_ID = 2025;

    private const CLUBHOUSE_ID = 1;

    private const USER_ID = 1;

    private const PLAYER_LIMIT = 10;

    /**
     * Seed the "Ungdom" holdrunder described in youth_team_rounds_data.php.
     *
     * Needs MemberSeeder, PointSeeder, SeasonSeeder and ClubhouseSeeder first.
     */
    public function run(): void
    {
        $rounds = require __DIR__.'/youth_team_rounds_data.php';

        foreach ($rounds as $round) {
            DB::table('team_rounds')->insert([
                'id' => $round['id'],
                'name' => $round['name'],
                'user_id' => self::USER_ID,
                'game_date' => $round['game_date'],
                'version' => self::RANKING_VERSION,
                'clubhouse_id' => self::CLUBHOUSE_ID,
                'season_id' => self::SEASON_ID,
                'round' => $round['round'],
            ]);

            foreach ($round['squads'] as $index => $categories) {
                $squadId = DB::table('squads')->insertGetId([
                    'playerLimit' => self::PLAYER_LIMIT,
                    'team_round_id' => $round['id'],
                    'order' => $index + 1,
                ]);

                foreach ($categories as $name => $players) {
                    $this->seedCategory($squadId, $name, $players);
                }
            }
        }
    }

    /**
     * @param  string  $name  Category name such as "1. HS"; the category is the part after the number
     * @param  string[]  $players  Member names
     */
    private function seedCategory(int $squadId, string $name, array $players): void
    {
        $categoryId = DB::table('squad_categories')->insertGetId([
            'category' => explode(' ', $name, 2)[1],
            'name' => $name,
            'squad_id' => $squadId,
        ]);

        foreach ($players as $playerName) {
            $member = DB::table('members')->where('name', $playerName)->sole();

            $squadMemberId = DB::table('squad_members')->insertGetId([
                'name' => $member->name,
                'gender' => $member->gender,
                'member_ref_id' => $member->refId,
                'squad_category_id' => $categoryId,
            ]);

            $points = DB::table('points')
                ->where('member_id', $member->id)
                ->where('version', self::RANKING_VERSION)
                ->get()
                ->map(fn (object $point) => [
                    'points' => $point->points,
                    'category' => $point->category,
                    'position' => $point->position,
                    'squad_member_id' => $squadMemberId,
                    'vintage' => $point->vintage,
                    'corrected_manually' => 0,
                    'version' => self::RANKING_VERSION,
                ])
                ->all();

            DB::table('squad_points')->insert($points);
        }
    }
}
