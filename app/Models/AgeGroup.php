<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string $code
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class AgeGroup extends Model
{
    protected $fillable = [
        'name',
        'code',
    ];

    public function leagueMatches(): HasMany
    {
        return $this->hasMany(LeagueMatch::class);
    }
}
