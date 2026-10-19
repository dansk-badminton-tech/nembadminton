<?php

declare(strict_types=1);

namespace App\SeasonHistory;

use App\Enums\SeasonPhase;

/**
 * The Official Lineup of one TeamRound: its Squads in order and a ladder of CategorySlots per category.
 */
final readonly class RoundLineup
{
    /**
     * @param  list<LineupSquad>  $squads  In Squad order, highest team first.
     * @param  array<string, list<CategorySlot>>  $ladders  Keyed by category; each category is its own ladder.
     */
    public function __construct(
        public string $teamRoundId,
        public string $name,
        public ?int $round,
        public ?string $gameDate,
        public array $squads,
        public array $ladders,
    ) {}

    /**
     * The TeamRound's phase, taken from its highest Squad because the phase can depend on the tier. Null when the
     * TeamRound has no round number.
     */
    public function phase(): ?SeasonPhase
    {
        return $this->squads[0]->phase ?? SeasonPhase::fromRound($this->round, null);
    }

    /**
     * The TeamRound's Squad for the Team; null when none of its Squads has the Team linked.
     */
    public function squadFor(int $teamId): ?LineupSquad
    {
        return array_find($this->squads, fn (LineupSquad $squad) => $squad->teamId === $teamId);
    }

    /**
     * @return list<CategorySlot>
     */
    public function ladder(string $category): array
    {
        return $this->ladders[$category] ?? [];
    }
}
