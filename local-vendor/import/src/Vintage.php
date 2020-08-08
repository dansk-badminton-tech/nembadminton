<?php
declare(strict_types = 1);

namespace FlyCompany\Import;

class Vintage
{

    private int $id;

    /**
     * @var string Name of the league
     */
    private string $name;

    /**
     * @var int Starting ege for a league
     */
    private int $ageFrom;

    /**
     * @var ?int End age for a league
     */
    private ?int $ageTo;

    /**
     * @var int I dont know yet
     */
    private int $tp;

    public static function xmlFactory(\SimpleXMLElement $attributes) : Vintage
    {
        $vintage = new self();
        foreach ($attributes as $key => $value){
            if($key === 'id'){
                $vintage->id = (int)$value;
            }
            if($key === 'nm' || $key === 'nam'){
                $vintage->name = (string)$value;
            }
            if($key === 'fr'){
                $vintage->ageFrom = (int)$value;
            }
            if($key === 'to'){
                $vintage->ageTo = (int)$value;
            }
            if($key === 'tp'){
                $vintage->tp = (int)$value;
            }
        }
        return $vintage;
    }
}
