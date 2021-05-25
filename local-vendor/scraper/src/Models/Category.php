<?php
declare(strict_types = 1);


namespace FlyCompany\Scraper\Models;

class Category
{

    public ?string $category;

    public string  $name;

    /**
     * @var Player[]
     */
    public array $players = [];

}
