<?php

declare(strict_types=1);

namespace App\SeasonHistory;

use DomainException;

/**
 * A phase-filtered lookup cannot complete until the coach supplies the missing round or Match Kind.
 */
class UnknownPhaseException extends DomainException
{
    public function __construct(
        public readonly string $teamRoundId,
        public readonly string $teamRoundName,
        public readonly ?int $squadId = null,
        public readonly ?string $squadName = null,
    ) {
        parent::__construct($squadId === null
            ? sprintf('Holdrunden "%s" mangler rundenummer, så fasen kan ikke afgøres.', $teamRoundName)
            : sprintf('Holdopstillingen "%s" (nr. %d) i holdrunden "%s" mangler kampart, så sæsonfasen kan ikke afgøres.', $squadName, $squadId, $teamRoundName));
    }
}
