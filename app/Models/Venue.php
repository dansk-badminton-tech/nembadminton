<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string|null $address
 * @property string|null $city
 * @property string|null $zip_code
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Venue extends Model
{
    protected $fillable = [
        'name',
        'address',
        'city',
        'zip_code',
    ];

    public function leagueMatches(): HasMany
    {
        return $this->hasMany(LeagueMatch::class);
    }
}
