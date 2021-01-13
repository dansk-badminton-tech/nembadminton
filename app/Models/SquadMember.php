<?php
declare(strict_types = 1);


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class SquadMember
 *
 * @package App\Models
 */
class SquadMember extends Model
{

    use HasFactory;

    protected $fillable = ['member_ref_id', 'squad_category_id', 'name', 'gender'];

    public function points() : HasMany
    {
        return $this->hasMany(SquadPoint::class);
    }
}
