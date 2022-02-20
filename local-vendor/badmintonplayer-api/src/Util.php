<?php

declare(strict_types=1);

namespace FlyCompany\BadmintonPlayerAPI;

class Util
{

    /**
     * @throws \JsonException
     */
    public static function decodeUnicode(?string $str): ?string
    {
        if($str === null){
            return null;
        }
        return preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', static function ($match) {
            var_dump($match[1]);
            return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UTF-16BE');
        }, $str);
    }

}
