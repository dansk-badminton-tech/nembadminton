<?php
declare(strict_types = 1);


namespace App\Models;

use App\Util\Util;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

/**
 * @property string  name
 * @property string  id
 * @property Carbon  version
 * @property Squad[] squads
 * @property integer $user_id
 */
class Teams extends Model
{

    public    $incrementing = false;

    protected $fillable     = ['teams', 'name', 'game_date', 'version', 'user_id'];

    protected static function booted()
    {
        static::creating(function (Teams $teams) {
            $teams->id = Util::generateRandomString(24);
        });
    }

    /**
     * Scope a query to only include popular users.
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeCurrentUser(Builder $query) : Builder
    {
        return $query->where('user_id', Auth::user()->id);
    }

    public function club() : BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    public function squads() : hasMany
    {
        return $this->hasMany(Squad::class)->orderBy('order');
    }

}
