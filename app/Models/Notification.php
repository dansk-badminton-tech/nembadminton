<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;

class Notification extends DatabaseNotification
{
    use HasFactory;

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function scopeMyNotifications(Builder $builder) : Builder
    {
        return $builder->where('notifiable_id', Auth::user()->getAuthIdentifier());
    }
}
