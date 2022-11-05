<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int    id
 * @property string name1
 * @property boolean initialized
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

    public function scopeOrderByName(Builder $builder) : Builder
    {
        return $builder->orderBy('name1');
    }

}
