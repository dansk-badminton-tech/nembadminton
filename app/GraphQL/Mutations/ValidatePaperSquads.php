<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Models\Squad;
use App\Models\TeamRound;
use App\SeasonHistory\PaperSquadRule;
use App\SeasonHistory\SeasonHistoryLoader;

/**
 * Mutation.validatePaperSquads: the players on the lineup's Paper Squads who break the paper-team rule (§38 stk. 7).
 *
 * Which Squads are Paper Squads, and their Team and tier, is read from the TeamRound, not the lineup. The lineup is
 * matched to them by Squad id, so a Squad id from another TeamRound is never checked.
 */
class ValidatePaperSquads
{
    public function __construct(
        private readonly SeasonHistoryLoader $loader,
    ) {}

    /**
     * @param  array{teamRoundId: string, input: list<array{name: string, squad?: array<string, mixed>|null}>}  $args
     * @return array{missingRound: bool, players: list<array<string, mixed>>}
     */
    public function __invoke(mixed $root, array $args): array
    {
        $teamRound = TeamRound::query()->with('squads.team.tier')->findOrFail($args['teamRoundId']);
        $paperSquads = $teamRound->squads
            ->filter(fn (Squad $squad) => $squad->is_paper_squad && $squad->team_id !== null)
            ->keyBy('id');

        /** @var array<int, array{squad: Squad, lineup: array<string, mixed>}> $lineup Paper Squads in the lineup, by lineup index. */
        $lineup = [];
        foreach ($args['input'] as $index => $team) {
            $squad = $paperSquads->get($team['squad']['id'] ?? null);
            if ($squad !== null) {
                $lineup[$index] = ['squad' => $squad, 'lineup' => $team['squad']];
            }
        }

        if ($lineup === []) {
            return ['missingRound' => false, 'players' => []];
        }
        if ($teamRound->round === null) {
            return ['missingRound' => true, 'players' => []];
        }

        $earlier = $this->loader->before($teamRound);
        $players = [];
        foreach ($lineup as $index => ['squad' => $squad, 'lineup' => $input]) {
            $rule = new PaperSquadRule($earlier, (int) $teamRound->round, (int) $squad->team_id, $squad->effectiveTier());
            foreach ($this->playersOf($input) as $refId => $player) {
                // A numeric refId becomes an int array key.
                $refId = (string) $refId;
                $breach = $rule->check($refId, $player['categories']);
                if ($breach !== null) {
                    $players[] = [
                        'squadIndex' => $index,
                        'refId' => $refId,
                        'name' => $player['name'],
                        'unplayedCategories' => $breach->unplayedCategories,
                        'matchCount' => $breach->matchCount,
                        'requiredMatchCount' => $breach->requiredMatchCount,
                    ];
                }
            }
        }

        return ['missingRound' => false, 'players' => $players];
    }

    /**
     * The players on a lineup Squad with the categories they stand in, in lineup order.
     *
     * @param  array<string, mixed>  $squad
     * @return array<string, array{name: string, categories: list<string>}>
     */
    private function playersOf(array $squad): array
    {
        $players = [];
        foreach ($squad['categories'] ?? [] as $category) {
            foreach ($category['players'] ?? [] as $player) {
                $players[$player['refId']]['name'] = $player['name'];
                $players[$player['refId']]['categories'][] = $category['category'];
            }
        }

        return $players;
    }
}
