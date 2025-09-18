<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property int|null $club_id
 * @property int|null $division_id
 * @property int|null $clubhouse_id
 * @property bool $created_by_system
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property Club|null $club
 * @property Division|null $division
 * @property Clubhouse|null $clubhouse
 */
class LeagueTeam extends Model
{

    protected static function booted()
    {
        static::creating(function ($leagueTeam) {
            if ($leagueTeam->created_by_system === null) {
                $leagueTeam->created_by_system = false;
            }
        });
    }

    protected $fillable = [
        'name',
        'club_id',
        'division_id',
        'clubhouse_id',
        'created_by_system',
    ];

    protected $casts = [
        'created_by_system' => 'boolean',
    ];

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function clubhouse(): BelongsTo
    {
        return $this->belongsTo(Clubhouse::class);
    }

    public function homeMatches(): HasMany
    {
        return $this->hasMany(LeagueMatch::class, 'team1_id');
    }

    public function awayMatches(): HasMany
    {
        return $this->hasMany(LeagueMatch::class, 'team2_id');
    }

    public function allMatches()
    {
        return LeagueMatch::where('team1_id', $this->id)
            ->orWhere('team2_id', $this->id);
    }
}
