<?php

declare(strict_types=1);

namespace FlyCompany\BadmintonPlayerAPI;

use FlyCompany\BadmintonPlayerAPI\Models\TeamMatch;
use FlyCompany\BadmintonPlayerAPI\Models\TeamMatchLineup;
use FlyCompany\TeamFight\Models\SerializerHelper;
use GuzzleHttp\Client;
use Illuminate\Support\Arr;

/**
 * api: https://badmintonplayer.dk/publicapi/swagger/index.html
 *
 * Class BadmintonPlayerAPI
 * @package FlyCompany\BadmintonPlayerAPI
 */
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
     * @param string $email
     * @param string $password
     * @return BadmintonPlayerAPI
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
     * @param Client $client
     * @param string $email
     * @param string $password
     * @return Client
     * @throws \JsonException
     */
    private static function auth(Client $client, string $email, string $password): Client
    {
        $response = $client->post('Authenticate', ['json' => ['email' => $email, 'password' => $password]]);
        $response = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        return new Client([
            'base_uri' => self::$base_url,
            'headers' => [
                'Authorization' => 'Bearer ' . $response['access_token']
            ]
        ]);
    }

    /**
     * Get all team matches for current season
     *
     * @return TeamMatch[]
     */
    public function getCurrentLeagueMatches(): array
    {
        $response = $this->client->get('LeagueMatch');
        $serializer = SerializerHelper::getSerializer();
        /** @var TeamMatch[] $teamMatches */
        $teamMatches = $serializer->deserialize($response->getBody()->getContents(), TeamMatch::class . '[]', 'json');
        return $teamMatches;
    }

    /**
     * Team matches information with lineups of allowed League divisions for current season. Lineup will be available when match was happen.
     *
     * @return TeamMatchLineup[]
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     * @throws \JsonException
     */
    public function getPlayedLeagueMatches(): array
    {
        $serializer = SerializerHelper::getSerializer();
        $response = $this->client->get('LeagueMatch/lineup');
        $contents = $response->getBody()->getContents();
        $data = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);

        $data = $this->fixTeamPlayersToBeArray($data);

        /** @var TeamMatchLineup[] $playedTeamMatches */
        $playedTeamMatches = $serializer->denormalize($data, TeamMatchLineup::class . '[]');
        return $playedTeamMatches;
    }

    /**
     * @param $data
     * @return array
     */
    protected function fixTeamPlayersToBeArray(&$data): array
    {
        foreach ($data as &$teamMatchLineup) {
            if (isset($teamMatchLineup['combinedTeamMatches'])) {
                foreach ($teamMatchLineup['combinedTeamMatches'] as &$combinedTeamMatch) {
                    $teamPlayers = $combinedTeamMatch['teamPlayers'] ?? false;
                    if ($teamPlayers !== false) {
                        $combinedTeamMatch['teamPlayers'] = Arr::collapse($teamPlayers);
                    }
                }
                unset($combinedTeamMatch);
            }
        }
        unset($teamMatchLineup);
        return $data;
    }

}
