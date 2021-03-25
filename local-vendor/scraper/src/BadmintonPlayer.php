<?php
declare(strict_types = 1);


namespace FlyCompany\Scraper;

use Carbon\Carbon;
use DiDom\Document;
use DOMDocument;
use FlyCompany\Scraper\Exception\NoPlayersException;
use FlyCompany\Scraper\Models\Point;
use FlyCompany\Scraper\Models\Team;
use FlyCompany\Scraper\Models\TeamMatch;
use FlyCompany\TeamFight\Models\Category;
use FlyCompany\TeamFight\Models\Player;
use FlyCompany\TeamFight\Models\Squad;
use GuzzleHttp\Client;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class BadmintonPlayer
{

    private $clientConfig = [
        'headers' => [
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

    public function __construct(Parser $parser)
    {
        $this->client = new Client($this->clientConfig);
        $this->parser = $parser;
    }

    /**
     * @return array
     * @throws \JsonException
     */
    public function getClubs() : array
    {
        $client = new Client();
        $response = $client->get('http://www.badmintonpeople.dk/sportsresults/components/clubcomponents/clublistclientscript.aspx?unionid=1');
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
                'id'   => $clubPair[1],
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

    private function getRankingListPlayersHtml(int $rankingListId, int $season, string $clubId, Carbon $rankingVersion, $pageIndex, string $param, string $gender) : string
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
            "getplayer"              => true,
            "getversions"            => true,
            "pageindex"              => $pageIndex,
            "param"                  => $param,
            "playerid"               => "",
            "pointsfrom"             => "",
            "pointsto"               => "",
            "rankingfrom"            => "",
            "rankinglistagegroupid"  => 15, // Magic number i dont understand yet
            "rankinglistid"          => $rankingListId,
            "rankinglistversiondate" => $rankingVersion->format('m/d/Y'),
            "rankingto"              => "",
            "regionid"               => "",
            "searchall"              => false,
            "seasonid"               => $season,
            "sortfield"              => 0,
        ];

        $response = $this->client->post('SportsResults/Components/WebService1.asmx/GetRankingListPlayers', [
            'json' => $params,
        ]);

        $data = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        if (!isset($data['d'])) {
            throw new \RuntimeException('Did not get any data back');
        }

        return Str::replaceFirst("<table class='RankingListGrid'", "<table class='RankingListGrid'>", $data["d"]['Html']);
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
    public function getRankingListPlayers(string $rankingList, int $season, string $clubId, Carbon $rankingVersion) : array
    {
        [$rankingListId, $param, $gender] = $this->getRankingListIdAndParams($rankingList);

        $playersCollection = [];
        for ($i = 0; $i < 100; $i++) {
            try {
                $html = $this->getRankingListPlayersHtml($rankingListId, $season, $clubId, $rankingVersion, $i, $param, $gender);
                $playersCollection = \array_merge($playersCollection, $this->parser->rankingListPlayers($html));
            } catch (NoPlayersException $exception) {
                break;
            }
        }

        return $playersCollection;
    }

    private function getRankingListIdAndParams(string $rankingList) : array
    {
        $mapping = [
            'DL'    => [
                287,
                '',
                'K',
            ],
            'HL'    => [
                287,
                '',
                'M',
            ],
            'HS'    => [
                288,
                'M',
                '',
            ],
            'DS'    => [
                288,
                'K',
                '',
            ],
            'DD'    => [
                289,
                'K',
                '',
            ],
            'HD'    => [
                289,
                'M',
                '',
            ],
            'MxH'   => [
                292,
                'M',
                '',
            ],
            'MxD'   => [
                292,
                'K',
                '',
            ],
        ];

        return $mapping[$rankingList];
    }

    /**
     * @param string $clubId
     * @param string $leagueMatchId
     * @param string $season
     *
     * @return TeamMatch
     * @throws \DiDom\Exceptions\InvalidSelectorException
     * @throws \JsonException
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

        $response = $this->client->post('SportsResults/Components/WebService1.asmx/GetLeagueStanding', [
            'json' => $params,
        ]);

        $data = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        if (!isset($data['d'])) {
            throw new \RuntimeException('Did not get any data back');
        }
        $html = $data["d"]['html'];

        return $this->parser->teamMatch($html);
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

        $result = preg_replace("/((\r?\n)|(\r\n?))/", ',', $response);
        $result = explode(",", $result);
        foreach ($result as $value) {
            if (strpos($value, 'SR_CallbackContext') !== false) {
                $key = $value;
            }
        }
        preg_match("/\'([^\)]*)\'/", $key, $key);
        $this->token = $key[1];

        return $this->token;
    }

}
