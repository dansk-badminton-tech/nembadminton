<?php
declare(strict_types = 1);


namespace App\Models;

use Carbon\Carbon;
use FlyCompany\BadmintonPlayerAPI\Util;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

/**
 * Class SquadMember
 *
 * @property String        member_ref_id
 * @property int           id
 * @property SquadPoint[]  points
 * @property SquadCategory $category
 * @package App\Models
 */
class SquadMember extends Model
{

    use HasFactory;

    protected $fillable = ['member_ref_id', 'squad_category_id', 'name', 'gender'];

    public function points() : HasMany
    {
        return $this->hasMany(SquadPoint::class, 'squad_member_id');
    }

    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class, 'member_ref_id', 'player_id');
    }

    public function category() : BelongsTo
    {
        return $this->belongsTo(SquadCategory::class, 'squad_category_id');
    }

    public function getIsInSquad() : bool
    {
        return true;
    }

    public function getVintage() : string
    {
        return Util::calculateVintage($this->getBirthday())->value;
    }

    public function getBirthday() : Carbon
    {
        return Carbon::createFromFormat('ymd', substr($this->member_ref_id, 0, 6));
    }

}
