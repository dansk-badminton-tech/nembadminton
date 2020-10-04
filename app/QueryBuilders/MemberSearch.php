<?php
declare(strict_types = 1);


namespace App\QueryBuilders;

use Illuminate\Database\Eloquent\Builder;

/**
 * Class MemberSearch
 *
 * @package App\QueryBuilders
 */
class MemberSearch
{

    /**
     * Add a limit constrained upon the query.
     *
     * @param Builder  $builder
     * @param int|null $value
     *
     * @return Builder
     */
    public function club(Builder $builder, ?int $value) : Builder
    {
        if ($value !== null) {
            return $builder->where('id', '=', $value);
        }

        return $builder;
    }

}
