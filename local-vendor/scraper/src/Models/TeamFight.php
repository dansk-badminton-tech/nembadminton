<?php

declare(strict_types=1);

namespace FlyCompany\Scraper\Models;

use Carbon\Carbon;

class TeamFight
{

    public array $teams = [];

    public ?string $matchId;

    public ?string $gameTime;

    public ?string $round;

    public ?string $roundDate;

    /**
     * @return Carbon
     */
    public function getGameTime(): Carbon
    {
        return Carbon::createFromFormat('d-m-Y H:i',$this->gameTime);
    }

}
