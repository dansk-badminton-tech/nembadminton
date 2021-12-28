<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cancellation extends Model
{
    use HasFactory;

    protected $fillable = ['refId', 'teamId'];

    public function scopeCancellationsByTeamId(Builder $builder, array $args)
    {
        $teamId = $args['teamId'] ?? null;
        if($teamId !== null){
            return $builder->where('teamId', $teamId)->orWhereNull('teamId');
        }
        return $builder;
    }
}
