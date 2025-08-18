<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property int|null $club_id
 * @property int|null $team_number
 * @property int|null $external_team_id
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property Club|null $club
 */
class LeagueTeam extends Model
{
    protected $fillable = [
        'name',
        'club_id',
        'team_number',
        'external_team_id',
    ];

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
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
