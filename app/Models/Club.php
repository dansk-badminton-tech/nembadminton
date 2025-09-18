<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int    id
 * @property string name1
 * @property boolean initialized
 * @property int badmintonPlayerId
 */
class Club extends Model
{

    protected $fillable = ['id', 'name1', 'name2', 'address', 'zipCode', 'city', 'email', 'memberOf', 'union', 'badmintonPlayerId'];

    protected $casts = [
        'initialized' => 'bool'
    ];

    public function members() : BelongsToMany
    {
        return $this->belongsToMany(Member::class);
    }

    public function users() : BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function clubhouses() : BelongsToMany
    {
        return $this->belongsToMany(Clubhouse::class, 'clubhouse_club');
    }

    public function leagueTeams(): HasMany
    {
        return $this->hasMany(LeagueTeam::class);
    }

    public function scopeOrderByName(Builder $builder) : Builder
    {
        return $builder->orderBy('name1');
    }

}
