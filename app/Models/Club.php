<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int    id
 * @property string name1
 */
class Club extends Model
{
    protected $fillable = ['id','name1','name2','address','zipCode','city','email','memberOf','union'];

    public function members() : BelongsToMany{
        return $this->belongsToMany(Member::class);
    }

}
