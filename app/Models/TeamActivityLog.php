<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ActivityAction;
use App\Enums\RecipientType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int     $id
 * @property string  $team_id
 * @property string  $action
 * @property string  $recipient_type
 * @property int     $recipient_count
 * @property string  $recipients_summary
 * @property string  $message
 * @property array   $metadata
 * @property int     $user_id
 * @property User    $user
 * @property Teams   $team
 */
class TeamActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'action',
        'recipient_type',
        'recipient_count',
        'recipients_summary',
        'message',
        'metadata',
        'user_id',
    ];

    protected $casts = [
        'action' => ActivityAction::class,
        'metadata' => 'array',
        'recipient_count' => 'integer',
    ];

    /**
     * Get the team that owns the activity log.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Teams::class, 'team_id');
    }

    /**
     * Get the user who performed the action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to order by most recent first.
     */
    public function scopeLatest(Builder $query): Builder
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Scope to filter by action type.
     */
    public function scopeByAction(Builder $query, ActivityAction $action): Builder
    {
        return $query->where('action', $action->value);
    }

    /**
     * Create a log entry for a notification sent.
     */
    public static function logNotificationSent(
        string $teamId,
        RecipientType $recipientType,
        int $recipientCount,
        string $recipientsSummary,
        ?string $message = null,
        ?array $metadata = null,
        ?int $userId = null
    ): self {
        return self::create([
            'team_id' => $teamId,
            'action' => ActivityAction::TEAM_PUBLISH,
            'recipient_type' => $recipientType,
            'recipient_count' => $recipientCount,
            'recipients_summary' => $recipientsSummary,
            'message' => $message,
            'metadata' => $metadata,
            'user_id' => $userId ?? auth()->id(),
        ]);
    }

    public function getRawMetadataAttribute(): string
    {
        return $this->attributes['metadata'];
    }

    /**
     * Create a log entry for a test email sent.
     */
    public static function logTestEmailSent(
        string $teamId,
        RecipientType $recipientType,
        ?string $message = null,
        ?array $metadata = null,
        ?int $userId = null
    ): self {
        return self::create([
            'team_id' => $teamId,
            'action' => ActivityAction::TEST_EMAIL_SENT,
            'recipient_type' => $recipientType,
            'recipient_count' => 1,
            'recipients_summary' => 'Test email to self',
            'message' => $message,
            'metadata' => $metadata,
            'user_id' => $userId ?? auth()->id(),
        ]);
    }
}
