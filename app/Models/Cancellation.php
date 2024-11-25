<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cancellation extends Model
{
    use HasFactory;

    protected $fillable = ['refId', 'teamId', 'message'];


    public function dates() : HasMany
    {
        return $this->hasMany(CancellationDate::class);
    }

    public function member() : BelongsTo {
        return $this->belongsTo(Member::class, 'refId', 'refId');
    }

    public function cancellationCollector() : BelongsTo{
        return $this->belongsTo(CancellationCollector::class);
    }
}
