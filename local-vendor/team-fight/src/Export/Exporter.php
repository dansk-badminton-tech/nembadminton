<?php

declare(strict_types=1);

namespace FlyCompany\TeamFight\Export;

use App\Models\Teams;
use Illuminate\Support\Arr;

class Exporter
{

    public function exportToCSV(Teams $team) : string{
        $csv = [];
        foreach ($team->squads as $index => $squad){
            $i = $index + 1;
            $csv[] = "Hold $i";
            foreach ($squad->categories as $category){
                foreach ($category->players as $playerIndex => $player){
                    $data = [];
                    if($playerIndex === 0){
                        $data[] = $category->name;
                    }else{
                        $data[] = "";
                    }
                    $data[] = $player->name;
                    $csv[] = implode(',', $data);
                }
            }
            $csv[] = "";
        }
        return implode(PHP_EOL, $csv);
    }

}
