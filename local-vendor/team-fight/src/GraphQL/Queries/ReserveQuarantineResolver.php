<?php

declare(strict_types=1);

namespace FlyCompany\TeamFight\GraphQL\Queries;

use App\Models\Squad;
use App\Models\SquadMember;
use App\Models\SquadReserve;
use App\Models\TeamRound;
use App\Models\TeamRoundScenario;
use App\Models\User;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

/**
 * SquadMember.reserveQuarantine (§44): where the placed Member was a Reserve Player in the Official Lineup of a TeamRound
 * of the same Clubhouse and Season one round number earlier, or null. Soft only; it never blocks anything.
 *
 * The lookup runs once per TeamRound per request, on (clubhouse, season, round - 1), like ParallelAllocationResolver.
 */
class ReserveQuarantineResolver
{
    /**
     * teamRoundId -> [member refId => quarantine]
     *
     * @var array<string, array<string, array<string, mixed>>>
     */
    private static array $cache = [];

    private static ?Request $cachedRequest = null;

    /**
     * @param  array<string, mixed>  $args
     * @return array<string, mixed>|null
     */
    public function __invoke(SquadMember $root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): ?array
    {
        if (self::$cachedRequest !== request()) {
            self::$cache = [];
            self::$cachedRequest = request();
        }

        $teamRoundId = SquadMemberTeamRound::of($root);
        $user = $context->user();
        $clubhouseId = $user instanceof User ? $user->clubhouse_id : null;
        if ($teamRoundId === '' || $clubhouseId === null) {
            return null;
        }

        self::$cache[$teamRoundId] ??= $this->load($teamRoundId, (int) $clubhouseId);

        return self::$cache[$teamRoundId][(string) $root->member_ref_id] ?? null;
    }

    /**
     * Quarantines for the TeamRound, keyed by the reserve's refId. Empty for a TeamRound of another Clubhouse, or
     * without a Season or round number.
     *
     * @return array<string, array<string, mixed>>
     */
    private function load(string $teamRoundId, int $clubhouseId): array
    {
        /** @var TeamRound|null $teamRound */
        $teamRound = TeamRound::query()->whereKey($teamRoundId)->where('clubhouse_id', $clubhouseId)->first();
        if ($teamRound === null || $teamRound->round === null || $teamRound->season_id === null) {
            return [];
        }

        $previousRounds = TeamRound::query()
            ->where('clubhouse_id', $clubhouseId)
            ->where('season_id', $teamRound->season_id)
            ->where('round', (int) $teamRound->round - 1)
            ->with(['officialScenario', 'squads'])
            ->orderBy('game_date')
            ->orderBy('id')
            ->get();
        if ($previousRounds->isEmpty()) {
            return [];
        }

        // Only the Official Lineup counts: the official Scenario's records, or for a TeamRound without one, the records
        // outside any Scenario. Same rule as Season History.
        $reserves = SquadReserve::query()
            ->where(function (Builder $query) use ($previousRounds) {
                foreach ($previousRounds as $previousRound) {
                    /** @var TeamRoundScenario|null $officialScenario */
                    $officialScenario = $previousRound->officialScenario;
                    $officialScenarioId = $officialScenario?->id;
                    $query->orWhere(fn (Builder $scope) => $scope
                        ->whereIn('squad_id', $previousRound->squads->pluck('id'))
                        ->when(
                            $officialScenarioId === null,
                            fn (Builder $q) => $q->whereNull('team_round_scenario_id'),
                            fn (Builder $q) => $q->where('team_round_scenario_id', $officialScenarioId),
                        ));
                }
            })
            ->orderBy('id')
            ->get();

        $squads = [];
        foreach ($previousRounds as $previousRound) {
            foreach ($previousRound->squads->values() as $index => $squad) {
                /** @var Squad $squad */
                $squads[$squad->id] = ['teamRound' => $previousRound, 'position' => $index + 1];
            }
        }

        $quarantines = [];
        foreach ($reserves as $reserve) {
            ['teamRound' => $previousRound, 'position' => $position] = $squads[$reserve->squad_id];
            $quarantines[$reserve->reserve_ref_id] ??= [
                'teamRoundId' => $previousRound->id,
                'teamRoundName' => $previousRound->resolveName(),
                'round' => (int) $previousRound->round,
                'squadPosition' => $position,
                'replacedName' => $reserve->replaced_name,
            ];
        }

        return $quarantines;
    }

    /**
     * Clear the static request cache (primarily for test teardowns).
     */
    public static function clearCache(): void
    {
        self::$cache = [];
        self::$cachedRequest = null;
        SquadMemberTeamRound::clearCache();
    }
}
