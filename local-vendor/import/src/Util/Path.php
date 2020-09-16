<?php
declare(strict_types = 1);

namespace FlyCompany\Import\Util;

class Path
{

    /**
     * @param string $date
     *
     * @return string
     */
    public static function getRankingPath(string $date) : string{
        return 'ranking/ranking-' . $date . '.xml';
    }

}
