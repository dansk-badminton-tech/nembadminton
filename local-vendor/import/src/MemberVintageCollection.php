<?php


namespace FlyCompany\Import;

use Illuminate\Support\Collection;

class MemberVintageCollection extends Collection
{

    public function get($key, $default = null) : ?MemberVintage
    {
        return parent::get($key, $default);
    }

}
