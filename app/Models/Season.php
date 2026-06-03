<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Season extends Model
{
    public $incrementing = false;

    protected $fillable = [
        'id',
        'season_name',
    ];

    public function tournamentGroups(): HasMany
    {
        return $this->hasMany(TournamentGroup::class, 'season_id', 'id');
    }

    public function teams(): HasMany
    {
        return $this->hasMany(Team::class, 'season_id', 'id');
    }

    public function teamRounds(): HasMany
    {
        return $this->hasMany(TeamRound::class, 'season_id', 'id');
    }
}
