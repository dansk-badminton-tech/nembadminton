<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Notification extends Model
{
    use HasFactory;

    public $incrementing = false;

    public function scopeMyNotifications(Builder $builder) : Builder
    {
        return $builder->where('notifiable_id', Auth::user()->getAuthIdentifier());
    }
}
