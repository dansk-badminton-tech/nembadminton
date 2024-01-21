<?php
declare(strict_types = 1);


namespace FlyCompany\Scraper\Exception;

use GraphQL\Error\ClientAware;

class NoPlayersFoundInTeamMatchException extends \RuntimeException implements ClientAware
{

    public function isClientSafe() : bool
    {
        return true;
    }

    public function getCategory()
    {
        return 'backend';
    }

}
