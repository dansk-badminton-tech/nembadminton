<?php

namespace FlyCompany\BadmintonPlayerAPI\Commands;

use FlyCompany\BadmintonPlayerAPI\BadmintonPlayerAPI;
use Illuminate\Console\Command;

class AuthenticationCommand extends Command
{
    protected $signature = 'badmintonplayer-api:authentication';

    protected $description = 'Prints the authentication token';

    public function handle(BadmintonPlayerAPI $badmintonPlayerAPI): void
    {
        $this->info('Bearer '.$badmintonPlayerAPI->getAccessToken());
    }
}
