<?php
declare(strict_types = 1);

namespace FlyCompany\Import;

use Illuminate\Support\Collection;

class ClubCollection extends Collection
{

    public function get($key, $default = null) : Club
    {
        return parent::get($key, $default);
    }

}
