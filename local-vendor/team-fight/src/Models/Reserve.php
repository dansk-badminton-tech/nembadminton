<?php

declare(strict_types=1);

namespace FlyCompany\TeamFight\Models;

/**
 * A reserve record on a Squad in validation input: the Member with $reserveRefId, placed on this Squad, plays for
 * $replaced, who no longer stands there (§43).
 */
class Reserve
{
    public string $reserveRefId;

    public Player $replaced;
}
