<?php

declare(strict_types=1);


namespace App\Models;

use Carbon\Carbon;
use FlyCompany\BadmintonPlayerAPI\Util;
use FlyCompany\BadmintonPlayerAPI\Vintage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

/**
 * Class Member
 *
 * @property int id
 * @property string refId
 * @property string name
 * @property string gender
 * @property Point[] points
 * @package App\Models
 */
class Member extends Model
{

    protected $fillable = ['refId', 'name', 'gender', 'birthday'];

    public function clubs(): BelongsToMany
    {
        return $this->belongsToMany(Club::class);
    }

    public function points(): HasMany
    {
        return $this->hasMany(Point::class);
    }

    public function cancellations() : HasMany{
        return $this->hasMany(Cancellation::class, 'refId', 'refId');
    }

    public function squadMember() : HasMany{
        return $this->hasMany(SquadMember::class, 'member_ref_id', 'refId');
    }

    public function scopeHasCancellations(Builder $builder, array $args)
    {
        $teamId = $args['teamId'];
        return $builder->whereHas('cancellations', static function (Builder $builder) use ($teamId) {
            $builder->where('teamId', '=', $teamId)->orWhereNull('teamId');
        });
    }

    public function scopeNotCancelled(Builder $builder, string $teamId): Builder
    {
        return $builder->whereDoesntHave('cancellations', static function (Builder $builder) use ($teamId) {
            $builder->where('teamId', '=', $teamId)->orWhereNull('teamId');
        });
    }

    public function scopeNotOnSquad(Builder $builder, string $teamId): Builder
    {
        return $builder->whereDoesntHave('squadMember.category.squad', function (Builder $builder) use ($teamId) {
            $builder->where('teams_id', '=', $teamId);
        });
    }

    public function scopeHasPoints(Builder $builder): Builder
    {
        return $builder->has('points');
    }

    public function scopeClub(Builder $builder, int $clubId): Builder
    {
        return $builder->whereHas('clubs', function (Builder $builder) use ($clubId) {
            $builder->where('id', $clubId);
        });
    }

    public function scopeMyClub(Builder $builder): Builder
    {
        return $builder->whereHas('clubs', function (Builder $builder) {
            $builder->where('id', Auth::user()->organization_id);
        });
    }

    public function getVintage() : string{
        return Util::calculateVintage($this->getBirthday())->value;
    }

    public function getBirthday() : Carbon {
        return Carbon::createFromFormat('ymd', substr($this->refId, 0, 6));
    }

}
