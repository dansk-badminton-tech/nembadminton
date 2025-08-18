<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $year
 * @property string $name
 * @property string|null $start_date
 * @property string|null $end_date
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Season extends Model
{
    protected $fillable = [
        'year',
        'name',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function groups(): HasMany
    {
        return $this->hasMany(Group::class);
    }

    public function leagueMatches(): HasMany
    {
        return $this->hasMany(LeagueMatch::class);
    }
}
