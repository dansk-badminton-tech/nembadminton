<?php

declare(strict_types=1);

namespace FlyCompany\Import;

class Point
{
    private ?string $category = null;

    private ?int $points = null;

    private ?int $position = null;

    private ?string $cll = null;

    private ?string $clh = null;

    public static function xmlFactory(\SimpleXMLElement $attributes)
    {
        $point = new self;
        foreach ($attributes as $key => $value) {
            if ($key === 'cat') {
                $point->category = (string) $value;
            }
            if ($key === 'pnt') {
                $point->points = (int) $value;
            }
            if ($key === 'pos') {
                $point->position = (int) $value;
            }
            if ($key === 'cll') {
                $point->cll = (string) $value;
            }
            if ($key === 'clh') {
                $point->clh = (string) $value;
            }
        }

        return $point;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function getPoints(): ?int
    {
        return $this->points;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function getCll(): ?string
    {
        return $this->cll;
    }

    public function getClh(): ?string
    {
        return $this->clh;
    }
}
