<?php

declare(strict_types=1);

namespace App\GraphQL\Queries;

use App\Models\TeamRound;
use App\SeasonHistory\CategorySlot;
use App\SeasonHistory\RoundLineup;
use App\SeasonHistory\SeasonHistory;
use App\SeasonHistory\SeasonHistoryLoader;
use App\SeasonHistory\SlotPlayer;
use Carbon\Carbon;

/**
 * TeamRound.seasonHistory: the Category Rank ladders of the Clubhouse's earlier TeamRounds in the Season, then the
 * viewed TeamRound's own.
 */
class TeamRoundSeasonHistory
{
    public function __construct(
        private readonly SeasonHistoryLoader $loader,
    ) {}

    /**
     * @return list<array<string, mixed>>
     */
    public function __invoke(TeamRound $teamRound): array
    {
        return array_map(fn (RoundLineup $round) => [
            'id' => $round->teamRoundId,
            'name' => $round->name,
            'gameDate' => $round->gameDate,
            'round' => $round->round,
            'phase' => $round->phase()?->value,
            'isCurrent' => $round->teamRoundId === $teamRound->id,
            'categories' => array_map(fn (string $category) => [
                'category' => $category,
                'places' => array_map($this->place(...), $round->ladder($category)),
            ], SeasonHistory::CATEGORIES),
        ], $this->loader->upTo($teamRound)->rounds());
    }

    /**
     * TeamRound.seasonHistoryWithoutRound: the TeamRounds the Season History leaves out for lack of a round number.
     *
     * @return list<array<string, mixed>>
     */
    public function withoutRound(TeamRound $teamRound): array
    {
        return self::roundsWithoutNumber($this->loader->withoutRoundNumber($teamRound));
    }

    /**
     * SeasonHistoryRoundWithoutNumber values.
     *
     * @param  iterable<TeamRound>  $teamRounds
     * @return list<array<string, mixed>>
     */
    public static function roundsWithoutNumber(iterable $teamRounds): array
    {
        $rounds = [];
        foreach ($teamRounds as $teamRound) {
            $rounds[] = [
                'id' => $teamRound->id,
                'name' => $teamRound->resolveName(),
                'gameDate' => $teamRound->game_date === null ? null : Carbon::parse($teamRound->game_date)->toDateString(),
            ];
        }

        return $rounds;
    }

    /**
     * @return array<string, mixed>
     */
    private function place(CategorySlot $slot): array
    {
        return [
            'place' => $slot->rank,
            'squadPosition' => $slot->squad->position,
            'teamName' => $slot->squad->teamName,
            'tier' => $slot->squad->tier,
            'isPaperSquad' => $slot->squad->isPaperSquad,
            'label' => $slot->categoryName,
            'players' => array_map(fn (SlotPlayer $player) => [
                'refId' => $player->refId,
                'name' => $player->name,
                'gender' => $player->gender,
                'vintage' => $player->vintage,
            ], $slot->players),
        ];
    }
}
