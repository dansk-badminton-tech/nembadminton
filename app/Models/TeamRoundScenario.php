<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $team_round_id
 * @property string $name
 * @property bool $is_official
 * @property TeamRound $teamRound
 * @property SquadCategory[] $categories
 */
class TeamRoundScenario extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_round_id',
        'name',
        'is_official',
    ];

    protected $casts = [
        'is_official' => 'boolean',
    ];

    public function teamRound(): BelongsTo
    {
        return $this->belongsTo(TeamRound::class, 'team_round_id');
    }

    public function categories(): HasMany
    {
        return $this->hasMany(SquadCategory::class, 'team_round_scenario_id');
    }
}
