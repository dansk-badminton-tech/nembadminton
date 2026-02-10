<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class TeamRound extends Teams
{
    protected $table = 'teams';

    public function squads() : HasMany
    {
        return $this->hasMany(Squad::class, 'teams_id', 'id')->orderBy('order');
    }
}
