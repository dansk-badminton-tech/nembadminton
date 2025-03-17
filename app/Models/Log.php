<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class Log extends Model
{

    use HasFactory;

    protected $fillable = ['log', 'component', 'club_id'];

    public function club() : BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    public function scopeOrderByCreated(Builder $builder) : Builder
    {
        return $builder->orderBy('created_at', 'desc');
    }

    public function scopeClubhouse(Builder $builder, int $clubhouseId)
    {
        return $builder->whereHas('club.clubhouses', function (Builder $builder) use ($clubhouseId) {
            $builder->where('id', $clubhouseId);
        });
    }
}
