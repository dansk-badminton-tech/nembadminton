<?php
declare(strict_types = 1);


namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property SquadMember $member
 * @property string      $category
 * @property integer     $points
 * @property integer     $position
 * @property integer     $squad_member_id
 * @property string      $vintage
 * @property boolean     $corrected_manually
 * @property Carbon      $version
 */
class SquadPoint extends Model
{

    protected $fillable = ['points', 'position', 'category', 'squad_member_id', 'vintage', 'corrected_manually', 'version'];

    protected $casts    = [
        'version' => 'datetime:Y-m-d',
    ];

    public function member() : BelongsTo
    {
        return $this->belongsTo(SquadMember::class, 'squad_member_id');
    }

    public function usesTimestamps() : bool
    {
        return false;
    }

}
