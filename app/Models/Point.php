<?php
declare(strict_types = 1);


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Point extends Model
{
    protected $fillable = ['points', 'position', 'category', 'cll', 'clh', 'vintage', 'member_id'];
}
