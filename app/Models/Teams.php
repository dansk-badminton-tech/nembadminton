<?php
declare(strict_types = 1);


namespace App\Models;

use App\Util\Util;
use Illuminate\Database\Eloquent\Model;

class Teams extends Model
{

    public    $incrementing = false;

    protected $fillable     = ['teams', 'name'];

    protected static function booted()
    {
        static::creating(function (Teams $teams) {
            $teams->id = Util::generateRandomString(24);
        });
    }

}
