<?php
declare(strict_types = 1);

namespace FlyCompany\Scraper;

use DiDom\Document;
use FlyCompany\Scraper\Exception\NoPlayersException;
use FlyCompany\Scraper\Exception\NoPlayersFoundInTeamMatchException;
use FlyCompany\Scraper\Models\Category;
use FlyCompany\Scraper\Models\Player;
use FlyCompany\Scraper\Models\Point;
use FlyCompany\Scraper\Models\Squad;
use FlyCompany\Scraper\Models\Team;
use FlyCompany\Scraper\Models\TeamMatch;
use Illuminate\Support\Str;
use Psr\Log\LoggerInterface;

class Parser
{

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $output;

    public function __construct(LoggerInterface $output)
    {
        $this->output = $output;
    }

    public function teamFights(string $html) : array
    {
        $document = new Document($html);
        $trs = $document->find('table.matchlist tr');

        // remove table header
        array_shift($trs);

        $teams = [];
        foreach ($trs as $tr) {
            if ($tr->attr('class') !== 'roundheader') {
                $data = [];
                foreach ($tr->find('td.team') as $td) {
                    $data['teams'][] = $td->text();
                }
                foreach ($tr->find('td.matchno') as $td) {
                    $data['matchId'] = $td->text();
                }
                $data['gameTime'] = $this->findTime((string)$tr->find('td.time')[0]);
                $teams[] = $data;
            }
        }

        return $teams;
    }

    public function clubTeams(string $html) : array
    {
        $document = new Document($html);
        $trs = $document->find('table.clubgrouplist tr.grouprow');

        $teams = [];
        foreach ($trs as $teamTr) {
            $onClick = $teamTr->find('td a')[0]->attr('onclick');
            preg_match_all('/\d+/', $onClick, $ids);
            $leagueGroupId = $ids[0][2];
            $ageGroupId = $ids[0][3];

            $team = new Team($teamTr->find('td')[0]->text());
            $team->leagueGroupId = $leagueGroupId;
            $team->ageGroupId = $ageGroupId;
            $team->league = $teamTr->find('td')[1]->text();

            $teams[] = $team;
        }

        return $teams;
    }

    public function searchPlayer(string $html) : array
    {
        $document = new Document($html);
        $trs = $document->find('table tr');

        $players = [];
        foreach ($trs as $playerTr) {
            $player = [];
            $onClick = $playerTr->attr('onclick');
            $arguments = self::parseFunction($onClick);
            $badmintonPlayerInternalId = $arguments[0];

            $tds = $playerTr->find('td');
            $player['badmintonId'] = $tds[1]->text();
            $player['badmintonPlayerInternalId'] = $badmintonPlayerInternalId;
            $player['name'] = $tds[2]->text();
            $player['club'] = $tds[3]->text();

            $players[] = $player;
        }

        return $players;
    }

    public static function parseFunction(string $function) : array
    {
        /** @var string[] $parts */
        $parts = preg_match("/'(.+)'/", $function);
        $arguments = $parts[0];
        $arguments = explode(',', $arguments);

        return array_map(static function ($value) {
            return trim(trim($value, '\''));
        }, $arguments);
    }

    private function findTime(string $text) : string
    {
        $normalizedStr = \str_replace('‑', '-', $text);
        preg_match('/(\d\d-\d\d-\d\d\d\d)/', $normalizedStr, $dateMatches);
        $date = $dateMatches[0];
        preg_match('/(\d\d:\d\d)/', $normalizedStr, $timeMatches);

        if (!empty($timeMatches)) {
            $date .= ' ' . $timeMatches[0];
        }

        return $date;
    }

    /**
     * @param string $html
     *
     * @return Player[]
     * @throws \DiDom\Exceptions\InvalidSelectorException
     */
    public function rankingListPlayers(string $html, string $rankingList) : array
    {
        $document = new Document($html);
        $trs = $document->find('table.RankingListGrid tr');

        // Remove top of table
        array_shift($trs);

        $playersCollection = [];
        if (count($trs) < 3) {
            throw new NoPlayersException('No players');
        }
        foreach ($trs as $tr) {

            // Will continue if name is not set
            $aTagName = $tr->find('td.name a')[0] ?? false;
            if ($aTagName) {
                $name = $aTagName->text();
            } else {
                continue;
            }

            $points = $tr->find('td.points')[0]->text();
            $refId = \str_replace('‑', '-', $tr->find('td.playerid')[0]->text());
            $position = preg_replace("/[^0-9]/", "", $tr->find('td')[1]->text());
            $vintageText = $tr->find('td.clas')[0]->text();

            preg_match_all("/(SEN|U09|U11|U13|U15|U17|U19|U23)/", $vintageText, $matches);
            $vintage = $matches[1][0] ?? '';

            $player = new Player();
            $player->name = $name;
            $player->gender = BadmintonPlayer::findGenderByRanking($rankingList);
            $player->points = [new Point((int)$points, (int)$position, $vintage)];
            $player->refId = $refId;
            $playersCollection[] = $player;
        }

        return $playersCollection;
    }

    /**
     * @param string $html
     *
     * @return TeamMatch
     * @throws \DiDom\Exceptions\InvalidSelectorException
     */
    public function teamMatch(string $html) : TeamMatch
    {
        $document = new Document($html);
        $trs = $document->find('table.matchresultschema.showmatch tr');

        $playersTrs = array_shift($trs);
        if($playersTrs === null){
            throw new NoPlayersFoundInTeamMatchException('Could not find any players on match');
        }
        $topRow = $playersTrs->find('td');
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
            throw new \RuntimeException('Unknown category ' . $category);
        }

        return $category;
    }

}
