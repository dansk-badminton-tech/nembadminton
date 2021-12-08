<?php
declare(strict_types = 1);


namespace App\Models;

use FlyCompany\Scraper\Models\Team;
use FlyCompany\TeamFight\Models\Category;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int id
 * @property Category[] categories
 * @property Team team
 */
class Squad extends Model
{

    use HasFactory;

    protected $fillable = ['playerLimit', 'league', 'teams_id'];

    public function categories() : hasMany
    {
        return $this->hasMany(SquadCategory::class);
    }

    public function team() : BelongsTo
    {
        return $this->belongsTo(Teams::class, 'teams_id');
    }
}
