<?php
declare(strict_types = 1);


namespace App\Models;

use App\Events\UserUpdate;
use Carbon\Carbon;
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
 * @property string              $player_id
 * @property SubscriptionSetting $subscriptionSettings
 * @property Club                $club
 * @property int                 $clubhouse_id
 * @property string              $name
 * @property string              $email
 * @property Clubhouse           $clubhouse
 * @property Carbon              $created_at
 */
class User extends Authenticatable
{

    use HasApiTokens, Notifiable, HasPushSubscriptions, HasFactory, HasRoles;

    protected $dispatchesEvents = [
        'updated' => UserUpdate::class
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
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

    /**
     * Get the clubhouse that the user belongs to. This is the primary clubhouse for the user
     * @return BelongsTo
     */
    public function clubhouse() : BelongsTo
    {
        return $this->belongsTo(Clubhouse::class);
    }

}
