<?php
declare(strict_types = 1);


namespace FlyCompany\Scraper;

use Carbon\Carbon;
use FlyCompany\Scraper\Exception\MultiplePlayersFoundException;
use FlyCompany\Scraper\Exception\NoPlayersException;
use FlyCompany\Scraper\Models\Player;
use FlyCompany\Scraper\Models\PlayerSearch;
use FlyCompany\Scraper\Models\Team;
use FlyCompany\Scraper\Models\TeamMatch;
use GuzzleHttp\Client;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BadmintonPlayer
{

    const LEVEL_RANKING_NUMBER = 287;
    private $clientConfig = [
        'headers'  => [
            'User-Agent' => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13',
            'Accept'     => 'text/html',
        ],
        'base_uri' => 'https://badmintonplayer.dk/',
    ];

    private $token;

    /**
     * @var Client
     */
    private Client $client;

    /**
     * @var Parser
     */
    private Parser $parser;
    private Repository $cache;

    public function __construct(Parser $parser, Repository $cache)
    {
        $this->client = new Client($this->clientConfig);
        $this->parser = $parser;
        $this->cache = $cache;
    }

    /**
     * @return array
     * @throws \JsonException
     */
    public function getClubs() : array
    {
        $client = new Client();
        $response = $client->get('https://www.badmintonplayer.dk/sportsresults/components/clubcomponents/clublistclientscript.aspx?unionid=1');
        $body = $response->getBody()->getContents();
        $needle = 'var SportsResultsTeamList =';
        $pos = strpos($body, $needle);
        $pos += strlen($needle);

        $clubsStr = rtrim(trim(substr($body, $pos)), ';');
        $clubsStr = str_replace("'", '"', $clubsStr);

        $clubs = json_decode($clubsStr, true, 512, JSON_THROW_ON_ERROR);

        $responseJson = [];
        foreach ($clubs as $clubPair) {

            $clubName = str_replace("–", "-", $clubPair[0]);
            $responseJson[] = [
                'id'   => (int)$clubPair[1],
                'name' => $clubName,
            ];
        }

        $responseJson = Arr::sort($responseJson, 'name');

        return $responseJson;
    }

    public function getTeamFights(int $season, int $clubId, int $ageGroupID, int $leagueGroupId, string $clubName) : array
    {
        $params = [
            "callbackcontextkey" => $this->getToken(),
            "ageGroupID"         => (string)$ageGroupID,
            "clubID"             => (string)$clubId,
            "leagueGroupID"      => (string)$leagueGroupId,
            "leagueGroupTeamID"  => "",
            "leagueMatchID"      => "",
            "playerID"           => "",
            "regionID"           => "",
            "seasonID"           => (string)$season,
            "subPage"            => "4",
        ];

        $response = $this->client->post('SportsResults/Components/WebService1.asmx/GetLeagueStanding', [
            'json' => $params,
        ]);

        $data = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        if (!isset($data['d'])) {
            throw new \RuntimeException('Did not get any data back');
        }
        $teamFights = $this->parser->teamFights($data["d"]['html']);

        $teamFights = array_filter($teamFights, static function ($teamFight) use ($clubName) {
            return in_array($clubName, $teamFight['teams']);
        });

        return $teamFights;
    }

    /**
     * @param int $season
     * @param int $clubId
     *
     * @return array|Team[]
     * @throws \JsonException
     */
    public function getClubTeams(int $season, int $clubId) : array
    {
        $params = [
            "callbackcontextkey" => $this->getToken(),
            "ageGroupID"         => "",
            "clubID"             => (string)$clubId,
            "leagueGroupID"      => "",
            "leagueGroupTeamID"  => "",
            "leagueMatchID"      => "",
            "playerID"           => "",
            "regionID"           => "",
            "seasonID"           => (string)$season,
            "subPage"            => "6",
        ];

        $response = $this->client->post('SportsResults/Components/WebService1.asmx/GetLeagueStanding', [
            'json' => $params,
        ]);

        $data = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        if (!isset($data['d'])) {
            throw new \RuntimeException('Did not get any data back');
        }

        return $this->parser->clubTeams($data["d"]['html']);
    }

    /**
     * @param  int  $rankingListId
     * @param  int  $season
     * @param  string  $clubId
     * @param  Carbon  $rankingVersion
     * @param        $pageIndex
     * @param  string  $param
     * @param  string  $gender
     * @param  string  $playerId
     *
     * @return string
     * @throws \JsonException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    private function getRankingListPlayersHtml(int $rankingListId, int $season, string $clubId, Carbon $rankingVersion, $pageIndex, string $param, string $gender, string $playerId = "") : string
    {
        $params = [
            "callbackcontextkey"     => $this->getToken(),
            "agefrom"                => "",
            "agegroupid"             => "",
            "ageto"                  => "",
            "birthdatefromstring"    => "",
            "birthdatetostring"      => "",
            "classid"                => "",
            "clubid"                 => $clubId,
            "gender"                 => $gender,
            "getplayer"              => $clubId === "",
            "getversions"            => $clubId === "",
            "pageindex"              => $pageIndex,
            "param"                  => $param,
            "playerid"               => $playerId,
            "pointsfrom"             => "",
            "pointsto"               => "",
            "rankingfrom"            => "",
            "rankinglistagegroupid"  => (string)15, // Magic number i dont understand yet
            "rankinglistid"          => $rankingListId,
            "rankinglistversiondate" => $rankingVersion->format('m/d/Y'),
            "rankingto"              => "",
            "regionid"               => "",
            "searchall"              => true,
            "seasonid"               => (string)$season,
            "sortfield"              => 0,
        ];

        $url = "SportsResults/Components/WebService1.asmx/GetRankingListPlayersSenior";
        Log::debug("Requesting {$url}: " . \json_encode($params, JSON_THROW_ON_ERROR));

        $body = $this->sendRequestAndGetBody($url, $params);
        $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        if (!isset($data['d'])) {
            throw new \RuntimeException('Did not get any data back');
        }

        $html = Str::replaceFirst("<table class='RankingListGrid'", "<table class='RankingListGrid'>", $data["d"]['Html']);

        //Log::debug("HTML: $html");
        return $html;
    }

    /**
     * @param  string  $url
     * @param  array  $json
     * @return string
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function sendRequestAndGetBody(string $url, array $json) : string{
        $cacheBody = $this->cache->get($this->resolveCacheKey($json));
        if($cacheBody === null){
            $response = $this->client->post($url, [
                'json' => $json,
            ]);

            $cacheBody = $response->getBody()->getContents();
            $this->cache->put($this->resolveCacheKey($json), $cacheBody, 120);
        }
        return $cacheBody;
    }

    private function resolveCacheKey(array $postParams) : string{
        unset($postParams['callbackcontextkey']);
        return md5(implode('-', $postParams));
    }

    public function getPlayerByBadmintonPlayerId(int $badmintonPlayerId, Carbon $rankingVersion, int $season) : Player
    {
        $params = [
            "callbackcontextkey" => $this->getToken(),
            "getplayerdata"      => true,
            "playerid"           => (string)$badmintonPlayerId,
            "seasonid"           => null,
            "showUserProfile"    => true,
            "showheader"         => false,
        ];

        $url = 'SportsResults/Components/WebService1.asmx/GetPlayerProfile';

        Log::debug("Requesting {$url}: " . \json_encode($params, JSON_THROW_ON_ERROR));

        $response = $this->client->post($url, [
            'json' => $params,
        ]);

        $data = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        if (!isset($data['d'])) {
            throw new \RuntimeException('Did not get any data back');
        }

        return $this->getPlayerByBadmintonId($data['d']['playernumber'], $rankingVersion, $season);
    }

    public function getPlayerByBadmintonId(string $badmintonId, Carbon $rankingVersion, int $season) : Player
    {
        $players = $this->searchPlayers("", $badmintonId);

        return $this->getRankingList($players, $rankingVersion, $season);
    }

    public function getPlayerByName(string $name, Carbon $rankingVersion, int $season) : Player
    {
        $players = $this->searchPlayers($name);

        return $this->getRankingList($players, $rankingVersion, $season);
    }

    /**
     * @param  array|Player[]  $players
     * @param  Carbon  $rankingVersion
     * @param  int  $season
     * @return Player
     * @throws \DiDom\Exceptions\InvalidSelectorException
     * @throws \JsonException
     */
    private function getRankingList(array $players, Carbon $rankingVersion, int $season) : Player
    {
        $count = count($players);
        if ($count > 1 || $count === 0) {
            throw new MultiplePlayersFoundException("Multiple or no players found under the name {$players[0]->name}");
        }
        $player = $players[0];

        $playersCollection = [];
        foreach (static::rankingLists($player->gender) as $rankingList) {
            for ($i = 0; $i < 100; $i++) {
                [$rankingListId, $param, $gender] = $this->getRankingListIdAndParams($rankingList);
                try {
                    $html = $this->getRankingListPlayersHtml($rankingListId, $season, "", $rankingVersion, $i, $param, $gender, $player->badmintonPlayerInternalId);
                    $playersCollection = \array_merge($playersCollection, $this->parser->rankingListPlayers($html, $rankingList));
                } catch (NoPlayersException $exception) {
                    break;
                }
            }
        }

        $points = Arr::pluck($playersCollection, 'points');
        if(empty($playersCollection)){
            throw new \RuntimeException("Found not points in any category for {$player->name}");
        }
        $player = $playersCollection[0];
        $player->points = $points;

        return $player;
    }

    /**
     * @param string $rankingList
     * @param int    $season
     * @param string $clubId
     * @param Carbon $rankingVersion
     *
     * @return \FlyCompany\Scraper\Models\Player[]|array
     * @throws \DiDom\Exceptions\InvalidSelectorException
     * @throws \JsonException
     */
    public function getRankingListPlayersByClub(string $rankingList, int $season, string $clubId, Carbon $rankingVersion) : array
    {
        [$rankingListId, $param, $gender] = $this->getRankingListIdAndParams($rankingList);

        Log::debug("Fetching ranking list: $rankingList, gender: $gender, clubId: $clubId, season: $season, version: $rankingVersion");
        $playersCollection = [];
        for ($i = 0; $i < 100; $i++) {
            Log::debug("Fetching page: $i");
            try {
                $html = $this->getRankingListPlayersHtml($rankingListId, $season, $clubId, $rankingVersion, $i, $param, $gender);
                /** @var Player[] $playersCollection */
                $playersCollection = \array_merge($playersCollection, $this->parser->rankingListPlayers($html, $rankingList));
            } catch (NoPlayersException) {
                Log::debug("No players found on page: $i");
                break;
            }
        }

        // Apply version to scraped points
        foreach ($playersCollection as $player){
            foreach ($player->points as $point) {
                $point->version = $rankingVersion;
            }
        }

        return $playersCollection;
    }

    /**
     * @param int    $season
     * @param string $clubId
     * @param Carbon $rankingVersion
     *
     * @return array
     * @throws \DiDom\Exceptions\InvalidSelectorException
     * @throws \JsonException
     */
    public function getAllRankingListPlayers(int $season, string $clubId, Carbon $rankingVersion) : array
    {
        $rankingLists = [];
        foreach (static::rankingLists() as $rankingList) {
            $rankingLists[$rankingList] = $this->getRankingListPlayersByClub($rankingList, $season, $clubId, $rankingVersion);
        }

        return $rankingLists;
    }

    private function getRankingListIdAndParams(string $rankingList) : array
    {
        $mapping = [
            'DL'  => [
                self::LEVEL_RANKING_NUMBER,
                '',
                'K',
            ],
            'HL'  => [
                self::LEVEL_RANKING_NUMBER,
                '',
                'M',
            ],
            'HS'  => [
                288,
                'M',
                '',
            ],
            'DS'  => [
                288,
                'K',
                '',
            ],
            'DD'  => [
                289,
                'K',
                '',
            ],
            'HD'  => [
                289,
                'M',
                '',
            ],
            'MxH' => [
                292,
                'M',
                '',
            ],
            'MxD' => [
                292,
                'K',
                '',
            ],
        ];

        return $mapping[$rankingList];
    }

    /**
     * @param  string  $clubId
     * @param  string  $leagueMatchId
     * @param  string  $season
     *
     * @return TeamMatch
     * @throws \DiDom\Exceptions\InvalidSelectorException
     * @throws \JsonException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function getTeamMatch(string $clubId, string $leagueMatchId, string $season) : TeamMatch
    {
        $params = [
            "ageGroupID"         => '',
            "callbackcontextkey" => $this->getToken(),
            "clubID"             => $clubId,
            "leagueGroupID"      => "",
            "leagueGroupTeamID"  => "",
            "leagueMatchID"      => $leagueMatchId,
            "playerID"           => "",
            "regionID"           => "",
            "seasonID"           => $season,
            "subPage"            => "5",
        ];

        $url = 'SportsResults/Components/WebService1.asmx/GetLeagueStanding';

        Log::debug("Requesting {$url}: " . \json_encode($params, JSON_THROW_ON_ERROR));
        $body = $this->sendRequestAndGetBody($url, $params);
        $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        if (!isset($data['d'])) {
            throw new \RuntimeException('Did not get any data back');
        }
        $html = $data["d"]['html'];

        return $this->parser->teamMatch($html);
    }

    /**
     * @param  string  $name
     * @param  string  $badmintonId
     * @return array
     * @throws \DiDom\Exceptions\InvalidSelectorException
     * @throws \JsonException
     */
    public function searchPlayers(string $name = "", string $badmintonId = "") : array
    {
        $params = [
            "agegroupcontext"    => 0,
            "agegroupid"         => "",
            "callbackcontextkey" => $this->getToken(),
            "clubid"             => "",
            "gender"             => "",
            "licenseonly"        => false,
            "name"               => $name,
            "playernumber"       => $badmintonId,
            "searchteam"         => false,
            "selectfunction"     => "SPSel1",
            "tournamentdate"     => "",
        ];

        $url = 'SportsResults/Components/WebService1.asmx/SearchPlayer';
        Log::debug("Requesting {$url}: " . \json_encode($params, JSON_THROW_ON_ERROR));

        $response = $this->client->post($url, [
            'json' => $params,
        ]);

        $data = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        if (!isset($data['d'])) {
            throw new \RuntimeException('Did not get any data back');
        }
        $html = $data["d"]['Html'];

        return $this->parser->searchPlayer($html);
    }

    /**
     * @return Carbon[]
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \JsonException
     */
    public function getVersions(int $season): array
    {
        $params = [
            'callbackcontextkey' => $this->getToken(),
            'rankinglistagegroupid' => '',
            'rankinglistid' => self::LEVEL_RANKING_NUMBER,
            'seasonid' => $season,
            'rankinglistversiondate' => '',
            'agegroupid' => '',
            'classid' => '',
            'gender' => '',
            'clubid' => '',
            'searchall' => false,
            'regionid' => '',
            'pointsfrom' => '',
            'pointsto' => '',
            'rankingfrom' => '',
            'rankingto' => '',
            'birthdatefromstring' => '',
            'birthdatetostring' => '',
            'agefrom' => '',
            'ageto' => '',
            'playerid' => '',
            'param' => '',
            'pageindex' => 0,
            'sortfield' => 0,
            'getversions' => true,
            'getplayer' => true,
        ];

        $url = 'SportsResults/Components/WebService1.asmx/GetRankingListPlayers';
        $body = $this->sendRequestAndGetBody($url, $params);
        $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);

        if (!isset($data['d'])) {
            throw new \RuntimeException('Did not get any data back');
        }
        return BadmintonPlayerHelper::convertToCarbonObjects($data["d"]['Versions']);
    }

    /**
     * @return string
     */
    private function getToken() : string
    {
        if ($this->token !== null) {
            return $this->token;
        }

        $request = $this->client->get('/');
        $response = $request->getBody();

        $result = preg_replace("/((\r?\n)|(\r\n?))/", ',', $response->getContents());
        $result = explode(",", $result);
        foreach ($result as $value) {
            if (str_contains($value, 'SR_CallbackContext')) {
                $key = $value;
            }
        }
        preg_match("/\'([^\)]*)\'/", $key, $key);
        $this->token = $key[1];

        return $this->token;
    }

    public static function calculateSeason(Carbon $currentTime) : int
    {
        if ($currentTime->month > 6) {
            return $currentTime->year;
        }

        return $currentTime->year - 1;
    }

    public static function getCurrentSeason() : int
    {
        return static::calculateSeason(Carbon::now());
    }

    /**
     *
     * @return string[]
     */
    public static function rankingLists(?string $gender = null) : array
    {
        if ($gender === null) {
            return [
                'DL',
                'HL',
                'HS',
                'DS',
                'HD',
                'DD',
                'MxD',
                'MxH',
            ];
        }

        if ($gender === 'K') {
            return [
                'DL',
                'DS',
                'DD',
                'MxD',
            ];
        }

        if ($gender === 'M') {
            return [
                'HL',
                'HS',
                'HD',
                'MxH',
            ];
        }
        throw new \RuntimeException("Unknown gender '$gender'");
    }

    public static function findGenderByRanking(string $ranking) : string
    {
        $genderRankingMapping = [
            'DL'  => 'K',
            'HL'  => 'M',
            'HS'  => 'M',
            'DS'  => 'K',
            'HD'  => 'M',
            'DD'  => 'K',
            'MxD' => 'K',
            'MxH' => 'M',
        ];

        return $genderRankingMapping[$ranking];
    }

}
