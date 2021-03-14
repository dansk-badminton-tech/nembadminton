<?php
declare(strict_types = 1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Point
 *
 * @property int|null points
 * @property int|null position
 * @property string   category
 * @property string   cll
 * @property string   clh
 * @property string   vintage
 * @property int      member_id
 * @property string   version
 * @property Member   member
 * @package App\Models
 */
class Point extends Model
{

    protected $fillable = ['points', 'position', 'category', 'cll', 'clh', 'vintage', 'member_id', 'version'];

    public function member() : BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}
