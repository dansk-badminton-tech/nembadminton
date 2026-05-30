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
}
