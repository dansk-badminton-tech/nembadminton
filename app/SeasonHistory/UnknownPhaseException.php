<?php

declare(strict_types=1);

namespace App\SeasonHistory;

use DomainException;

/**
 * A lookup needs the phase of a TeamRound that has no round number. The team manager fills in the round to fix it.
 */
class UnknownPhaseException extends DomainException
{
    public function __construct(
        public readonly string $teamRoundId,
        public readonly string $teamRoundName,
    ) {
        parent::__construct(sprintf('Holdrunden "%s" mangler rundenummer, så fasen kan ikke afgøres.', $teamRoundName));
    }
}
