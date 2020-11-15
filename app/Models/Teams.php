<?php
declare(strict_types = 1);


namespace App\Models;

use App\Util\Util;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Teams extends Model
{

    public    $incrementing = false;

    protected $fillable     = ['teams', 'name', 'game_date', 'user_id'];

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

}
