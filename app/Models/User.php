<?php
declare(strict_types = 1);


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use NotificationChannels\WebPush\HasPushSubscriptions;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property string              player_id
 * @property SubscriptionSetting subscriptionSettings
 * @property club                club
 * @property int                 organization_id
 */
class User extends Authenticatable
{

    use HasApiTokens, Notifiable, HasPushSubscriptions, HasFactory, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'organization_id',
        'player_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function club() : BelongsTo
    {
        return $this->belongsTo(Club::class, 'organization_id', 'id');
    }

    public function clubs() : BelongsToMany
    {
        return $this->belongsToMany(Club::class);
    }

    public function clubhouses() : BelongsToMany
    {
        return $this->belongsToMany(Clubhouse::class);
    }

    public function subscriptionSettings() : HasOne
    {
        return $this->hasOne(SubscriptionSetting::class);
    }

    public function teams() : HasMany
    {
        return $this->hasMany(Teams::class);
    }

    public function cancellationCollector() : HasOne
    {
        return $this->hasOne(CancellationCollector::class);
    }

    public function clubhouse() : BelongsTo {
        return $this->belongsTo(Clubhouse::class, 'clubhouse_id', 'id');
    }

}
