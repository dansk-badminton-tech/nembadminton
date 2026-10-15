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

            /** @var SquadReserve */
            return $squad->reserves()->create(
                ['team_round_scenario_id' => $this->scope($teamRound, $scenarioId)]
                + $this->checked($squad, $scenarioId, $reserveRefId, $replacedRefId, null),
            );
        });
    }

    /**
     * Changes who plays for whom in a record, on the same Squad and in the same lineup. The record itself doesn't count
     * as an earlier one, and a rejected change leaves it as it was.
     *
     * @throws ReserveRejectedException for the same reasons as add().
     */
    public function update(SquadReserve $record, string $reserveRefId, string $replacedRefId): SquadReserve
    {
        $squad = $record->squad;
        $teamRound = $squad->teamRound;
        /** @var TeamRoundScenario|null $officialScenario */
        $officialScenario = $teamRound->officialScenario;
        // The official Scenario's records are checked against the official lineup, like add() without a Scenario.
        $scenarioId = $record->team_round_scenario_id === $officialScenario?->id ? null : $record->team_round_scenario_id;

        return DB::transaction(function () use ($record, $squad, $teamRound, $scenarioId, $reserveRefId, $replacedRefId) {
            TeamRound::query()->whereKey($teamRound->id)->lockForUpdate()->first();

            $record->update($this->checked($squad, $scenarioId, $reserveRefId, $replacedRefId, $record->id));

            return $record;
        });
    }

    /**
     * The record's player columns for "X for Y" on the Squad, once the §43 rules allow it. Call inside a locked
     * transaction.
     *
     * @param  int|null  $exceptId  A record to leave out when looking for earlier records of X or Y
     * @return array<string, string>
     *
     * @throws ReserveRejectedException
     */
    private function checked(Squad $squad, ?int $scenarioId, string $reserveRefId, string $replacedRefId, ?int $exceptId): array
    {
        $teamRound = $squad->teamRound;
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

        $existing = SquadReserve::query()
            ->whereIn('squad_id', $squads->pluck('id'))
            ->inLineup($this->scope($teamRound, $scenarioId))
            ->when($exceptId !== null, fn (Builder $query) => $query->whereKeyNot($exceptId));
        if ((clone $existing)->where('reserve_ref_id', $reserveRefId)->exists()) {
            throw new ReserveRejectedException("{$reserve->name} er allerede reserve.");
        }
        if ((clone $existing)->where('replaced_ref_id', $replacedRefId)->exists()) {
            throw new ReserveRejectedException("{$replaced->name} er allerede erstattet.");
        }

        return [
            'reserve_ref_id' => $reserveRefId,
            'reserve_name' => $reserve->name,
            'reserve_gender' => $reserve->gender,
            'replaced_ref_id' => $replacedRefId,
            'replaced_name' => $replaced->name,
            'replaced_gender' => $replaced->gender,
        ];
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
