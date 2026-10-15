<?php

declare(strict_types=1);

namespace FlyCompany\TeamFight;

use GraphQL\Error\ClientAware;
use RuntimeException;

/**
 * A reserve record the server won't save. The message is shown to the coach.
 */
class ReserveRejectedException extends RuntimeException implements ClientAware
{
    public function isClientSafe(): bool
    {
        return true;
    }
}
