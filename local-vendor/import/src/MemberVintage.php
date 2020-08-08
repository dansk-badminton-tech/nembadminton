<?php
declare(strict_types = 1);

namespace FlyCompany\Import;

class MemberVintage
{

    /**
     * @var string Name of the league
     */
    private string $name;

    private PointCollection $points;

    public static function xmlFactory(\SimpleXMLElement $attributes, \SimpleXMLElement $points) : MemberVintage
    {
        $vintage = new self();
        foreach ($attributes as $key => $value){
            if($key === 'nam'){
                $vintage->name = (string)$value;
            }
        }

        $pointCollection = new PointCollection();
        foreach ($points as $point){
            $pointCollection->add(Point::xmlFactory($point->attributes()));
        }
        $vintage->points = $pointCollection;

        return $vintage;
    }

    /**
     * @return PointCollection
     */
    public function getPoints() : PointCollection
    {
        return $this->points;
    }
}
