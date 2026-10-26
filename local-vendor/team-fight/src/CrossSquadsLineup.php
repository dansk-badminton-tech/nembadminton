<?php

declare(strict_types=1);

namespace FlyCompany\TeamFight;

use App\Models\Member;
use App\Models\Point;
use App\Models\Squad;
use App\Models\SquadCategory;
use App\Models\SquadMember;
use App\Models\SquadPoint;
use App\Models\TeamRound;
use Carbon\Carbon;

/**
 * A TeamRound's lineup with the points that decide which Squad a player may stand on (§38 stk. 4): the Members' points
 * on the TeamRound's Ranking List, for every Squad. A Squad's stored points, with a coach's corrections or points from
 * its own Ranking List, only decide the order on the Squad (stk. 2-3). A player without points on the Ranking List has
 * nothing to compare, like an unranked player. A TeamRound without a Ranking List uses the Squads' stored points.
 */
class CrossSquadsLineup
{
    public function __construct(
        private readonly ScenarioManager $scenarioManager
    ) {}

    /**
     * @return list<array<string, mixed>> the Squads in order, shaped like the ValidateSquad input
     */
    public function of(TeamRound $teamRound, ?int $scenarioId): array
    {
        $version = self::date($teamRound->version);

        $lineup = [];
        $refIds = [];
        foreach ($teamRound->squads as $squad) {
            $categories = $this->scenarioManager->getCategories($squad, $scenarioId);
            foreach ($categories as $category) {
                foreach ($category->players as $player) {
                    $refIds[] = $player->member_ref_id;
                }
            }
            $lineup[] = [$squad, $categories];
        }

        $rankingPoints = $version === null ? null : $this->rankingPoints($version, $refIds);

        return array_map(static fn (array $entry) => self::squad(...$entry, rankingPoints: $rankingPoints), $lineup);
    }

    /**
     * @param  iterable<SquadCategory>  $categories
     * @param  array<string, list<array<string, mixed>>>|null  $rankingPoints  by refId, or null to use the stored points
     * @return array<string, mixed>
     */
    private static function squad(Squad $squad, iterable $categories, ?array $rankingPoints): array
    {
        $players = static fn (SquadCategory $category) => collect($category->players)->map(fn (SquadMember $player) => [
            'id' => $player->id,
            'gender' => $player->gender,
            'name' => $player->name,
            'refId' => $player->member_ref_id,
            'points' => $rankingPoints === null
                ? array_map(self::point(...), collect($player->points)->all())
                : $rankingPoints[$player->member_ref_id] ?? [],
        ])->all();

        return [
            'id' => (string) $squad->id,
            'playerLimit' => $squad->playerLimit,
            'isPaperSquad' => $squad->is_paper_squad,
            'categories' => collect($categories)->map(fn (SquadCategory $category) => [
                'category' => $category->category,
                'name' => $category->name,
                'players' => $players($category),
            ])->all(),
        ];
    }

    /**
     * @param  list<string>  $refIds
     * @return array<string, list<array<string, mixed>>> the Members' points on the Ranking List, by refId
     */
    private function rankingPoints(string $version, array $refIds): array
    {
        if ($refIds === []) {
            return [];
        }

        /** @var array<int, string> $refIdOf by Member id */
        $refIdOf = Member::query()->whereIn('refId', array_unique($refIds))->pluck('refId', 'id')->all();

        $points = [];
        foreach (Point::query()->whereIn('member_id', array_keys($refIdOf))->where('version', $version)->orderBy('id')->get() as $point) {
            $points[$refIdOf[$point->member_id]][] = self::point($point);
        }

        return $points;
    }

    /**
     * @return array<string, mixed>
     */
    private static function point(Point|SquadPoint $point): array
    {
        return [
            'category' => $point->category,
            'points' => $point->points,
            'position' => $point->position,
            'vintage' => $point->vintage,
        ];
    }

    private static function date(mixed $value): ?string
    {
        return $value === null ? null : Carbon::parse($value)->toDateString();
    }
}
