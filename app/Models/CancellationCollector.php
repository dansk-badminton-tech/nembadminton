<?php

namespace App\Models;

use App\Util\Util;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

/**
 * @property string $sharing_id
 * @property int $user_id
 * @property string $email
 * @property Cancellation[] $cancellations
 * @property int $clubhouse_id
 */
class CancellationCollector extends Model
{
    use HasFactory;

    protected $fillable = ['email', 'user_id', 'clubhouse_id'];

    protected static function booted()
    {
        static::creating(function (CancellationCollector $cancellationCollector) {
            $cancellationCollector->sharing_id = Util::generateRandomString(24);
        });
    }

    public function scopeCurrentUser(Builder $query) : Builder
    {
        return $query->where('user_id', Auth::user()->id);
    }

    public function clubs() : BelongsToMany
    {
        return $this->belongsToMany(Club::class, 'cancellation_collector_clubs', 'cancellation_collector_id', 'club_id');
    }

    public function cancellations() : HasMany
    {
        return $this->hasMany(Cancellation::class);
    }

    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function clubhouse() : BelongsTo
    {
        return $this->belongsTo(Clubhouse::class);
    }
}
