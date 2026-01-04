<?php
declare(strict_types = 1);


namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

/**
 * @property int                        $id
 * @property Collection|SquadCategory[] $categories
 * @property Teams                      $team
 * @property int                        $teams_id
 * @property int                        $order
 * @property int|null                   $external_team_fight_id
 * @property Carbon|null                $playing_datetime
 * @property string|null                $playing_place
 * @property string|null                $playing_address
 * @property string|null                $playing_zip_code
 * @property string|null                $playing_city
 * @property Carbon|null                $version
 */
class Squad extends Model implements Sortable
{

    use HasFactory;
    use SortableTrait;

    public array $sortable = [
        'order_column_name'  => 'order',
        'sort_when_creating' => true,
    ];

    protected    $casts    = [
        "playing_datetime" => 'datetime',
    ];

    protected    $fillable = [
        'playerLimit',
        'league',
        'order',
        'teams_id',
        'name',
        'external_team_fight_id',
        'playing_datetime',
        'playing_place',
        'playing_address',
        'playing_zip_code',
        'playing_city',
        'version',
    ];

    public function buildSortQuery() : \Illuminate\Database\Eloquent\Builder
    {
        return static::query()->where('teams_id', $this->teams_id);
    }

    public function categories() : hasMany
    {
        return $this->hasMany(SquadCategory::class);
    }

    public function team() : BelongsTo
    {
        return $this->belongsTo(Teams::class, 'teams_id');
    }
}
