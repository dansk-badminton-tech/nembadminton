<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use FlyCompany\BadmintonPlayerAPI\Util;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A reserve record (§43): on the Squad, the reserve Member, an Available Member or one from a lower Squad, plays for the
 * replaced Member and stands in their slots. Scoped per Scenario like category slots.
 *
 * @property int $id
 * @property int $squad_id
 * @property int|null $team_round_scenario_id
 * @property string $reserve_ref_id
 * @property string $reserve_name
 * @property string $reserve_gender
 * @property string $replaced_ref_id
 * @property string $replaced_name
 * @property string $replaced_gender
 * @property Squad $squad
 */
class SquadReserve extends Model
{
    protected $fillable = [
        'squad_id',
        'team_round_scenario_id',
        'reserve_ref_id',
        'reserve_name',
        'reserve_gender',
        'replaced_ref_id',
        'replaced_name',
        'replaced_gender',
    ];

    protected $casts = [
        'team_round_scenario_id' => 'integer',
    ];

    /**
     * The records of one lineup: a Scenario's, or with null those outside any Scenario.
     *
     * @param  Builder<SquadReserve>  $query
     * @return Builder<SquadReserve>
     */
    public function scopeInLineup(Builder $query, ?int $scenarioId): Builder
    {
        return $scenarioId === null ? $query->whereNull('team_round_scenario_id') : $query->where('team_round_scenario_id', $scenarioId);
    }

    /**
     * @return BelongsTo<Squad, $this>
     */
    public function squad(): BelongsTo
    {
        return $this->belongsTo(Squad::class);
    }

    /**
     * @return BelongsTo<TeamRoundScenario, $this>
     */
    public function scenario(): BelongsTo
    {
        return $this->belongsTo(TeamRoundScenario::class, 'team_round_scenario_id');
    }

    /**
     * @return array{refId: string, name: string, gender: string, vintage: string}
     */
    public function reservePlayer(): array
    {
        return self::player($this->reserve_ref_id, $this->reserve_name, $this->reserve_gender);
    }

    /**
     * @return array{refId: string, name: string, gender: string, vintage: string}
     */
    public function replacedPlayer(): array
    {
        return self::player($this->replaced_ref_id, $this->replaced_name, $this->replaced_gender);
    }

    /**
     * @return array{refId: string, name: string, gender: string, vintage: string}
     */
    private static function player(string $refId, string $name, string $gender): array
    {
        // Same as SquadMember::getVintage: the birthday is the first six digits of the refId.
        $vintage = Util::calculateVintage(Carbon::createFromFormat('ymd', substr($refId, 0, 6)))->value;

        return ['refId' => $refId, 'name' => $name, 'gender' => $gender, 'vintage' => $vintage];
    }
}
