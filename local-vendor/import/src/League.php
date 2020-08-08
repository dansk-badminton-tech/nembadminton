<?php
declare(strict_types = 1);

namespace FlyCompany\Import;

class League
{

    private int $id;
    private string $name;

    public static function xmlFactory(\SimpleXMLElement $attributes) : League
    {
        $league = new self();
        foreach ($attributes as $key => $value){
            if($key === 'id'){
                $league->id = (int)$value;
            }
            if($key === 'nm'){
                $league->name = (string)$value;
            }
        }
        return $league;
    }

}
