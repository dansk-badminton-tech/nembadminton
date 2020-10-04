<?php
declare(strict_types = 1);


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Member
 *
 * @package App\Models
 */
class Member extends Model
{

    protected $fillable = ['refId', 'name', 'gender', 'birthday'];

    public function clubs() : BelongsToMany
    {
        return $this->belongsToMany(Club::class);
    }

    public function points() : HasMany
    {
        return $this->hasMany(Point::class);
    }

}
