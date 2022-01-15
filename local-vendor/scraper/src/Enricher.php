<?php
declare(strict_types = 1);


namespace FlyCompany\Scraper;

use Carbon\Carbon;
use FlyCompany\Scraper\Exception\MultiplePlayersFoundException;
use FlyCompany\Scraper\Models\Team;

class Enricher
{

    private BadmintonPlayer $scraper;

    public function __construct(BadmintonPlayer $scraper)
    {
        $this->scraper = $scraper;
    }

    /**
     * Enrich the players on the team with points
     *
     * @param  Team  $team
     * @param  int  $clubId
     * @param  int  $season
     * @param  Carbon  $version
     * @return Team
     * @throws \DiDom\Exceptions\InvalidSelectorException
     * @throws \JsonException
     */
    public function teamMatch(Team $team, int $clubId, int $season, Carbon $version) : Team{

        $playersWithPoints = [];
        foreach ($this->resolveClubs($clubId) as $currentClubId) {
            $rankingLists = $this->scraper->getAllRankingListPlayers($season, (string)$currentClubId, $version);
            $playersWithPoints += BadmintonPlayerHelper::collapseRankingLists($rankingLists);
        }

        foreach ($team->squad->categories as $category) {
            foreach ($category->players as &$player) {
                if (isset($playersWithPoints[$player->name])) {
                    $player = $playersWithPoints[$player->name];
                } else {
                    try {
                        if (!$player->isNoBody()) {
                            $player = $this->scraper->getPlayerByBadmintonPlayerId(
                                $player->badmintonPlayerId,
                                $version,
                                $season
                            );
                        } else {
                            $player = null;
                        }
                    } catch (MultiplePlayersFoundException) {
                        throw new \RuntimeException("Multiple players named $player->name");
                    }
                }
            }
            unset($player);
            $category->players = array_filter($category->players, static function ($player) {
                return $player !== null;
            });
        }

        return $team;
    }

    private function resolveClubs(int $clubId) : array
    {
        $clubIds = [$clubId];
        if ($clubId < 0) {
            switch ($clubId) {
                case -473: // RSL ODENSE OBK
                    $clubIds = [1392];
                    break;
                case -2: // Team Skælskør-Slagelse
                    $clubIds = [327, 1157];
                    break;
                case -439: // Højbjerg/Via Biler
                    $clubIds = [25];
                    break;
                case -3: // Vendsyssel
                    $clubIds = [1494, 1500, 1497];
                    break;
            }
        }

        return $clubIds;
    }


}
