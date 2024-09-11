<?php
declare(strict_types = 1);


namespace FlyCompany\TeamFight;

use GraphQL\Error\ClientAware;

class PointNotFoundInCategoryException extends \RuntimeException implements ClientAware
{

    public function isClientSafe() : bool
    {
        return true;
    }
}
