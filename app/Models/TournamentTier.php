<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TournamentTier extends Model
{

    protected $fillable = [
        'tier_name',
    ];

    public function tournamentGroups(): HasMany
    {
        return $this->hasMany(TournamentGroup::class, 'tier_id', 'id');
    }
}
