<?php

declare(strict_types=1);

namespace Tests\Integration;

use FlyCompany\BadmintonPlayerAPI\BadmintonPlayerAPI;
use GuzzleHttp\Client;
use Illuminate\Foundation\Testing\TestCase;
use Tests\CreatesApplication;

class BadmintonPlayerApiTest extends TestCase
{

    use CreatesApplication;

    private static BadmintonPlayerAPI $client;

    public static function setUpBeforeClass(): void
    {
        self::$client = BadmintonPlayerAPI::make(env('BADMINTONPLAYER_API_EMAIL'), env('BADMINTONPLAYER_API_PASSWORD'));
    }

    /**
     * @test
     * @return void
     */
    public function returnAllLeagueMatchesForCurrentSeason(): void
    {
        self::$client->getCurrentLeagueMatches();
    }
}
