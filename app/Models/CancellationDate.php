<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CancellationDate extends Model
{
    use HasFactory;

    protected $fillable = [
        'cancellation_id',
        'date',
    ];

    public function cancellation() : BelongsTo
    {
        return $this->belongsTo(Cancellation::class);
    }
}
