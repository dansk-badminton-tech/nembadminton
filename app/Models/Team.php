<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'tier_id',
        'custom_tier_name',
        'group_name',
        'clubhouse_id',
    ];

    public function tier(): BelongsTo
    {
        return $this->belongsTo(TournamentTier::class, 'tier_id');
    }

    public function clubhouse(): BelongsTo
    {
        return $this->belongsTo(Clubhouse::class);
    }

    public function squads(): HasMany
    {
        return $this->hasMany(Squad::class);
    }
}
