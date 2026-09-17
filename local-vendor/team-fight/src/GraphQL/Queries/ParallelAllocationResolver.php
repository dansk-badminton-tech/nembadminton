<?php

declare(strict_types=1);

namespace FlyCompany\TeamFight\GraphQL\Queries;

use App\Models\Member;
use App\Models\SquadCategory;
use App\Models\SquadMember;
use App\Models\TeamRound;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class ParallelAllocationResolver
{
    /**
     * Cache lookups within the same HTTP/GraphQL request keyed by teamRoundId.
     *
     * @var array<string, array<string, array<string, mixed>>>
     */
    private static array $requestCache = [];

    /**
     * Cache mapping squad_category_id to team_round_id within the same request.
     *
     * @var array<int|string, string>
     */
    private static array $categoryToRoundCache = [];

    /**
     * Resolve the parallel team round allocation for a given member in the context of an active team round.
     *
     * @param Member|SquadMember|array<string, mixed> $root
     * @param array<string, mixed> $args
     * @param GraphQLContext $context
     * @param ResolveInfo $resolveInfo
     * @return array<string, mixed>|null
     */
    public function __invoke(mixed $root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): ?array
    {
        $teamRoundId = (string) ($args['teamRoundId'] ?? '');
        if ($teamRoundId === '') {
            if ($root instanceof SquadMember) {
                // Check if relations are already loaded in memory first
                if ($root->relationLoaded('category') && $root->category?->relationLoaded('squad') && $root->category->squad?->team_round_id !== null) {
                    $teamRoundId = (string) $root->category->squad->team_round_id;
                } else {
                    $categoryId = $root->squad_category_id;
                    if ($categoryId !== null) {
                        if (!isset(self::$categoryToRoundCache[$categoryId])) {
                            // Query squad directly via category id
                            $roundId = SquadCategory::query()
                                ->join('squads as s', 'squad_categories.squad_id', '=', 's.id')
                                ->where('squad_categories.id', $categoryId)
                                ->value('s.team_round_id');
                            self::$categoryToRoundCache[$categoryId] = (string) ($roundId ?? '');
                        }
                        $teamRoundId = self::$categoryToRoundCache[$categoryId];
                    }
                }
            }
        }
        if ($teamRoundId === '') {
            return null;
        }

        $user = $context->user();
        $clubhouseId = $user ? $user->clubhouse_id : null;

        if (!isset(self::$requestCache[$teamRoundId])) {
            self::$requestCache[$teamRoundId] = $this->loadAllocationsForTeamRound($teamRoundId, $clubhouseId);
        }

        $refId = is_object($root) ? ($root->refId ?? $root->member_ref_id ?? null) : ($root['refId'] ?? null);
        if ($refId === null) {
            return null;
        }

        return self::$requestCache[$teamRoundId][$refId] ?? null;
    }

    /**
     * Load all parallel allocations for other team rounds sharing the same clubhouse, season, and round number.
     *
     * @param string $teamRoundId
     * @param int|null $clubhouseId
     * @return array<string, array<string, mixed>> Keyed by member_ref_id
     */
    private function loadAllocationsForTeamRound(string $teamRoundId, ?int $clubhouseId): array
    {
        /** @var TeamRound|null $activeRound */
        $roundQuery = TeamRound::query()->where('id', $teamRoundId);
        if ($clubhouseId !== null) {
            $roundQuery->where('clubhouse_id', $clubhouseId);
        }
        $activeRound = $roundQuery->first();
        if ($activeRound === null || $activeRound->round === null || $activeRound->season_id === null || $activeRound->clubhouse_id === null) {
            return [];
        }

        $rows = SquadMember::query()
            ->join('squad_categories as sc', 'squad_members.squad_category_id', '=', 'sc.id')
            ->join('squads as s', 'sc.squad_id', '=', 's.id')
            ->join('team_rounds as tr', 's.team_round_id', '=', 'tr.id')
            ->where('tr.clubhouse_id', '=', $activeRound->clubhouse_id)
            ->where('tr.season_id', '=', $activeRound->season_id)
            ->where('tr.round', '=', $activeRound->round)
            ->where('tr.id', '!=', $activeRound->id)
            ->orderBy('s.order', 'asc')
            ->orderBy('tr.id', 'asc')
            ->select([
                'squad_members.member_ref_id',
                'tr.id as team_round_id',
                'tr.name as team_round_name',
                'tr.round',
                's.name as squad_name',
                's.order as squad_order',
                'sc.name as category_name',
            ])
            ->get();

        $lookup = [];
        foreach ($rows as $row) {
            if ($row->member_ref_id === null) {
                continue;
            }

            // If player appears in multiple parallel rounds/squads, keep the first recorded one
            if (!isset($lookup[$row->member_ref_id])) {
                $lookup[$row->member_ref_id] = [
                    'teamRoundId' => (string) $row->team_round_id,
                    'teamRoundName' => $row->team_round_name ?? ('Runde ' . $row->round),
                    'squadName' => $row->squad_name,
                    'squadOrder' => $row->squad_order !== null ? (int) $row->squad_order : null,
                    'categoryName' => $row->category_name,
                ];
            }
        }

        return $lookup;
    }

    /**
     * Clear the static request cache (primarily for test teardowns).
     */
    public static function clearCache(): void
    {
        self::$requestCache = [];
        self::$categoryToRoundCache = [];
    }
}
