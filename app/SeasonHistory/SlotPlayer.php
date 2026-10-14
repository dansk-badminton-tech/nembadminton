<?php

declare(strict_types=1);

namespace App\SeasonHistory;

/**
 * A Member standing on a CategorySlot.
 */
final readonly class SlotPlayer
{
    /**
     * @param  string  $vintage  The age group, e.g. U17 or SEN.
     */
    public function __construct(
        public string $refId,
        public string $name,
        public string $gender,
        public string $vintage,
    ) {}
}
