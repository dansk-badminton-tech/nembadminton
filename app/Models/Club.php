<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int id
 */
class Club extends Model
{
    protected $fillable = ['id','name1','name2','address','zipCode','city','email','memberOf','union'];

    public function members() : BelongsToMany{
        return $this->belongsToMany(Member::class);
    }

}
