<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
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

    public function users() : BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function user() : HasOne
    {
        return $this->hasOne(User::class);
    }
}
