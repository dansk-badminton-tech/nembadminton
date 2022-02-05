<?php

declare(strict_types=1);

namespace FlyCompany\BadmintonPlayerAPI;

use FlyCompany\TeamFight\Models\SerializerHelper;
use GuzzleHttp\Client;

class BadmintonPlayerAPI
{
    private static $base_url = 'https://badmintonplayer.dk/publicapi/v1/';

    private Client $client;

    /**
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @throws \JsonException
     */
    public static function make(string $email, string $password): BadmintonPlayerAPI
    {
        $client = new Client([
            'base_uri' => self::$base_url,
        ]);

        $client = self::auth($client, $email, $password);

        return new self($client);
    }

    /**
     * @throws \JsonException
     */
    private static function auth(Client $client, string $email, string $password): Client
    {
        $response = $client->post('Authenticate', ['json' => ['email' => $email, 'password' => $password]]);
        $response = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        return new Client([
            'base_uri' => self::$base_url,
            'headers' => [
                'Authorization' => 'Bearer '.$response['access_token']
            ]
        ]);
    }

    /**
     * Get all team matches for current season (only 14 days back)
     * @return void
     */
    public function getCurrentLeagueMatches(){
        $response = $this->client->get('LeagueMatch');
        $serializer = SerializerHelper::getSerializer();
        $matches = $serializer->deserialize($response->getBody()->getContents(), );
    }


}
