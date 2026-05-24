<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TournamentGroup extends Model
{
    protected $fillable = [
        'tier_id',
        'season_id',
        'group_name',
        'phase_type',
    ];

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class, 'season_id', 'id');
    }

    public function tournamentTier(): BelongsTo
    {
        return $this->belongsTo(TournamentTier::class, 'tier_id', 'id');
    }
}
