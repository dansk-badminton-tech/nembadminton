<?php


namespace FlyCompany\Scraper\Exception;

use GraphQL\Error\ClientAware;

class NoPlayerPointsFound extends \RuntimeException implements ClientAware
{

    public function isClientSafe() : bool
    {
        return true;
    }
}
