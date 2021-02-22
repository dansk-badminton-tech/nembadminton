<?php
declare(strict_types = 1);


namespace App\QueryBuilders;

use Illuminate\Database\Eloquent\Builder;

class PointsBuilder
{

    public function version(Builder $builder) : Builder
    {
        dd($builder);
    }

}
