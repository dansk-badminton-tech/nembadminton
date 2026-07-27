<?php

namespace App\Models;

use App\Enums\Permission;
use App\Enums\Role;
use App\Util\Util;
use FlyCompany\TeamFight\SquadManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Auth;

class TeamRound extends Model
{
    use HasFactory;

    public    $incrementing = false;

    protected $fillable     = ['teams', 'name', 'game_date', 'version', 'round', 'user_id', 'clubhouse_id', 'season_id'];

    protected static function booted(): void
    {
        static::creating(static function (TeamRound $teamRound) {
            $teamRound->id = Util::generateRandomString(24);
        });
        static::updated(static function (TeamRound $teamRound) {
            if($teamRound->isDirty('version')){
                $squadManager = new SquadManager();
                $squadManager->updatePointsOnAllSquadsInTeamRound($teamRound, $teamRound->version);
            }
        });
    }

    /**
     * Scope a query to only include popular users.
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeCurrentUser(Builder $query) : Builder
    {
        return $query->where('user_id', Auth::user()->id);
    }

    public function scopeVisibleToUser(Builder $query) : Builder
    {
        $user = Auth::user();
        if ($user && $user->primaryRole?->name === Role::PLAYER->value) {
            return $query->whereHas('squads.categories.players', function (Builder $q) use ($user) {
                $q->where('member_ref_id', $user->player_id);
            });
        }
        return $query;
    }

    public function resolveName(){
        if($this->name === null){
            return 'Runde '.$this->round;
        }
        return $this->name;
    }

    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function clubhouse() : BelongsTo
    {
        return $this->belongsTo(Clubhouse::class);
    }

    public function season() : BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function receiver() : HasOne
    {
        return $this->hasOne(TeamReceivers::class, 'team_round_id');
    }

    public function activityLogs() : HasMany
    {
        return $this->hasMany(TeamActivityLog::class, 'team_round_id')->orderBy('created_at', 'desc');
    }

    public function squads() : HasMany
    {
        return $this->hasMany(Squad::class, 'team_round_id', 'id')->orderBy('order');
    }

    /**
     * Ref IDs of players on this team round that have a linked platform user account.
     * Single batched query — runs once per team round, not per player.
     *
     * Lighthouse's @field(resolver:) resolves a fresh instance from the container
     * and passes the loaded model as the first argument, so we use $root (not $this).
     *
     * @param  TeamRound  $root  The loaded TeamRound model for this query node
     * @return array<int,string>
     */
    public function getReachablePlayerRefIds($root, array $args, $context, $resolveInfo): array
    {
        $refIds = $root->collectPlayerRefIds();

        if ($refIds === []) {
            return [];
        }

        return User::query()
            ->whereIn('player_id', $refIds)
            ->where('clubhouse_id', $root->clubhouse_id)
            ->pluck('player_id')
            ->map(fn ($id) => (string) $id)
            ->all();
    }

    /**
     * Walk squads → categories → players and collect all member_ref_id values.
     *
     * @return array<int,string>
     */
    private function collectPlayerRefIds(): array
    {
        $refIds = [];
        foreach ($this->squads as $squad) {
            foreach ($squad->categories as $category) {
                foreach ($category->players as $player) {
                    if ($player->member_ref_id !== null) {
                        $refIds[] = (string) $player->member_ref_id;
                    }
                }
            }
        }
        return $refIds;
    }
}
