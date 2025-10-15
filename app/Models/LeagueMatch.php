<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $external_match_id
 * @property int $division_id
 * @property int $age_group_id
 * @property int $team1_id
 * @property int $team2_id
 * @property int|null $venue_id
 * @property int $season_id
 * @property int|null $clubhouse_id
 * @property bool $created_by_system
 * @property \Illuminate\Support\Carbon|null $match_time
 * @property int|null $score1
 * @property int|null $score2
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property Division $division
 * @property AgeGroup $ageGroup
 * @property LeagueTeam $team1
 * @property LeagueTeam $team2
 * @property Venue $venue
 * @property Season $season
 * @property Clubhouse|null $clubhouse
 */
class LeagueMatch extends Model
{
    protected $fillable = [
        'external_match_id',
        'division_id',
        'age_group_id',
        'team1_id',
        'team2_id',
        'venue_id',
        'season_id',
        'clubhouse_id',
        'created_by_system',
        'match_time',
        'score1',
        'score2',
    ];

    protected $casts = [
        'match_time' => 'datetime',
        'created_by_system' => 'boolean',
    ];

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function ageGroup(): BelongsTo
    {
        return $this->belongsTo(AgeGroup::class);
    }

    public function team1(): BelongsTo
    {
        return $this->belongsTo(LeagueTeam::class, 'team1_id');
    }

    public function team2(): BelongsTo
    {
        return $this->belongsTo(LeagueTeam::class, 'team2_id');
    }

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function clubhouse(): BelongsTo
    {
        return $this->belongsTo(Clubhouse::class);
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('match_time', '>', now())
            ->whereNotNull('match_time');
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->whereNotNull('score1')
            ->whereNotNull('score2');
    }

    public function scopeForTeam(Builder $query, int $teamId): Builder
    {
        return $query->where('team1_id', $teamId)
            ->orWhere('team2_id', $teamId);
    }

    public function scopeScheduled(Builder $query): Builder
    {
        return $query->whereNotNull('match_time');
    }

    public function scopeUnscheduled(Builder $query): Builder
    {
        return $query->whereNull('match_time');
    }
}
