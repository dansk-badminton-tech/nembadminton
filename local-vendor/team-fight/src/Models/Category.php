<?php

declare(strict_types=1);


namespace FlyCompany\TeamFight\Models;

class Category
{

    public ?string $category;

    public string $name;

    /**
     * @var Player[]
     */
    public array $players = [];

    public function isMixDouble(): bool
    {
        return $this->category === 'MD';
    }

    public function isMensDouble(): bool
    {
        return $this->category === 'HD';
    }

    public function isWomensDouble(): bool
    {
        return $this->category === 'DD';
    }

    public function isMenSingle()
    {
        return $this->category === 'HS';
    }

    public function isWomenSingle()
    {
        return $this->category === 'DS';
    }

    public function isDouble(): bool
    {
        return $this->category === 'MD' || $this->category === 'DD' || $this->category === 'HD';
    }

    public function amountOfMenPlayers() : int{
        return array_reduce($this->players, static function(int $carry, Player $player){
            if($player->gender === 'M'){
                return ++$carry;
            }
            return $carry;
        }, 0);
    }

    public function amountOfWomenPlayers() : int{
        return array_reduce($this->players, static function(int $carry, Player $player){
            if($player->gender === 'K'){
                return ++$carry;
            }
            return $carry;
        }, 0);
    }

    public function amountOfPlayers(): int
    {
        return count($this->players);
    }

}
