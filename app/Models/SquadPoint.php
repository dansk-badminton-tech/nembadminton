<?php
declare(strict_types = 1);


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SquadPoint extends Model
{

    protected $fillable = ['points', 'position', 'category', 'squad_member_id'];

    public function member() : BelongsTo
    {
        $this->belongsTo(SquadMember::class);
    }

    public function usesTimestamps() : bool
    {
        return false;
    }

}
