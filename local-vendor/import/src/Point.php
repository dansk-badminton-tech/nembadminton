<?php
declare(strict_types = 1);


namespace FlyCompany\Import;

class Point
{

    private string $category;
    private int $points;

    public static function xmlFactory(\SimpleXMLElement $attributes){
        $point = new self();
        foreach ($attributes as $key => $value){
            if($key === 'cat'){
                $point->category = (string)$value;
            }
            if($key === 'pnt'){
                $point->points = (int)$value;
            }
        }
        return $point;
    }

}
