<?php
declare(strict_types = 1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Point extends Model
{

    protected $fillable = ['points', 'position', 'category', 'cll', 'clh', 'vintage', 'member_id'];

    public function member() : BelongsTo
    {
        $this->belongsTo(Member::class);
    }
}
