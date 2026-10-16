<?php

declare(strict_types=1);

namespace FlyCompany\TeamFight\Models;

/**
 * A reserve record on a Squad in validation input: $reserve, an Available Member or a Member placed on a lower Squad,
 * plays for the Member with $replacedRefId on this Squad (§43).
 */
class Reserve
{
    public Player $reserve;

    public string $replacedRefId;
}
