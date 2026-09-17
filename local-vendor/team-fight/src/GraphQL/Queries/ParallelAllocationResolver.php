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
     * In-memory cache mapping teamRoundId -> [memberRefId => allocationData]
     * Keeps track of parallel allocations per round so we only run the database query once per GraphQL request.
     *
     * @var array<string, array<string, array<string, mixed>>>
     */
    private static array $roundAllocationsCache = [];

    /**
     * In-memory cache mapping squadCategoryId -> teamRoundId
     * Avoids re-querying which team round a squad category belongs to when resolving multiple players.
     *
     * @var array<int|string, string>
     */
    private static array $categoryToRoundCache = [];

    /**
     * Resolve the parallel team round allocation for a given member.
     *
     * This resolver handles two scenarios:
     * 1. Searching for players (`Member` model):
     *    Lighthouse calls this with `teamRoundId` passed explicitly in `$args`.
     * 2. Viewing team round lineups (`SquadMember` model):
     *    Lighthouse calls this for each player on a squad. `$args['teamRoundId']` is empty,
     *    so we derive the team round from the player's category/squad hierarchy.
     */
    public function __invoke(mixed $root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): ?array
    {
        // Step 1: Determine which team round is currently active
        $activeTeamRoundId = $this->resolveActiveTeamRoundId($root, $args);
        if ($activeTeamRoundId === '') {
            return null;
        }

        // Step 2: Extract the player's unique ranking reference ID (badmintonplayer.dk refId)
        $memberRefId = $this->extractMemberRefId($root);
        if ($memberRefId === null) {
            return null;
        }

        // Step 3: Fetch (or read from request cache) all parallel allocations for this team round
        $user = $context->user();
        $clubhouseId = $user ? $user->clubhouse_id : null;

        if (!isset(self::$roundAllocationsCache[$activeTeamRoundId])) {
            self::$roundAllocationsCache[$activeTeamRoundId] = $this->loadAllocationsForTeamRound($activeTeamRoundId, $clubhouseId);
        }

        // Step 4: Return any parallel allocation found for this player in other rounds
        return self::$roundAllocationsCache[$activeTeamRoundId][$memberRefId] ?? null;
    }

    /**
     * Determine the active team round ID either from GraphQL arguments or the parent SquadMember.
     */
    private function resolveActiveTeamRoundId(mixed $root, array $args): string
    {
        // 1. Explicit argument provided (e.g. from memberSearchPoints query)
        if (!empty($args['teamRoundId'])) {
            return (string) $args['teamRoundId'];
        }

        // 2. Invoked on a SquadMember model in a teamRound query
        if ($root instanceof SquadMember) {
            // Use in-memory Eloquent relations if already loaded
            if ($root->relationLoaded('category') && $root->category?->relationLoaded('squad') && $root->category->squad?->team_round_id !== null) {
                return (string) $root->category->squad->team_round_id;
            }

            // Otherwise, look up and cache the team_round_id for this category
            $categoryId = $root->squad_category_id;
            if ($categoryId !== null) {
                if (!isset(self::$categoryToRoundCache[$categoryId])) {
                    $roundId = SquadCategory::query()
                        ->join('squads as s', 'squad_categories.squad_id', '=', 's.id')
                        ->where('squad_categories.id', $categoryId)
                        ->value('s.team_round_id');

                    self::$categoryToRoundCache[$categoryId] = (string) ($roundId ?? '');
                }

                return self::$categoryToRoundCache[$categoryId];
            }
        }

        return '';
    }

    /**
     * Extract the player refId across Member models, SquadMember models, or array shapes.
     */
    private function extractMemberRefId(mixed $root): ?string
    {
        if (is_object($root)) {
            return $root->refId ?? $root->member_ref_id ?? null;
        }

        return $root['refId'] ?? $root['member_ref_id'] ?? null;
    }

    /**
     * Load all parallel allocations across other team rounds in the same clubhouse, season, and round number.
     *
     * Example:
     * Active round: Clubhouse A, Season 2025/2026, Round 2 (Senior 1)
     * Matches any player assigned to: Clubhouse A, Season 2025/2026, Round 2 (Senior 2, Senior 3, etc.)
     *
     * @return array<string, array<string, mixed>> Keyed by member_ref_id
     */
    private function loadAllocationsForTeamRound(string $teamRoundId, ?int $clubhouseId): array
    {
        // Fetch active round metadata to identify the matching scope (season and round number)
        $roundQuery = TeamRound::query()->where('id', $teamRoundId);
        if ($clubhouseId !== null) {
            $roundQuery->where('clubhouse_id', $clubhouseId);
        }
        /** @var TeamRound|null $activeRound */
        $activeRound = $roundQuery->first();

        // If the active round has incomplete metadata, no parallel collision can be safely determined
        if ($activeRound === null || $activeRound->round === null || $activeRound->season_id === null || $activeRound->clubhouse_id === null) {
            return [];
        }

        // Query all players on other team rounds matching:
        // - same clubhouse
        // - same season
        // - same round number
        // - different team round ID
        $rows = SquadMember::query()
            ->join('squad_categories as sc', 'squad_members.squad_category_id', '=', 'sc.id')
            ->join('squads as s', 'sc.squad_id', '=', 's.id')
            ->join('team_rounds as tr', 's.team_round_id', '=', 'tr.id')
            ->where('tr.clubhouse_id', '=', $activeRound->clubhouse_id)
            ->where('tr.season_id', '=', $activeRound->season_id)
            ->where('tr.round', '=', $activeRound->round)
            ->where('tr.id', '!=', $activeRound->id)
            // Order by squad.order ASC (e.g. 1. Hold before 2. Hold) as a priority tiebreaker
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

        // Index allocations by member_ref_id for O(1) lookup
        $allocationsByMember = [];
        foreach ($rows as $row) {
            if ($row->member_ref_id === null) {
                continue;
            }

            // Keep the first (highest-priority squad) if player appears more than once
            if (!isset($allocationsByMember[$row->member_ref_id])) {
                $allocationsByMember[$row->member_ref_id] = [
                    'teamRoundId' => (string) $row->team_round_id,
                    'teamRoundName' => $row->team_round_name ?? ('Runde ' . $row->round),
                    'squadName' => $row->squad_name,
                    'squadOrder' => $row->squad_order !== null ? (int) $row->squad_order : null,
                    'categoryName' => $row->category_name,
                ];
            }
        }

        return $allocationsByMember;
    }

    /**
     * Clear the static request cache (primarily for test teardowns).
     */
    public static function clearCache(): void
    {
        self::$roundAllocationsCache = [];
        self::$categoryToRoundCache = [];
    }
}
