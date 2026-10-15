<?php

declare(strict_types=1);

namespace FlyCompany\TeamFight\Models;

/**
 * A reserve record on a Squad in validation input: the Member with $reserveRefId, placed on a lower Squad, plays for the
 * Member with $replacedRefId on this Squad (§43).
 */
class Reserve
{
    public string $reserveRefId;

    public string $replacedRefId;
}
