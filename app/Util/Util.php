<?php
declare(strict_types = 1);


namespace App\Util;

class Util
{

    /**
     * @param int $length
     *
     * @return false|string
     */
    public static function generateRandomString(int $length = 10)
    {
        return substr(str_shuffle(str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', (int)ceil($length / strlen($x)))), 1, $length);
    }

}
