<?php
declare(strict_types = 1);


namespace FlyCompany\Scraper\Models;

use Illuminate\Support\Str;

class Team
{

    public ?string $name;

    public ?string $leagueGroupId;

    public ?string $ageGroupId;

    public ?string $leagueMatchId;

    public ?string $league;

    public ?Squad  $squad;

    public function __construct(string $name, ?Squad $squad = null)
    {
        $this->name = $name;
        $this->squad = $squad;
    }

    public function isDenmarkSeriesOrAbove() : bool {
        return Str::is([
            'Grundspil',
            '1. division Pulje 1',
            '1. division Pulje 2',
            '1. division Pulje 3',
            '1. division Pulje 4',
            '2. division Pulje 1',
            '2. division Pulje 2',
            '2. division Pulje 3',
            '2. division Pulje 4',
            '3. division Pulje 1',
            '3. division Pulje 2',
            '3. division Pulje 3',
            '3. division Pulje 4',
            '3. division Pulje 5',
            '3. division Pulje 6',
            'Danmarksserien Pulje 1',
            'Danmarksserien Pulje 2',
            'Danmarksserien Pulje 3',
            'Danmarksserien Pulje 4',
            'Danmarksserien Pulje 5',
            'Danmarksserien Pulje 6',
            'Danmarksserien Pulje 7',
            'Danmarksserien Pulje 8',
            'KBH Serien P1 Pulje 1',
            'KBH Serien P1 Pulje 2',
            'KBH Serien P2 Pulje 1',
            'KBH Serien P2 Pulje 2'
        ],$this->league);
    }

}
