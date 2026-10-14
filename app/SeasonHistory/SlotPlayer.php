<?php

declare(strict_types=1);

namespace App\SeasonHistory;

/**
 * A Member standing on a CategorySlot.
 */
final readonly class SlotPlayer
{
    public function __construct(
        public string $refId,
        public string $name,
        public string $gender,
    ) {}
}
