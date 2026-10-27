<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MatchKind;
use App\Exceptions\PaperSquadWithoutTeamException;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

/**
 * @property int $id
 * @property Collection|SquadCategory[] $categories
 * @property TeamRound $teamRound
 * @property string $team_round_id
 * @property int $order
 * @property int|null $external_team_fight_id
 * @property Carbon|null $playing_datetime
 * @property string|null $playing_place
 * @property string|null $playing_address
 * @property string|null $playing_zip_code
 * @property string|null $playing_city
 * @property Carbon|null $version
 * @property string|null $tier
 * @property int|null $team_id
 * @property bool $is_paper_squad
 * @property MatchKind|null $match_kind
 * @property Team|null $team
 */
class Squad extends Model implements Sortable
{
    use HasFactory;
    use SortableTrait;

    public array $sortable = [
        'order_column_name' => 'order',
        'sort_when_creating' => true,
    ];

    protected $casts = [
        'playing_datetime' => 'datetime',
        'is_paper_squad' => 'boolean',
        'match_kind' => MatchKind::class,
    ];

    protected $attributes = [
        'is_paper_squad' => false,
    ];

    protected $fillable = [
        'playerLimit',
        'order',
        'team_round_id',
        'team_id',
        'name',
        'tier',
        'is_paper_squad',
        'match_kind',
        'external_team_fight_id',
        'playing_datetime',
        'playing_place',
        'playing_address',
        'playing_zip_code',
        'playing_city',
        'version',
    ];

    protected static function booted(): void
    {
        // Only a Squad with a Team can be a Paper Squad; a one-off Squad without a Team can't.
        static::saving(function (Squad $squad) {
            if ($squad->is_paper_squad && $squad->team_id === null) {
                throw new PaperSquadWithoutTeamException;
            }
        });
    }

    public function buildSortQuery(): Builder
    {
        return static::query()->where('team_round_id', $this->team_round_id);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(SquadCategory::class);
    }

    /**
     * Reserve records of every Scenario; ScenarioManager and ReserveManager pick a Scenario's.
     *
     * @return HasMany<SquadReserve, $this>
     */
    public function reserves(): HasMany
    {
        return $this->hasMany(SquadReserve::class);
    }

    public function teamRound(): BelongsTo
    {
        return $this->belongsTo(TeamRound::class, 'team_round_id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * The tier shown for this squad: the attached team's current tier, or the squad's own free-text tier when no team is attached.
     */
    public function effectiveTier(): ?string
    {
        return $this->team === null ? $this->tier : $this->team->tierName();
    }
}
