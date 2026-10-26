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
 * A TeamRound's lineup with the points that decide which Squad a player may stand on (§38 stk. 4): those on the
 * TeamRound's Ranking List. A Squad on that Ranking List uses its stored points, so a coach's correction counts. A
 * Squad with its own Ranking List stores points from that one, so it uses the Members' points on the TeamRound's.
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
        $ownRankingRefIds = [];
        foreach ($teamRound->squads as $squad) {
            $categories = $this->scenarioManager->getCategories($squad, $scenarioId);
            $ownRanking = $version !== null && $squad->version !== null && self::date($squad->version) !== $version;
            if ($ownRanking) {
                foreach ($categories as $category) {
                    foreach ($category->players as $player) {
                        $ownRankingRefIds[] = $player->member_ref_id;
                    }
                }
            }
            $lineup[] = [$squad, $categories, $ownRanking];
        }

        $rankingPoints = $this->rankingPoints($version, $ownRankingRefIds);

        return array_map(static fn (array $entry) => self::squad(...$entry, rankingPoints: $rankingPoints), $lineup);
    }

    /**
     * @param  iterable<SquadCategory>  $categories
     * @param  array<string, list<array<string, mixed>>>  $rankingPoints  by refId
     * @return array<string, mixed>
     */
    private static function squad(Squad $squad, iterable $categories, bool $ownRanking, array $rankingPoints): array
    {
        $players = static fn (SquadCategory $category) => collect($category->players)->map(fn (SquadMember $player) => [
            'id' => $player->id,
            'gender' => $player->gender,
            'name' => $player->name,
            'refId' => $player->member_ref_id,
            'points' => $ownRanking
                ? $rankingPoints[$player->member_ref_id] ?? []
                : array_map(self::point(...), collect($player->points)->all()),
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
    private function rankingPoints(?string $version, array $refIds): array
    {
        if ($version === null || $refIds === []) {
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
