<?php


namespace FlyCompany\Scraper;


use DiDom\Document;
use FlyCompany\Scraper\Exception\NoPlayersException;
use FlyCompany\Scraper\Models\Point;
use FlyCompany\Scraper\Models\Team;
use FlyCompany\Scraper\Models\TeamMatch;
use FlyCompany\TeamFight\Models\Category;
use FlyCompany\TeamFight\Models\Player;
use FlyCompany\TeamFight\Models\Squad;
use Illuminate\Support\Str;

class Parser
{

    /**
     * @param string $html
     * @return Point[]
     * @throws \DiDom\Exceptions\InvalidSelectorException
     */
    public function rankingListPlayers(string $html): array
    {
        $document = new Document($html);
        $trs = $document->find('table.RankingListGrid tr');

        // Remove top of table
        array_shift($trs);

        $pointsCollection = [];
        if(count($trs) < 3){
            throw new NoPlayersException('No players');
        }
        foreach ($trs as $match) {

            // Will continue if name is not set
            if($aTagName = $match->find('td.name a')[0] ?? false){
                $name = $aTagName->text();
            }else{
                continue;
            }

            $points = $match->find('td.points')[0]->text();
            $position = preg_replace("/[^0-9]/", "",$match->find('td')[1]->text());
            preg_match_all("/(SEN|U09|U11|U13|U15|U17|U19|U23)/", $match->find('td.clas')[0]->text(), $matches);
            $vintage = $matches[1][0];
            $pointsCollection[] = new Point($name, $points, $position, $vintage);
        }
        return $pointsCollection;
    }

    /**
     * @param string $html
     * @return TeamMatch
     * @throws \DiDom\Exceptions\InvalidSelectorException
     */
    public function teamMatch(string $html): TeamMatch
    {
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
            throw new \RuntimeException('Unknow category ' . $category);
        }

        return $category;
    }

}
