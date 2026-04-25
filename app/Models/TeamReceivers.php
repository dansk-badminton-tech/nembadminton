<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
/**
 * TeamReceivers model representing the relationship between teams and their receivers.
 *
 * This model handles the association of email addresses to a team and casts
 * the `emails` attribute to JSON for database storage. It establishes a
 * belongsTo relationship with the Teams model.
 *
 * @property int $id The primary key of the TeamReceivers table.
 * @property string $team_round_id The ID of the associated team round.
 * @property array $emails A JSON-encoded array of email addresses.
 *
 */
class TeamReceivers extends Model
{

    protected $fillable = ['team_round_id','emails'];

    public function team(): BelongsTo
    {
        return $this->belongsTo(TeamRound::class, 'team_round_id');
    }

    public function teams(): BelongsTo
    {
        return $this->team();
    }

    protected function casts(): array
    {
        return [
            'emails' => 'json',
        ];
    }
}
