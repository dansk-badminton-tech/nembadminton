<?php

declare(strict_types=1);

namespace FlyCompany\TeamFight;

use GraphQL\Error\ClientAware;
use RuntimeException;

class CannotDeleteOfficialScenarioException extends RuntimeException implements ClientAware
{
    public function __construct(string $message = 'Cannot delete the official lineup.')
    {
        parent::__construct($message);
    }

    public function isClientSafe(): bool
    {
        return true;
    }
}
