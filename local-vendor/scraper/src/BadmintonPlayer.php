<?php
declare(strict_types=1);


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
use Illuminate\Support\Str;

class BadmintonPlayer
{

    private $clientConfig = [
        'headers' => [
            'User-Agent' => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13',
            'Accept' => 'text/html',
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

    private function getRankingListPlayersHtml(int $rankingListId, int $season, string $clubId, Carbon $rankingVersion, $pageIndex, string $param): string
    {
        $params = [
            "callbackcontextkey" => $this->getToken(),
            "agefrom" => "",
            "agegroupid" => "",
            "ageto" => "",
            "birthdatefromstring" => "",
            "birthdatetostring" => "",
            "classid" => "",
            "clubid" => $clubId,
            "gender" => "",
            "getplayer" => true,
            "getversions" => true,
            "pageindex" => $pageIndex,
            "param" => $param,
            "playerid" => "",
            "pointsfrom" => "",
            "pointsto" => "",
            "rankingfrom" => "",
            "rankinglistagegroupid" => 15, // Magic number i dont understand yet
            "rankinglistid" => $rankingListId,
            "rankinglistversiondate" => $rankingVersion->format('m/d/Y'),
            "rankingto" => "",
            "regionid" => "",
            "searchall" => false,
            "seasonid" => $season,
            "sortfield" => 0,
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
     * @param int $season
     * @param string $clubId
     * @param Carbon $rankingVersion
     * @return Point[]|array
     * @throws \DiDom\Exceptions\InvalidSelectorException
     * @throws \JsonException
     */
    public function getRankingListPlayers(string $rankingList, int $season, string $clubId, Carbon $rankingVersion): array
    {
        [$rankingListId, $param] = $this->getRankingListIdAndParams($rankingList);

        $rankingCollection = [];
        for ($i = 0; $i < 100; $i++) {
            try {
                $html = $this->getRankingListPlayersHtml($rankingListId, $season, $clubId, $rankingVersion, $i, $param);
                $rankingCollection = \array_merge($rankingCollection, $this->parser->rankingListPlayers($html));
            } catch (NoPlayersException $exception) {
                break;
            }
        }
        return $rankingCollection;
    }

    private function getRankingListIdAndParams(string $rankingList): array
    {
        $mapping = [
            'Level' => [
                287,
                ''
            ],
            'HS' => [
                292,
                'M'
            ],
            'DS' => [
                292,
                'K'
            ],
            'DD' => [
                289,
                'M'
            ],
            'HD' => [
                289,
                'K'
            ],
            'MxH' => [
                292,
                'M'
            ],
            'MxD' => [
                292,
                'K'
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
    public function getTeamMatch(string $clubId, string $leagueMatchId, string $season): TeamMatch
    {
        $params = [
            "ageGroupID" => '',
            "callbackcontextkey" => $this->getToken(),
            "clubID" => $clubId,
            "leagueGroupID" => "",
            "leagueGroupTeamID" => "",
            "leagueMatchID" => $leagueMatchId,
            "playerID" => "",
            "regionID" => "",
            "seasonID" => $season,
            "subPage" => "5",
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
    private function getToken(): string
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

    private function prettyPrint(string $html)
    {
        $dom = new DOMDocument();

        $dom->preserveWhiteSpace = false;
        $dom->loadHTML('<body>' . $html . '</body>', LIBXML_HTML_NOIMPLIED);
        $dom->formatOutput = true;

        return $dom->saveXML($dom->documentElement);;
    }

}
