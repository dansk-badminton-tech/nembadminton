<?php

declare(strict_types=1);

namespace App\Exceptions;

use GraphQL\Error\ClientAware;
use RuntimeException;

/**
 * Only a Squad with a Team can be a Paper Squad. The message is shown to the coach.
 */
class PaperSquadWithoutTeamException extends RuntimeException implements ClientAware
{
    public function __construct()
    {
        parent::__construct('Kun en holdopstilling med et hold kan være papirhold.');
    }

    public function isClientSafe(): bool
    {
        return true;
    }
}
