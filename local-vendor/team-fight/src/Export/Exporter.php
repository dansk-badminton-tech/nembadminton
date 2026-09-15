<?php

declare(strict_types=1);

namespace FlyCompany\TeamFight\Export;

use App\Models\TeamRound;
use Illuminate\Support\Arr;

class Exporter
{

    public function exportToCSV(TeamRound $team, bool $includeCategories = true) : string{
        $csv = [];
        foreach ($team->squads as $index => $squad){
            $i = $index + 1;
            $csv[] = '"'."Hold $i".'"';
            $seenPlayers = [];
            foreach ($squad->categories as $category){
                foreach ($category->players as $playerIndex => $player){
                    if (!$includeCategories) {
                        $identifier = (string)($player->member_ref_id ?: $player->name);
                        if ($identifier !== '') {
                            if (isset($seenPlayers[$identifier])) {
                                continue;
                            }
                            $seenPlayers[$identifier] = true;
                        }
                    }

                    $data = [];
                    if ($includeCategories) {
                        if ($playerIndex === 0) {
                            $data[] = '"'.$category->name.'"';
                        } else {
                            $data[] = "";
                        }
                    }
                    $data[] = '"'.$player->name.'"';
                    $csv[] = implode(',', $data);
                }
            }
            $csv[] = "";
        }
        return implode(PHP_EOL, $csv);
    }

}
