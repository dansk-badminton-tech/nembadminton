<?php

declare(strict_types=1);

namespace FlyCompany\TeamFight\GraphQL\Queries;

use App\Models\Cancellation;
use App\Models\Member;
use App\Models\SquadMember;
use App\Models\TeamRound;

/**
 * Resolves whether a placed player (SquadMember) has an afbud that applies to their team round.
 *
 * Uses the same rules as the player search: a manual afbud for the team round, an afbudslink
 * afbud from the team round's clubhouse on its game date, or a permanent afbud (playable = false).
 */
class PlacedPlayerCancellationResolver
{
    /**
     * In-memory cache mapping teamRoundId -> [memberRefId => cancellationData]
     * Keeps track of cancellations per round so we only run the database queries once per GraphQL request.
     *
     * @var array<string, array<string, array{permanent: bool, viaCancellationLink: bool}>>
     */
    private static array $roundCancellationsCache = [];

    /**
     * @return array{permanent: bool, viaCancellationLink: bool}|null
     */
    public function __invoke(SquadMember $root): ?array
    {
        $teamRoundId = SquadMemberTeamRound::of($root);
        if ($teamRoundId === '' || $root->member_ref_id === null) {
            return null;
        }

        if (!isset(self::$roundCancellationsCache[$teamRoundId])) {
            self::$roundCancellationsCache[$teamRoundId] = $this->loadCancellationsForTeamRound($teamRoundId);
        }

        return self::$roundCancellationsCache[$teamRoundId][$root->member_ref_id] ?? null;
    }

    /**
     * @return array<string, array{permanent: bool, viaCancellationLink: bool}> Keyed by member refId
     */
    private function loadCancellationsForTeamRound(string $teamRoundId): array
    {
        /** @var TeamRound|null $teamRound */
        $teamRound = TeamRound::query()->find($teamRoundId);
        if ($teamRound === null) {
            return [];
        }

        $cancellations = [];

        $manualRefIds = Cancellation::query()
            ->where('team_round_id', $teamRound->id)
            ->pluck('refId');
        foreach ($manualRefIds as $refId) {
            $cancellations[$refId] = ['permanent' => false, 'viaCancellationLink' => false];
        }

        if ($teamRound->game_date !== null && $teamRound->clubhouse_id !== null) {
            $linkRefIds = Cancellation::query()
                ->whereHas('cancellationCollector', fn ($query) => $query->where('clubhouse_id', $teamRound->clubhouse_id))
                ->whereHas('dates', fn ($query) => $query->whereDate('date', $teamRound->game_date))
                ->pluck('refId');
            foreach ($linkRefIds as $refId) {
                $cancellations[$refId] = ['permanent' => false, 'viaCancellationLink' => true];
            }
        }

        // A permanent afbud outweighs any dated afbud
        $permanentRefIds = Member::query()
            ->where('playable', false)
            ->whereIn('refId', SquadMember::query()
                ->join('squad_categories as sc', 'squad_members.squad_category_id', '=', 'sc.id')
                ->join('squads as s', 'sc.squad_id', '=', 's.id')
                ->where('s.team_round_id', $teamRound->id)
                ->select('squad_members.member_ref_id'))
            ->pluck('refId');
        foreach ($permanentRefIds as $refId) {
            $cancellations[$refId] = ['permanent' => true, 'viaCancellationLink' => false];
        }

        return $cancellations;
    }

    /**
     * Clear the static request cache (primarily for test teardowns).
     */
    public static function clearCache(): void
    {
        self::$roundCancellationsCache = [];
        SquadMemberTeamRound::clearCache();
    }
}
