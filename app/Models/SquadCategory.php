<?php
declare(strict_types = 1);


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use phpseclib\Crypt\Hash;

class SquadCategory extends Model
{

    use HasFactory;

    protected $fillable = ['category', 'name'];

    public function players() : HasMany
    {
        return $this->hasMany(SquadMember::class);
    }
}
