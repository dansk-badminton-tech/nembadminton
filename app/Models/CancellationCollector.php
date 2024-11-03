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
 */
class CancellationCollector extends Model
{
    use HasFactory;

    protected $fillable = ['email', 'user_id', 'club_id'];

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

    public function cancellationPublic() : HasMany
    {
        return $this->hasMany(CancellationPublic::class);
    }
}
