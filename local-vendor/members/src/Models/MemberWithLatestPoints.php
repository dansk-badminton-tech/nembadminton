<?php

declare(strict_types=1);

namespace FlyCompany\Members\Models;

use App\Models\Club;
use App\Models\Point;
use App\Models\SquadMember;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

/**
 * @property-read int $id
 * @property string $refId
 * @property string $name
 * @property string $gender
 * @property CarbonInterface|null $birthday
 * @property bool $hide
 * @property CarbonInterface $created_at
 * @property CarbonInterface $updated_at
 * @property int|null $latest_level_points
 * @property int|null $latest_level_position
 * @property CarbonInterface $latest_level_version
 * @property int|null $latest
 */
class MemberWithLatestPoints extends Model
{

    protected $table = 'members_with_latest_points';

    public function clubs(): BelongsToMany
    {
        return $this->belongsToMany(Club::class, 'club_member', 'member_id', 'club_id');
    }

    public function points(): HasMany
    {
        return $this->hasMany(Point::class, 'member_id');
    }

    public function scopeMyClub(Builder $builder): Builder
    {
        return $builder->whereHas('clubs', function (Builder $builder) {
            $builder->where('id', Auth::user()->organization_id);
        });
    }

}
