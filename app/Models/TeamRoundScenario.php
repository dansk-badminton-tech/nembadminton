<?php

declare(strict_types=1);

namespace App\Models;

use App\Util\Util;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 * @property string $team_round_id
 * @property string $name
 * @property bool $is_official
 * @property TeamRound $teamRound
 * @property SquadCategory[] $categories
 */
class TeamRoundScenario extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'team_round_id',
        'name',
        'is_official',
    ];

    protected $casts = [
        'is_official' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(static function (TeamRoundScenario $scenario) {
            if (empty($scenario->id)) {
                $scenario->id = Util::generateRandomString(24);
            }
        });
    }

    public function teamRound(): BelongsTo
    {
        return $this->belongsTo(TeamRound::class, 'team_round_id');
    }

    public function categories(): HasMany
    {
        return $this->hasMany(SquadCategory::class, 'team_round_scenario_id');
    }
}
