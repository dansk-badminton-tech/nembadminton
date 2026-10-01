<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $name
 * @property TournamentTier|null $tier
 * @property string|null $custom_tier_name
 * @property string|null $group_name
 */
class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'tier_id',
        'custom_tier_name',
        'group_name',
        'clubhouse_id',
        'season_id',
    ];

    public function tier(): BelongsTo
    {
        return $this->belongsTo(TournamentTier::class, 'tier_id');
    }

    /**
     * The team's tier label: its tournament tier, or the custom tier name when it has none.
     */
    public function tierName(): ?string
    {
        return $this->tier->tier_name ?? $this->custom_tier_name;
    }

    public function clubhouse(): BelongsTo
    {
        return $this->belongsTo(Clubhouse::class);
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function squads(): HasMany
    {
        return $this->hasMany(Squad::class);
    }
}
