<?php


namespace FlyCompany\Import;

use Illuminate\Support\Collection;

class MemberCollection extends Collection
{

    public function get($key, $default = null) : Member
    {
        return parent::get($key, $default);
    }

}
