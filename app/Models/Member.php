<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Member extends Model
{
    protected $fillable = ['refId','name','gender','birthday'];

    public function clubs() : BelongsToMany{
        return $this->belongsToMany(Club::class);
    }

}
