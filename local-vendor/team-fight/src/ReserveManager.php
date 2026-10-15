<?php

declare(strict_types=1);

namespace FlyCompany\TeamFight;

use App\Models\Squad;
use App\Models\SquadMember;
use App\Models\SquadReserve;
use App\Models\TeamRound;
use App\Models\TeamRoundScenario;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Reserve records (§43): on a Squad, X from a lower Squad of the same lineup plays for Y on this Squad. Records are
 * scoped per Scenario like category slots.
 */
class ReserveManager
{
    public function __construct(
        private readonly ScenarioManager $scenarioManager,
    ) {}

    /**
     * The Scenario that holds a lineup's records: the given Scenario, or else the official one. Null for a TeamRound
     * without an official Scenario, whose lineup isn't in any Scenario.
     */
    public function scope(TeamRound $teamRound, ?int $scenarioId): ?int
    {
        /** @var TeamRoundScenario|null $officialScenario */
        $officialScenario = $teamRound->officialScenario;

        return $scenarioId ?? $officialScenario?->id;
    }

    /**
     * @return Collection<int, SquadReserve>
     */
    public function reservesOf(Squad $squad, ?int $scenarioId): Collection
    {
        return $squad->reserves()->inLineup($this->scope($squad->teamRound, $scenarioId))->orderBy('id')->get();
    }

    /**
     * Records that X plays for Y on the Squad, in the given Scenario or the official lineup.
     *
     * @throws ReserveRejectedException when X isn't on a lower Squad of the lineup, Y isn't on this Squad, X is already a
     *                                  reserve or Y has already been replaced.
     */
    public function add(Squad $squad, ?int $scenarioId, string $reserveRefId, string $replacedRefId): SquadReserve
    {
        $teamRound = $squad->teamRound;
        if ($scenarioId !== null && ! $teamRound->scenarios()->whereKey($scenarioId)->exists()) {
            throw new ReserveRejectedException('Scenariet hører ikke til holdrunden.');
        }

        return DB::transaction(function () use ($squad, $teamRound, $scenarioId, $reserveRefId, $replacedRefId) {
            TeamRound::query()->whereKey($teamRound->id)->lockForUpdate()->first();

            /** @var Collection<int, Squad> $squads */
            $squads = $teamRound->squads()->get()->values();
            $squadIndex = $squads->search(fn (Squad $candidate) => $candidate->id === $squad->id);
            $placements = $squads->map(fn (Squad $candidate) => $this->placedMembers($candidate, $scenarioId));

            $replaced = $placements[$squadIndex][$replacedRefId] ?? null;
            if ($replaced === null) {
                throw new ReserveRejectedException('Spilleren, der erstattes, står ikke på holdet.');
            }

            $reserve = null;
            foreach ($placements as $index => $members) {
                if (isset($members[$reserveRefId])) {
                    if ($index <= $squadIndex) {
                        throw new ReserveRejectedException('Reserven skal komme fra et lavere hold.');
                    }
                    $reserve ??= $members[$reserveRefId];
                }
            }
            if ($reserve === null) {
                throw new ReserveRejectedException('Reserven står ikke på et lavere hold.');
            }

            $scope = $this->scope($teamRound, $scenarioId);
            $existing = SquadReserve::query()->whereIn('squad_id', $squads->pluck('id'))->inLineup($scope);
            if ((clone $existing)->where('reserve_ref_id', $reserveRefId)->exists()) {
                throw new ReserveRejectedException("{$reserve->name} er allerede reserve.");
            }
            if ((clone $existing)->where('replaced_ref_id', $replacedRefId)->exists()) {
                throw new ReserveRejectedException("{$replaced->name} er allerede erstattet.");
            }

            /** @var SquadReserve */
            return $squad->reserves()->create([
                'team_round_scenario_id' => $scope,
                'reserve_ref_id' => $reserveRefId,
                'reserve_name' => $reserve->name,
                'reserve_gender' => $reserve->gender,
                'replaced_ref_id' => $replacedRefId,
                'replaced_name' => $replaced->name,
                'replaced_gender' => $replaced->gender,
            ]);
        });
    }

    /**
     * The Members standing in the Squad's category slots of the lineup the editor shows for the Scenario.
     *
     * @return array<string, SquadMember> Keyed by refId
     */
    private function placedMembers(Squad $squad, ?int $scenarioId): array
    {
        $members = [];
        foreach ($this->scenarioManager->getCategories($squad, $scenarioId) as $category) {
            foreach ($category->players as $player) {
                $members[(string) $player->member_ref_id] ??= $player;
            }
        }

        return $members;
    }
}
