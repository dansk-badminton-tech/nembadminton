<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property integer id
 * @property string name
 * @property string email
 */
class Clubhouse extends Model
{

    protected $fillable = ['name', 'email'];

    public function clubs() : BelongsToMany
    {
        return $this->belongsToMany(Club::class, 'clubhouse_club');
    }

    public function users() : HasMany
    {
        return $this->hasMany(User::class);
    }

    public function user() : HasOne
    {
        return $this->hasOne(User::class);
    }

    public function invitations() : HasMany
    {
        return $this->hasMany(Invitation::class);
    }
}
