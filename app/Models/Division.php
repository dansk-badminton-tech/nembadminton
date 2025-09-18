<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string $code
 * @property int $display_order
 * @property int|null $clubhouse_id
 * @property bool $created_by_system
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Division extends Model
{
    protected $fillable = [
        'name',
        'code',
        'display_order',
        'clubhouse_id',
        'created_by_system',
    ];

    protected $casts = [
        'created_by_system' => 'boolean',
    ];

    public function clubhouse(): BelongsTo
    {
        return $this->belongsTo(Clubhouse::class);
    }

    public function leagueTeams(): HasMany
    {
        return $this->hasMany(LeagueTeam::class);
    }

    public function leagueMatches(): HasMany
    {
        return $this->hasMany(LeagueMatch::class);
    }

    public function scopeOrderByDisplayOrder(Builder $query): Builder
    {
        return $query->orderBy('display_order');
    }

    public function scopeSystemDivisions(Builder $query): Builder
    {
        return $query->where('created_by_system', true);
    }

    public function scopeClubhouseDivisions(Builder $query, ?int $clubhouseId = null): Builder
    {
        $query = $query->where('created_by_system', false);
        
        if ($clubhouseId) {
            $query->where('clubhouse_id', $clubhouseId);
        }
        
        return $query;
    }
}
