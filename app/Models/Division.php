<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string $code
 * @property int $display_order
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Division extends Model
{
    protected $fillable = [
        'name',
        'code',
        'display_order',
    ];

    public function groups(): HasMany
    {
        return $this->hasMany(Group::class);
    }

    public function leagueMatches(): HasMany
    {
        return $this->hasMany(LeagueMatch::class);
    }

    public function scopeOrderByDisplayOrder(Builder $query): Builder
    {
        return $query->orderBy('display_order');
    }
}
