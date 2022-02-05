<?php

declare(strict_types=1);

namespace Tests\Integration;

use FlyCompany\BadmintonPlayerAPI\BadmintonPlayerAPI;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\TestCase;
use Tests\CreatesApplication;

class BadmintonPlayerApiTest extends TestCase
{

    use CreatesApplication;


    /**
     * @var BadmintonPlayerAPI
     */
    private BadmintonPlayerAPI $client;

    public function getClient(): BadmintonPlayerAPI
    {
        return $this->client ?? ($this->client = BadmintonPlayerAPI::make(env('BADMINTONPLAYER_API_EMAIL'), env('BADMINTONPLAYER_API_PASSWORD')));
    }

    /**
     * @test
     * @return void
     */
    public function returnAllLeagueMatchesForCurrentSeason(): void
    {

        $body = file_get_contents(__DIR__ . '/leagueMatchesForSeason.json');
        $mock = new MockHandler([
            new Response(200, [], $body),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);
        $badmintonPlayerApi = new BadmintonPlayerAPI($client);

        $matches = $badmintonPlayerApi->getCurrentLeagueMatches();
        $firstMatch = array_shift($matches);
        self::assertIsInt($firstMatch->leagueMatchId);
    }

    /**
     * @test
     */
    public function allPlayedMatches14DaysBack(): void
    {
        $body = file_get_contents(__DIR__ . '/playedMatchesLinups.json');
        $mock = new MockHandler([
            new Response(200, [], $body),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);
        $badmintonPlayerApi = new BadmintonPlayerAPI($client);

        $playedMatches = $badmintonPlayerApi->getPlayedLeagueMatches();
        $firstPlayedMatch = $playedMatches[0];
        self::assertIsInt($firstPlayedMatch->combinedTeamMatches[0]->teamPlayers[0]->disciplineRanking);
    }
}
