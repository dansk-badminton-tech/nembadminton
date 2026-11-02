<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Enums\CheckNotApplicableReason;
use App\Enums\SquadCoverage;
use App\Models\Squad;
use App\Models\SquadMember;
use App\Models\TeamRound;
use App\SeasonHistory\RoundLineup;
use App\SeasonHistory\SeasonHistory;
use App\SeasonHistory\SeasonHistoryLoader;
use Closure;

/**
 * What the history Checks (§38 stk. 8, §27 stk. 5, §32 stk. 1) share: a saved Scenario's placements, or the Official
 * Lineup's, checked player by player against earlier Official Lineups, and the Check's notApplicableReason from each
 * Squad's coverage. A Check says which Squads it covers (squadCheck) and how a player of a covered Squad does
 * (playerResult).
 *
 * @phpstan-type SquadCheck array{status: ?string, incompleteReasons: list<string>, coverage: ?SquadCoverage, ...<string, mixed>}
 */
abstract class ValidateHistoryCheck
{
    public function __construct(private readonly SeasonHistoryLoader $loader, private readonly TeamRoundLineup $lineup) {}

    /**
     * @param  array{teamRoundId: string, scenarioId?: string|null}  $args
     * @return array{notApplicableReason: ?string, players: list<array<string, mixed>>}
     */
    public function __invoke(mixed $root, array $args): array
    {
        $round = $this->lineup->teamRound($args['teamRoundId']);
        $history = null;
        $earlier = function () use (&$history, $round): SeasonHistory {
            return $history ??= $this->loader->before($round);
        };
        $results = [];
        $coverage = [];
        foreach ($this->lineup->squads($round, $args['scenarioId'] ?? null) as ['index' => $index, 'squad' => $squad, 'players' => $players]) {
            $squadCheck = $this->squadCheck($squad, $round);
            $coverage[] = $squadCheck['coverage'];
            foreach ($players as $player) {
                $results[] = [
                    'squadIndex' => $index,
                    'refId' => $player->member_ref_id,
                    'name' => $player->name,
                    ...$this->playerResult($player, $squad, $round, $squadCheck, $earlier),
                ];
            }
        }

        return ['notApplicableReason' => CheckNotApplicableReason::fromSquads($coverage)?->value, 'players' => $results];
    }

    /**
     * How the Check covers the Squad. A null status means the requirement applies and history must be checked; else
     * every player of the Squad takes it (NOT_APPLICABLE with a coverage, or CANNOT_VERIFY with incompleteReasons).
     *
     * @return SquadCheck
     */
    abstract protected function squadCheck(Squad $squad, TeamRound $round): array;

    /**
     * The player's outcome: status, incompleteReasons, uncountedRounds and any fields of the Check's own.
     *
     * @param  SquadCheck  $squadCheck
     * @param  Closure(): SeasonHistory  $earlier  the TeamRound's earlier Official Lineups, loaded once when first asked for
     * @return array<string, mixed>
     */
    abstract protected function playerResult(SquadMember $player, Squad $squad, TeamRound $round, array $squadCheck, Closure $earlier): array;

    /**
     * A Squad the Check leaves out: its players are NOT_APPLICABLE.
     *
     * @param  array<string, mixed>  $extra
     * @return SquadCheck
     */
    protected static function notCovered(SquadCoverage $coverage, array $extra = []): array
    {
        return ['status' => 'NOT_APPLICABLE', 'incompleteReasons' => [], ...$extra, 'coverage' => $coverage];
    }

    /**
     * A covered Squad: its players are checked, or CANNOT_VERIFY while a fact is missing.
     *
     * @param  array<string, bool>  $missing  each *IncompleteReason, true when it holds
     * @param  array<string, mixed>  $extra
     * @return SquadCheck
     */
    protected static function covered(array $missing, array $extra = []): array
    {
        $incompleteReasons = array_keys(array_filter($missing));

        return ['status' => $incompleteReasons === [] ? null : 'CANNOT_VERIFY', 'incompleteReasons' => $incompleteReasons, ...$extra, 'coverage' => null];
    }

    /**
     * Earlier TeamRounds the player played in without the Team linked, so they can't be counted.
     *
     * @param  list<RoundLineup>  $rounds
     * @return list<array{teamRoundId: string, name: string, round: ?int}>
     */
    protected static function uncountedRounds(array $rounds): array
    {
        return array_map(fn (RoundLineup $round) => [
            'teamRoundId' => $round->teamRoundId,
            'name' => $round->name,
            'round' => $round->round,
        ], $rounds);
    }
}
