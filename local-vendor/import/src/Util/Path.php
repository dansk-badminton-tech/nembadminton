<?php
declare(strict_types = 1);

namespace FlyCompany\Import\Util;

class Path
{

    /**
     * @param string $date
     *
     * @return string
     * @throws \Exception
     */
    public static function getRankingPath(string $date) : string
    {
        $date = self::validateRankingDate($date);

        return 'ranking/ranking-' . $date->format('Y-m-d') . '.xml';
    }

    /**
     * @param string $date
     *
     * @return \DateTime
     * @throws \Exception
     */
    public static function validateRankingDate(string $date) : \DateTime
    {
        $date = new \DateTime($date);

        if (!(int)$date->format('j') === 1 && !in_array((int)$date->format('N'), [1, 3, 5], true)) {
            throw new \Exception('The date must be ether Monday, Wednesday or Friday');
        }

        return $date;
    }

}
