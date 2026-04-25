<?php

namespace App\Models;

use App\Util\Util;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Auth;

class TeamRound extends Model
{
    use HasFactory;

    public    $incrementing = false;

    protected $fillable     = ['teams', 'name', 'game_date', 'version', 'round', 'user_id', 'clubhouse_id'];

    protected static function booted(): void
    {
        static::creating(static function (TeamRound $teamRound) {
            $teamRound->id = Util::generateRandomString(24);
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

    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function clubhouse() : BelongsTo
    {
        return $this->belongsTo(Clubhouse::class);
    }

    public function receiver() : HasOne
    {
        return $this->hasOne(TeamReceivers::class, 'team_id');
    }

    public function activityLogs() : HasMany
    {
        return $this->hasMany(TeamActivityLog::class, 'team_id')->orderBy('created_at', 'desc');
    }

    public function squads() : HasMany
    {
        return $this->hasMany(Squad::class, 'teams_id', 'id')->orderBy('order');
    }
}
