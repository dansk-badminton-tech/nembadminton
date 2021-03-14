<?php
declare(strict_types = 1);


namespace FlyCompany\Scraper;

use DiDom\Document;
use DOMDocument;
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

    public function __construct()
    {
        $this->client = new Client($this->clientConfig);
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
        $document = new Document($html);
        $trs = $document->find('table.matchresultschema.showmatch tr');

        $topRow = array_shift($trs)->find('td');
        $club1 = $topRow[1]->text();
        $club2 = $topRow[2]->text();

        $squad1 = new Squad();
        $squad1->playerLimit = 10;
        $squad2 = new Squad();
        $squad2->playerLimit = 10;
        foreach ($trs as $match) {
            $categoryName = $match->find('td')[0]->text();
            $category = $this->findCategoryByName($categoryName);
            $isSinglePlayer = Str::contains($categoryName, ['HS', 'DS']);

            $club1Player1 = $match->find('td')[1]->find('a')[0]->text();
            if (!$isSinglePlayer) {
                $club1Player2 = $match->find('td')[1]->find('a')[1]->text();
            } else {
                $club1Player2 = null;
            }

            $club2Player1 = $match->find('td')[2]->find('a')[0]->text();
            if (!$isSinglePlayer) {
                $club2Player2 = $match->find('td')[2]->find('a')[1]->text();
            } else {
                $club2Player2 = null;
            }

            // Squad 1
            $categoryObj = new Category();
            $categoryObj->category = $category;
            $categoryObj->name = $categoryName;
            $squad1->categories[] = $categoryObj;

            $player1 = new Player();
            $player1->name = $club1Player1;
            if ($club1Player2 !== null) {
                $player2 = new Player();
                $player2->name = $club1Player2;
                $categoryObj->players[] = $player2;
            }

            $categoryObj->players[] = $player1;

            // Squad 2
            $categoryObj = new Category();
            $categoryObj->category = $category;
            $categoryObj->name = $categoryName;
            $squad2->categories[] = $categoryObj;

            $player1 = new Player();
            $player1->name = $club2Player1;
            if ($club2Player2 !== null) {
                $player2 = new Player();
                $player2->name = $club2Player2;
                $categoryObj->players[] = $player2;
            }

            $categoryObj->players[] = $player1;
        }

        $team1 = new Team($club1, $squad1);
        $team2 = new Team($club2, $squad2);

        return new TeamMatch($team1, $team2);
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

    private function findCategoryByName(string $categoryName) : string
    {
        $category = null;
        $MD = 'MD';
        if (Str::contains($categoryName, $MD)) {
            $category = $MD;
        }

        $HS = 'HS';
        if (Str::contains($categoryName, $HS)) {
            $category = $HS;
        }

        $DS = 'DS';
        if (Str::contains($categoryName, $DS)) {
            $category = $DS;
        }

        $HD = 'HD';
        if (Str::contains($categoryName, $HD)) {
            $category = $HD;
        }

        $DD = 'DD';
        if (Str::contains($categoryName, $DD)) {
            $category = $DD;
        }

        if ($category === null) {
            throw new \RuntimeException('Unknow category ' . $category);
        }

        return $category;
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
