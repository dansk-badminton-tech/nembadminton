<?php

declare(strict_types=1);

namespace FlyCompany\TeamFight;

use App\Models\Member;
use App\Models\Squad;
use App\Models\SquadMember;
use App\Models\SquadReserve;
use App\Models\TeamRound;
use App\Models\TeamRoundScenario;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Reserve records (§43): on a Squad, X plays for Y on this Squad. X is an Available Member of the Clubhouse or comes
 * from a lower Squad of the same lineup, where X keeps their slot. The Squad's slots hold the lineup as played: adding a
 * record puts X in Y's slots, and removing it puts Y back. Records are scoped per Scenario like category slots.
 */
class ReserveManager
{
    public function __construct(
        private readonly ScenarioManager $scenarioManager,
        private readonly SquadManager $squadManager,
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
     * Records that X plays for Y on the Squad, in the given Scenario or the official lineup, and puts X in Y's slots on
     * the Squad. The coach then reorders the Squad if X's points call for it.
     *
     * @throws ReserveRejectedException when X stands on this or a higher Squad, an X who isn't placed isn't a Member of
     *                                  the Clubhouse, Y isn't on this Squad, X is already a reserve or Y has already
     *                                  been replaced.
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

            // An earlier record has already moved its players, so check the records first.
            $scope = $this->scope($teamRound, $scenarioId);
            $existing = SquadReserve::query()->whereIn('squad_id', $squads->pluck('id'))->inLineup($scope);
            $record = (clone $existing)->where('reserve_ref_id', $reserveRefId)->first();
            if ($record !== null) {
                throw new ReserveRejectedException("{$record->reserve_name} er allerede reserve.");
            }
            $record = (clone $existing)->where('replaced_ref_id', $replacedRefId)->first();
            if ($record !== null) {
                throw new ReserveRejectedException("{$record->replaced_name} er allerede erstattet.");
            }

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
            // Not placed in the lineup: an Available Member of the Clubhouse.
            $reserve ??= Member::query()->clubhouse((int) $teamRound->clubhouse_id)->where('refId', $reserveRefId)->first()
                ?? throw new ReserveRejectedException('Reserven er ikke medlem af klubben.');

            $this->swap($squad, $scenarioId, $replacedRefId, $reserveRefId);

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
     * Removes the record and puts Y back in X's slots on the Squad.
     */
    public function remove(SquadReserve $record): SquadReserve
    {
        return DB::transaction(function () use ($record) {
            $this->swap($record->squad, $record->team_round_scenario_id, $record->reserve_ref_id, $record->replaced_ref_id);
            $record->delete();

            return $record;
        });
    }

    /**
     * Replaces $fromRefId with $toRefId in the Squad's category slots, with $toRefId's points at the Squad's ranking
     * version. A slot that already holds $toRefId only loses $fromRefId.
     */
    private function swap(Squad $squad, ?int $scenarioId, string $fromRefId, string $toRefId): void
    {
        $version = Carbon::parse($squad->version ?? $squad->teamRound->version)->toDateString();
        foreach ($this->scenarioManager->getCategories($squad, $scenarioId) as $category) {
            $players = collect($category->players);
            $from = $players->first(fn (SquadMember $player) => (string) $player->member_ref_id === $fromRefId);
            if ($from === null) {
                continue;
            }
            $from->delete();
            if (! $players->contains(fn (SquadMember $player) => (string) $player->member_ref_id === $toRefId)) {
                $this->squadManager->addPlayerToSquadByRefId($toRefId, $category->id, $version);
            }
        }
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
