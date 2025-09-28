<?php

namespace FlyCompany\BadmintonPlayerAPI;

enum AgeGroup : int
{
    case U09 = 2;
    case U11 = 3;
    case U13 = 4;
    case U15 = 5;
    case U17 = 6;
    case U19 = 7;
    case U23 = 20;
    case U17_U19 = 18;
    case UNG = 21;
    case SEN = 1;
    case SEN_PLUS_30 = 19;
    case SEN_PLUS_35 = 8;
    case SEN_PLUS_40 = 9;
    case SEN_PLUS_45 = 10;
    case SEN_PLUS_50 = 11;
    case SEN_PLUS_55 = 12;
    case SEN_PLUS_60 = 13;
    case SEN_PLUS_65 = 14;
    case SEN_PLUS_70 = 17;
    case SEN_PLUS_75 = 22;
    case SEN_PLUS_80 = 28;
    case MOT = 16;
    case ANDET = 15;
    case U10 = 23;
    case U12 = 24;
    case U14 = 25;
    case U16 = 26;
    case U18 = 27;

    public function label(): string
    {
        return match ($this) {
            self::U09 => 'U09',
            self::U11 => 'U11',
            self::U13 => 'U13',
            self::U15 => 'U15',
            self::U17 => 'U17',
            self::U19 => 'U19',
            self::U23 => 'U23',
            self::U17_U19 => 'U17/U19',
            self::UNG => 'UNG',
            self::SEN => 'SEN',
            self::SEN_PLUS_30 => 'SEN+30',
            self::SEN_PLUS_35 => 'SEN+35',
            self::SEN_PLUS_40 => 'SEN+40',
            self::SEN_PLUS_45 => 'SEN+45',
            self::SEN_PLUS_50 => 'SEN+50',
            self::SEN_PLUS_55 => 'SEN+55',
            self::SEN_PLUS_60 => 'SEN+60',
            self::SEN_PLUS_65 => 'SEN+65',
            self::SEN_PLUS_70 => 'SEN+70',
            self::SEN_PLUS_75 => 'SEN+75',
            self::SEN_PLUS_80 => 'SEN+80',
            self::MOT => 'MOT',
            self::ANDET => 'Andet',
            self::U10 => 'U10',
            self::U12 => 'U12',
            self::U14 => 'U14',
            self::U16 => 'U16',
            self::U18 => 'U18',
        };
    }
}
