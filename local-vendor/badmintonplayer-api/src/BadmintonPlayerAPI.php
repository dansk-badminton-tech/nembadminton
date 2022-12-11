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
use Illuminate\Support\Facades\Log;
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
    private const CACHE_TTL = 129600;
    private const CACHE_KEY_BADMINTONPLAYER_API_LEAGUE_MATCH = 'badmintonplayer-api:leagueMatch';
    private const CACHE_KEY_BADMINTONPLAYER_API_LEAGUE_MATCH_LINEUP = 'badmintonplayer-api:leagueMatch-lineup';

    private static string $base_url = 'https://badmintonplayer.dk/publicapi/v1/';
    private bool $overrideCache = false;

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
            'timeout' => 600,
            'connect_timeout' => 60,
            'read_timeout' => 300
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

    public function overrideCache(): void
    {
        $this->overrideCache = true;
    }

    /**
     * Get all team matches for current season
     *
     * @return TeamMatch[]
     * @throws InvalidArgumentException
     */
    public function getCurrentLeagueMatches(): array
    {
        $contents = $this->cache->get(self::CACHE_KEY_BADMINTONPLAYER_API_LEAGUE_MATCH);
        if($contents === null || $this->overrideCache){
            Log::info("No cache found with key '" . self::CACHE_KEY_BADMINTONPLAYER_API_LEAGUE_MATCH . "'. Fetching from badmintonplayer.dk....");
            $response = $this->client->get('LeagueMatch');
            $contents = $response->getBody()->getContents();
            $this->cache->put(self::CACHE_KEY_BADMINTONPLAYER_API_LEAGUE_MATCH, $contents, self::CACHE_TTL);
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
        $contents = $this->cache->get(self::CACHE_KEY_BADMINTONPLAYER_API_LEAGUE_MATCH_LINEUP);
        if($contents === null || $this->overrideCache){
            Log::info("No cache found with key '" . self::CACHE_KEY_BADMINTONPLAYER_API_LEAGUE_MATCH_LINEUP . "'. Fetching from badmintonplayer.dk....");
            $response = $this->client->get('LeagueMatch/lineup');
            $contents = $response->getBody()->getContents();
            $this->cache->put(self::CACHE_KEY_BADMINTONPLAYER_API_LEAGUE_MATCH_LINEUP, $contents, self::CACHE_TTL);
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
        Log::info("Fetching ranking list '{$periodType->value}'");
        $cacheKey = "badmintonplayer-api:player-ranking-".md5($periodType->value.$numberOfRows);
        $contents = $this->cache->get($cacheKey);
        if($contents === null || $this->overrideCache){
            Log::info("No cache found with key '$cacheKey'. Fetching from badmintonplayer.dk....");
            $response = $this->client->get('Player/ranking',[
                'query' => array_filter([
                    'rankingType' => $periodType->value,
                    'numberOfrows' => $numberOfRows
                ])
            ]);
            $contents = $response->getBody()->getContents();
            $this->cache->put($cacheKey, $contents, self::CACHE_TTL);
            Log::info("Putting ranking list into cache");
        }else{
            Log::info("Ranking list found in cache");
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
