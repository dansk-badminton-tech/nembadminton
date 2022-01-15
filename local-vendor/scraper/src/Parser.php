<?php

declare(strict_types=1);

namespace FlyCompany\Scraper;

use DiDom\Document;
use DiDom\Element;
use FlyCompany\Scraper\Exception\NoPlayersException;
use FlyCompany\Scraper\Exception\NoPlayersFoundInTeamMatchException;
use FlyCompany\Scraper\Models\Category;
use FlyCompany\Scraper\Models\LeagueScoreboardEntry;
use FlyCompany\Scraper\Models\LeagueScoreboardTeam;
use FlyCompany\Scraper\Models\Player;
use FlyCompany\Scraper\Models\PlayerSearch;
use FlyCompany\Scraper\Models\Point;
use FlyCompany\Scraper\Models\Squad;
use FlyCompany\Scraper\Models\Team;
use FlyCompany\Scraper\Models\TeamFight;
use FlyCompany\Scraper\Models\TeamMatch;
use Illuminate\Support\Str;
use Psr\Log\LoggerInterface;

class Parser
{

    public function leagueScoreboard(string $html): LeagueScoreboard
    {
        $document = new Document($html);
        $leagueName = $document->find('h3');
        if (!isset($leagueName[0])) {
            throw new \RuntimeException('Could not find league name');
        }

        $trs = $document->find('table.groupstandings tr');

        // remove table header
        array_shift($trs);

        $leagueScoreboard = new LeagueScoreboard($leagueName[0]->text());
        foreach ($trs as $tr) {
            $teamName = $tr->find('td.team')[0]->text();

            $onClick = $tr->find('td.team a')[0]->attr('onclick');
            preg_match_all('/\d+/', $onClick, $ids);
            [, , $leagueGroupId, $ageGroupId, , $leagueGroupTeamId] = $ids[0];

            $scoresTd = $tr->find('td.score');
            $scoresResolved = array_map(static function (Element $e) {
                return $e->text();
            }, $scoresTd);
            [$numberOfFights, $numberOfFightWins, $numberOfWinSets, $numberOfLostSets, $totalPoints] = $scoresResolved;

            $leagueScoreboardEntry = new LeagueScoreboardEntry(new LeagueScoreboardTeam($teamName,
                (int)$leagueGroupId, (int)$ageGroupId, (int)$leagueGroupTeamId), (int)$numberOfFights,
                (int)$numberOfFightWins, (int)$numberOfWinSets, (int)$numberOfLostSets, (int)$totalPoints);

            $leagueScoreboard->addLeagueScoreboardEntry($leagueScoreboardEntry);
        }

        return $leagueScoreboard;
    }

    /**
     * @param  string  $html
     * @return TeamFight[]|array
     * @throws \DiDom\Exceptions\InvalidSelectorException
     */
    public function teamFights(string $html): array
    {
        $document = new Document($html);
        $trs = $document->find('table.matchlist tr');

        // remove table header
        array_shift($trs);

        $teams = [];
        $currentRound = '';
        $currentRoundDate = '';
        foreach ($trs as $tr) {
            if($tr->attr('class') === 'roundheader'){
                preg_match('/(\d+) (\d\d-\d\d-\d\d\d\d)/', $tr->text(), $matches);
                [,$currentRound,$currentRoundDate] = $matches;
            }
            if ($tr->attr('class') !== 'roundheader') {
                $teamFightEntry = new TeamFight();
                foreach ($tr->find('td.team') as $td) {
                    $teamFightEntry->teams[] = $td->text();
                }
                foreach ($tr->find('td.matchno') as $td) {
                    $teamFightEntry->matchId = $td->text();
                }
                $teamFightEntry->gameTime = $this->findTime((string)$tr->find('td.time')[0]);
                $teamFightEntry->round = $currentRound;
                $teamFightEntry->roundDate = $currentRoundDate;
                $teams[] = $teamFightEntry;
            }
        }

        return $teams;
    }

    public function clubTeams(string $html): array
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

    /**
     * @param  string  $html
     *
     * @return array|PlayerSearch[]
     * @throws \DiDom\Exceptions\InvalidSelectorException
     */
    public function searchPlayer(string $html): array
    {
        $document = new Document($html);
        $trs = $document->find('table tr');

        $players = [];
        foreach ($trs as $playerTr) {
            $player = [];
            $onClick = $playerTr->attr('onclick');
            $arguments = self::parseFunction($onClick);
            $badmintonPlayerInternalId = $arguments[0];
            $gender = str_replace('\'', '', $arguments[5]);

            $tds = $playerTr->find('td');
            $player = new PlayerSearch();
            $player->refId = $tds[1]->text();
            $player->badmintonPlayerInternalId = $badmintonPlayerInternalId;
            $player->name = $tds[2]->text();
            $player->club = $tds[3]->text();
            $player->gender = $gender;

            $players[] = $player;
        }

        return $players;
    }

    public static function parseFunction(string $function): array
    {
        /** @var string[] $parts */
        $parts = preg_match_all("/'(.+)'/", $function, $out);
        $arguments = $out[0][0];
        $arguments = explode(',', $arguments);

        return array_map(static function ($value) {
            return trim(trim($value, '\''));
        }, $arguments);
    }

    private function findTime(string $text): string
    {
        $normalizedStr = \str_replace('‑', '-', $text);
        preg_match('/(\d\d-\d\d-\d\d\d\d)/', $normalizedStr, $dateMatches);
        $date = $dateMatches[0];
        preg_match('/(\d\d:\d\d)/', $normalizedStr, $timeMatches);

        if (!empty($timeMatches)) {
            $date .= ' '.$timeMatches[0];
        }

        return $date;
    }

    /**
     * @param  string  $html
     *
     * @return Player[]
     * @throws \DiDom\Exceptions\InvalidSelectorException
     */
    public function rankingListPlayers(string $html, string $rankingList): array
    {
        $document = new Document($html);
        $trs = $document->find('table.RankingListGrid tr');

        // Remove top of table
        array_shift($trs);

        if (count($trs) < 1) {
            throw new NoPlayersException('No players');
        }
        $testRow = $trs[0];
        $seePlayer = $testRow->has('td.name') && $testRow->has('td.playerid');
        if (!$seePlayer) {
            throw new NoPlayersException('No players');
        }

        $playersCollection = [];
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
            $point = new Point((int)$points, (int)$position, $vintage);
            $point->setCategory(BadmintonPlayerHelper::rankingListNormalized($rankingList));
            $player->points = [$point];
            $player->refId = $refId;
            $playersCollection[] = $player;
        }

        return $playersCollection;
    }

    /**
     * @param  string  $html
     *
     * @return TeamMatch
     * @throws \DiDom\Exceptions\InvalidSelectorException
     */
    public function teamMatch(string $html): TeamMatch
    {
        $document = new Document($html);
        $trs = $document->find('table.matchresultschema.showmatch tr');

        $playersTrs = array_shift($trs);
        if ($playersTrs === null) {
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

            $playerTd = $match->find('td')[1];
            $aElement = $playerTd->find('a')[0] ?? null;
            [$club1Player1Name, $club1Player1BadmintonPlayerId] = $this->extractNameAndId($aElement);
            if (!$isSinglePlayer) {
                $aElement1 = $playerTd->find('a')[1] ?? null;
                [$club1Player2Name, $club1Player2BadmintonPlayerId] = $this->extractNameAndId($aElement1);
            } else {
                $club1Player2Name = null;
            }

            $playerTd2 = $match->find('td')[2];
            $aElement2 = $playerTd2->find('a')[0] ?? null;
            [$club2Player1Name, $club2Player1BadmintonPlayerId] = $this->extractNameAndId($aElement2);
            if (!$isSinglePlayer) {
                $aElement3 = $playerTd2->find('a')[1] ?? null;
                [$club2Player2Name, $club2Player2BadmintonPlayerId] = $this->extractNameAndId($aElement3);
            } else {
                $club2Player2Name = null;
            }

            // Squad 1
            $categoryObj = new Category();
            $categoryObj->category = $category;
            $categoryObj->name = $categoryName;
            $squad1->categories[] = $categoryObj;

            $player1 = new Player();
            $player1->name = $club1Player1Name;
            $player1->badmintonPlayerId = $club1Player1BadmintonPlayerId;
            if ($club1Player2Name !== null) {
                $player2 = new Player();
                $player2->name = $club1Player2Name;
                $player2->badmintonPlayerId = $club1Player2BadmintonPlayerId;
                $categoryObj->players[] = $player2;
            }

            $categoryObj->players[] = $player1;

            // Squad 2
            $categoryObj = new Category();
            $categoryObj->category = $category;
            $categoryObj->name = $categoryName;
            $squad2->categories[] = $categoryObj;

            $player1 = new Player();
            $player1->name = $club2Player1Name;
            $player1->badmintonPlayerId = $club2Player1BadmintonPlayerId;
            if ($club2Player2Name !== null) {
                $player2 = new Player();
                $player2->name = $club2Player2Name;
                $player2->badmintonPlayerId = $club2Player2BadmintonPlayerId;
                $categoryObj->players[] = $player2;
            }

            $categoryObj->players[] = $player1;
        }

        $team1 = new Team($club1, $squad1);
        $team2 = new Team($club2, $squad2);

        return new TeamMatch($team1, $team2);
    }

    /**
     * @param  Element  $aElement
     *
     * @return array
     */
    private function extractNameAndId(?Element $aElement): array
    {
        if ($aElement === null) {
            return ['Ikke fremmødt', 0];
        }
        $href = $aElement->getAttribute('href');
        $badmintonPlayerId = Str::after($href, '#');
        return [$aElement->text(), (int)$badmintonPlayerId];
    }

    private function findCategoryByName(string $categoryName): string
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
            throw new \RuntimeException('Unknown category '.$category);
        }

        return $category;
    }

}
