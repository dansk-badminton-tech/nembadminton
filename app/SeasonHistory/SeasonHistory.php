<?php

declare(strict_types=1);

namespace App\SeasonHistory;

use App\Enums\SeasonPhase;

/**
 * Season History of a Clubhouse: the Official Lineups of its TeamRounds in one Season.
 */
final readonly class SeasonHistory
{
    /**
     * @param  list<RoundLineup>  $rounds  Ordered by game date, then round number.
     */
    public function __construct(
        private array $rounds,
    ) {}

    /**
     * @return list<RoundLineup>
     */
    public function rounds(): array
    {
        return $this->rounds;
    }

    /**
     * The TeamRounds without a round number, whose phase is unknown, so the team manager can fill in the round.
     *
     * @return list<RoundLineup>
     */
    public function roundsWithUnknownPhase(): array
    {
        return array_values(array_filter($this->rounds, fn (RoundLineup $round) => $round->round === null));
    }

    /**
     * The Member's Placements, one per TeamRound and Squad they stand on, in round order.
     *
     * @return list<Placement>
     */
    public function placementsFor(string $refId): array
    {
        $placements = [];
        foreach ($this->rounds as $round) {
            /** @var array<int, list<CategoryPlacement>> $bySquad */
            $bySquad = [];
            foreach ($round->ladders as $ladder) {
                foreach ($ladder as $slot) {
                    $categoryPlacement = $this->categoryPlacement($slot, $refId);
                    if ($categoryPlacement !== null) {
                        $bySquad[$slot->squad->squadId][] = $categoryPlacement;
                    }
                }
            }
            foreach ($round->squads as $squad) {
                if (isset($bySquad[$squad->squadId])) {
                    $placements[] = new Placement($round, $squad, $bySquad[$squad->squadId]);
                }
            }
        }

        return $placements;
    }

    /**
     * How many team matches the Member stands in on the Team.
     *
     * @throws UnknownPhaseException When filtering by phase and one of the Member's TeamRounds has no round number.
     */
    public function teamMatchCount(string $refId, int $teamId, ?SeasonPhase $phase = null): int
    {
        return count(array_filter(
            $this->placementsIn($refId, $phase),
            fn (Placement $placement) => $placement->squad->teamId === $teamId,
        ));
    }

    /**
     * How many team matches the Member stands in on the Team or a lower one. Lower means a later position in that
     * TeamRound's Squad order. A TeamRound without a Squad for the Team can't be compared, so it doesn't count.
     *
     * @throws UnknownPhaseException When filtering by phase and one of the Member's TeamRounds has no round number.
     */
    public function matchCountOnSameOrLowerTeam(string $refId, int $teamId, ?SeasonPhase $phase = null): int
    {
        return count(array_filter(
            $this->placementsIn($refId, $phase),
            function (Placement $placement) use ($teamId) {
                $teamSquad = array_find($placement->round->squads, fn (LineupSquad $squad) => $squad->teamId === $teamId);

                return $teamSquad !== null && $placement->squad->position >= $teamSquad->position;
            },
        ));
    }

    /**
     * The TeamRounds the Member stands in, in round order, each once.
     *
     * @return list<RoundLineup>
     *
     * @throws UnknownPhaseException When filtering by phase and one of the Member's TeamRounds has no round number.
     */
    public function playedRounds(string $refId, ?SeasonPhase $phase = null): array
    {
        $rounds = [];
        foreach ($this->placementsIn($refId, $phase) as $placement) {
            $rounds[$placement->round->teamRoundId] = $placement->round;
        }

        return array_values($rounds);
    }

    /**
     * The categories the Member stands in, in the order HS, DS, HD, DD, MD.
     *
     * @return list<string>
     *
     * @throws UnknownPhaseException When filtering by phase and one of the Member's TeamRounds has no round number.
     */
    public function playedCategories(string $refId, ?SeasonPhase $phase = null): array
    {
        $played = [];
        foreach ($this->placementsIn($refId, $phase) as $placement) {
            foreach ($placement->categories as $category) {
                $played[$category->slot->category] = true;
            }
        }

        return array_values(array_filter(SeasonHistoryLoader::CATEGORIES, fn (string $category) => isset($played[$category])));
    }

    /**
     * @return list<Placement>
     */
    private function placementsIn(string $refId, ?SeasonPhase $phase): array
    {
        $placements = $this->placementsFor($refId);
        if ($phase === null) {
            return $placements;
        }
        foreach ($placements as $placement) {
            if ($placement->phase() === null) {
                throw new UnknownPhaseException($placement->round->teamRoundId, $placement->round->name);
            }
        }

        return array_values(array_filter($placements, fn (Placement $placement) => $placement->phase() === $phase));
    }

    private function categoryPlacement(CategorySlot $slot, string $refId): ?CategoryPlacement
    {
        $isOnSlot = false;
        $partner = null;
        foreach ($slot->players as $player) {
            if ($player->refId === $refId) {
                $isOnSlot = true;
            } else {
                $partner = $player;
            }
        }

        return $isOnSlot ? new CategoryPlacement($slot, $partner) : null;
    }
}
