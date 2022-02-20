<?php

declare(strict_types=1);

namespace FlyCompany\BadmintonPlayerAPI;

use FlyCompany\BadmintonPlayerAPI\Collections\TeamMatchLineupCollection;
use FlyCompany\BadmintonPlayerAPI\Models\PlayersRanking;
use FlyCompany\BadmintonPlayerAPI\Models\RankingPair;
use FlyCompany\BadmintonPlayerAPI\Models\TeamMatch;
use FlyCompany\BadmintonPlayerAPI\Models\TeamMatchLineup;
use FlyCompany\TeamFight\Models\SerializerHelper;
use GuzzleHttp\Client;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Arr;
use JsonException;
use Psr\SimpleCache\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

/**
 * api: https://badmintonplayer.dk/publicapi/swagger/index.html
 *
 * Class BadmintonPlayerAPI
 * @package FlyCompany\BadmintonPlayerAPI
 */
class BadmintonPlayerAPI
{
    private const CACHE_TTL = 14400;

    private static $base_url = 'https://badmintonplayer.dk/publicapi/v1/';

    public function __construct(private Client $client, private Repository $cache)
    {
    }

    /**
     * @param string $email
     * @param string $password
     * @param Repository $cache
     * @return BadmintonPlayerAPI
     * @throws JsonException
     */
    public static function make(string $email, string $password, Repository $cache): BadmintonPlayerAPI
    {
        $client = new Client([
            'base_uri' => self::$base_url,
        ]);

        $client = self::auth($client, $email, $password);

        return new self($client, $cache);
    }

    /**
     * @param Client $client
     * @param string $email
     * @param string $password
     * @return Client
     * @throws JsonException
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
     * @throws InvalidArgumentException
     */
    public function getCurrentLeagueMatches(): array
    {
        $cacheKey = 'badmintonplayer-api:leagueMatch';
        $contents = $this->cache->get($cacheKey);
        if($contents === null){
            $response = $this->client->get('LeagueMatch');
            $contents = $response->getBody()->getContents();
            $this->cache->put($cacheKey, $contents, self::CACHE_TTL);
        }


        $serializer = SerializerHelper::getSerializer();
        /** @var TeamMatch[] $teamMatches */
        $teamMatches = $serializer->deserialize($contents, TeamMatch::class . '[]', 'json');
        return $teamMatches;
    }

    /**
     * Team matches information with lineups of allowed League divisions for current season. Lineup will be available when match was happen.
     *
     * @return TeamMatchLineupCollection|TeamMatchLineup[]
     * @throws ExceptionInterface
     * @throws JsonException
     * @throws InvalidArgumentException
     */
    public function getPlayedLeagueMatches(): TeamMatchLineupCollection
    {
        $cacheKey = 'badmintonplayer-api:leagueMatch-lineup';
        $contents = $this->cache->get($cacheKey);
        if($contents === null){
            $response = $this->client->get('LeagueMatch/lineup');
            $contents = $response->getBody()->getContents();
            $this->cache->put($cacheKey, $contents, self::CACHE_TTL);
        }

        $serializer = SerializerHelper::getSerializer();
        $data = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        $data = $this->fixTeamPlayersToBeArray($data);
        $playedTeamMatches = $serializer->denormalize($data, TeamMatchLineup::class . '[]');
        return new TeamMatchLineupCollection($playedTeamMatches);
    }

    /**
     * @param RankingPeriodType $periodType
     * @param int|null $numberOfRows
     * @return PlayersRanking
     * @throws InvalidArgumentException
     */
    public function getPlayerRanking(RankingPeriodType $periodType, ?int $numberOfRows = null): PlayersRanking
    {
        $cacheKey = "badmintonplayer-api:player-ranking:".md5($periodType->value.$numberOfRows);
        $contents = $this->cache->get($cacheKey);
        if($contents === null){
            $response = $this->client->get('Player/ranking',[
                'query' => array_filter([
                    'rankingType' => $periodType->value,
                    'numberOfrows' => $numberOfRows
                ])
            ]);
            $contents = $response->getBody()->getContents();
            $this->cache->put($cacheKey, $contents, self::CACHE_TTL);
        }

        $serializer = SerializerHelper::getSerializer();
        /** @var RankingPair $rankingPair */
        $rankingPair = $serializer->deserialize($contents, RankingPair::class, 'json');

        if ($periodType === RankingPeriodType::CURRENT) {
            return $rankingPair->current;
        }

        return $rankingPair->previous;
    }

    /**
     * @param $data
     * @return array
     */
    private function fixTeamPlayersToBeArray(&$data): array
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
