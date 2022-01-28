<?php

declare(strict_types=1);


namespace FlyCompany\Scraper\Models;

use FlyCompany\Scraper\Enum\LeagueType;
use Illuminate\Support\Str;

class Team
{

    public ?string $name;

    public ?string $leagueGroupId;

    public ?string $ageGroupId;

    public ?string $leagueMatchId;

    public ?string $league;

    public ?Squad $squad;

    public ?TeamFight $teamFight;

    public function __construct(string $name, ?Squad $squad = null)
    {
        $this->name = $name;
        $this->squad = $squad;
    }

    public function getLeagueType() : LeagueType{
        if(Str::contains($this->league,'liga')){
            return LeagueType::LIGA;
        }
        if(Str::contains($this->league,'1. division')){
            return LeagueType::LIGA;
        }
        return LeagueType::OTHER;
    }

    public function isRegionSeriesOrAbove(): bool
    {
        $matches = [
            '/Grundspil/',
            '/1. division.*Pulje/',
            '/2. division.*Pulje/',
            '/3. division.*Pulje/',
            '/Danmarksserien.*Pulje/',
            '/KBH Serien P1 Pulje/',
            '/Kredsserie Vest Pulje/',
            '/Sjællandsserien Pulje/',
        ];
        foreach ($matches as $match){
            $stringable = Str::of($this->league)->match($match);
            if($stringable != ''){
                return true;
            }
        }
        return false;
    }

}
