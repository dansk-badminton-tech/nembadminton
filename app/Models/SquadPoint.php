<?php
declare(strict_types = 1);


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property SquadMember member
 * @property string      category
 * @property integer     points
 * @property integer     position
 */
class SquadPoint extends Model
{

    protected $fillable = ['points', 'position', 'category', 'squad_member_id'];

    public function member() : BelongsTo
    {
        return $this->belongsTo(SquadMember::class, 'squad_member_id');
    }

    public function usesTimestamps() : bool
    {
        return false;
    }

}
