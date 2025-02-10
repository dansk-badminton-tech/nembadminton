<?php


namespace App\Models;

use App\Util\Util;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Represents an invitation entity within the application.
 *
 * @property int $id The unique identifier for the invitation.
 * @property int $clubhouse_id The ID of the clubhouse this invitation is associated with.
 * @property int $invited_by The ID of the user who sent the invitation.
 * @property int|null    $invitee_user_id The ID of the invited user, if already registered in the system.
 * @property string|null $invitee_email The email address of the invitee (if not yet registered).
 * @property string      $role The role assigned to the invitee within the clubhouse.
 * @property string      $token The unique token for this invitation.
 * @property string      $status The status of the invitation (e.g., 'pending', 'accepted', 'declined').
 * @property Carbon|null $expires_at The datetime when the invitation expires.
 * @property Carbon|null $accepted_at The datetime when the invitation was accepted.
 * @property Carbon|null $created_at The datetime when the invitation was created.
 * @property Carbon|null $updated_at The datetime when the invitation was last updated.
 *
 * @property Clubhouse   $clubhouse The clubhouse (tenant) associated with this invitation.
 * @property User        $inviter The user who sent the invitation.
 * @property User|null   $invitee The user who was invited, if registered in the system.
 */

class Invitation extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'invitations';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'clubhouse_id',
        'invited_by',
        'invitee_user_id',
        'invitee_email',
        'role',
        'expires_at',
        'accepted_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'expires_at'  => 'datetime',
        'accepted_at' => 'datetime'
    ];

    protected static function booted()
    {
        static::creating(function (Invitation $invitation) {
            $invitation->token = Util::generateRandomString(50);
            $invitation->status = 'pending';
            $invitation->expires_at = Carbon::now()->addDays(14);
        });
    }

    /**
     * Get the clubhouse (tenant) associated with this invitation.
     */
    public function clubhouse() : BelongsTo
    {
        return $this->belongsTo(Clubhouse::class);
    }

    /**
     * Get the user who sent the invitation.
     */
    public function inviter() : BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    /**
     * Get the invitee if already registered in the system.
     */
    public function invitee() : BelongsTo
    {
        return $this->belongsTo(User::class, 'invitee_user_id');
    }
}
