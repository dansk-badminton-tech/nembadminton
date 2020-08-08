<?php
declare(strict_types = 1);


namespace FlyCompany\Import;

use Illuminate\Support\Collection;

class PointCollection extends Collection
{

    public function get($key, $default = null) : ?Point
    {
        return parent::get($key, $default);
    }

}
