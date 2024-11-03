<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CancellationDate extends Model
{
    use HasFactory;

    protected $fillable = [
        'cancellation_publics_id',
        'date',
    ];

    public function cancellationPublic()
    {
        return $this->belongsTo(CancellationPublic::class);
    }
}
