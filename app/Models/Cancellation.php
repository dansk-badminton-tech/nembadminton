<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property Teams|null $team
 * @property CancellationCollector|null $cancellationCollector
 * @property Member $member
 * @property CancellationDate[]|Collection $dates
 * @property string $message
 * @property string $email
 */
class Cancellation extends Model
{
    use HasFactory;

    protected $fillable = ['refId', 'teamId', 'message', 'email'];

    public function dates() : HasMany
    {
        return $this->hasMany(CancellationDate::class);
    }

    public function member() : BelongsTo {
        return $this->belongsTo(Member::class, 'refId', 'refId');
    }

    public function team() : BelongsTo {
        return $this->belongsTo(Teams::class, 'teamId', 'id');
    }

    public function cancellationCollector() : BelongsTo{
        return $this->belongsTo(CancellationCollector::class);
    }

}
