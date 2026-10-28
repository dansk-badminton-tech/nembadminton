<?php

declare(strict_types=1);

namespace App\SeasonHistory;

/**
 * One player's result of the Playoff Participation Requirement (§32 stk. 1 a–c).
 */
final readonly class PlayoffParticipation
{
    /**
     * @param  string  $status  SATISFIED, REQUIREMENT_NOT_MET or CANNOT_VERIFY.
     * @param  list<string>  $incompleteReasons  Why the result is CANNOT_VERIFY; empty for the other statuses.
     * @param  int  $matchCount  Earlier team matches in the DH Tournament this season.
     * @param  bool|null  $playedByDeadline  Whether the 20 October deadline is met; null when it can't be told.
     * @param  list<RoundLineup>  $uncountedRounds  Earlier TeamRounds whose unknown tier or missing date keeps a CANNOT_VERIFY result open, in round order.
     */
    public function __construct(
        public string $status,
        public array $incompleteReasons,
        public int $matchCount,
        public ?bool $playedByDeadline,
        public array $uncountedRounds,
    ) {}
}
