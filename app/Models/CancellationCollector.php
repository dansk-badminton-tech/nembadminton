<?php

namespace App\Models;

use App\Util\Util;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CancellationCollector extends Model
{
    use HasFactory;

    protected $fillable = ['email', 'user_id'];

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
}
