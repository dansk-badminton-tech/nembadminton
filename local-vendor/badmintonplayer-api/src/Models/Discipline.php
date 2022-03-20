<?php

declare(strict_types=1);

namespace FlyCompany\BadmintonPlayerAPI\Models;

enum Discipline: string
{

    case MixedDoubles = 'MixedDoubles';
    case WomensSingles = 'WomensSingles';
    case MensSingles = 'MensSingles';
    case WomensDoubles = 'WomensDoubles';
    case MensDoubles = 'MensDoubles';

    public function shortName(): string
    {
        return match ($this) {
            Discipline::MixedDoubles => 'MD',
            Discipline::WomensSingles => 'DS',
            Discipline::MensSingles => 'HS',
            Discipline::WomensDoubles => 'DD',
            Discipline::MensDoubles => 'HD'
        };
    }

}
