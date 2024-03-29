<?php
declare(strict_types = 1);


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use phpseclib\Crypt\Hash;

/**
 * @property int   id
 * @property Squad $squad
 * @property SquadMember[] $players
 */
class SquadCategory extends Model
{

    use HasFactory;

    protected $fillable = ['category', 'name'];

    public function players() : HasMany
    {
        return $this->hasMany(SquadMember::class);
    }

    public function squad() : BelongsTo
    {
        return $this->belongsTo(Squad::class);
    }
}
