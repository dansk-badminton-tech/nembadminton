<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamReceivers extends Model
{

    protected $fillable = ['team_id','emails'];

    public function teams(): BelongsTo
    {
        return $this->belongsTo(Teams::class);
    }

    protected function casts(): array
    {
        return [
            'emails' => 'json',
        ];
    }
}
