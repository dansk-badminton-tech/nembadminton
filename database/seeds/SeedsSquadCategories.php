<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;

/**
 * Inserts a category of a SeasonLineupGenerator lineup, with its players and their points at the version.
 */
trait SeedsSquadCategories
{
    /**
     * @param  array{category: string, name: string, players: list<array<string, mixed>>}  $category
     */
    private function seedCategory(int $squadId, ?int $scenarioId, array $category, string $version): void
    {
        $categoryId = DB::table('squad_categories')->insertGetId([
            'category' => $category['category'],
            'name' => $category['name'],
            'squad_id' => $squadId,
            'team_round_scenario_id' => $scenarioId,
        ]);

        foreach ($category['players'] as $player) {
            $squadMemberId = DB::table('squad_members')->insertGetId([
                'name' => $player['name'],
                'gender' => $player['gender'],
                'member_ref_id' => $player['refId'],
                'squad_category_id' => $categoryId,
            ]);

            DB::table('squad_points')->insert(array_map(fn (object $point) => [
                'points' => $point->points,
                'category' => $point->category,
                'position' => $point->position,
                'squad_member_id' => $squadMemberId,
                'vintage' => $point->vintage,
                'corrected_manually' => 0,
                'version' => $version,
            ], $player['versions'][$version]['rows']));
        }
    }
}
