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
 * @property User    $user
 * @property boolean $publish
 * @property string  $message
 */
class Teams extends Model
{

    public    $incrementing = false;

    protected $fillable     = ['teams', 'name', 'game_date', 'version', 'round', 'user_id', 'clubhouse_id'];

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

    public function squads() : HasMany
    {
        return $this->hasMany(Squad::class)->orderBy('order');
    }

    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function clubhouse() : BelongsTo
    {
        return $this->belongsTo(Clubhouse::class);
    }

    public function activityLogs() : HasMany
    {
        return $this->hasMany(TeamActivityLog::class, 'team_id')->orderBy('created_at', 'desc');
    }

}
