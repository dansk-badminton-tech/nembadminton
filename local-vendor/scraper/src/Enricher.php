<?php
declare(strict_types = 1);


namespace FlyCompany\Scraper;

use Carbon\Carbon;
use FlyCompany\BadmintonPlayerAPI\Util;
use FlyCompany\Scraper\Exception\MultiplePlayersFoundException;
use FlyCompany\Scraper\Models\Player;
use FlyCompany\Scraper\Models\Point;
use FlyCompany\Scraper\Models\Team;
use Illuminate\Support\Arr;

class Enricher
{

    private BadmintonPlayer $scraper;

    public function __construct(BadmintonPlayer $scraper)
    {
        $this->scraper = $scraper;
    }

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
            $category->players = array_map(static function(Player $player) use ($season) {
                $vintage = $player->calculateVintage(BadmintonPlayerHelper::makeSeasonStart($season));
                $hasLevelPoint = Arr::first($player->points, static fn(Point $point) => $point->category === null) !== null;
                if(!$hasLevelPoint && Util::isYoungPlayer($vintage)){
                    $point = new Point(0, 0, $vintage->value);
                    $point->setCategory(null);
                    $player->points[] = $point;
                }
                return $player;
            }, $category->players);
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
