<?php

declare(strict_types=1);

namespace App\SeasonHistory;

/**
 * A Member's place in one category of a TeamRound: the CategorySlot they stand on and, in doubles, their partner.
 */
final readonly class CategoryPlacement
{
    public function __construct(
        public CategorySlot $slot,
        public ?SlotPlayer $partner,
    ) {}
}
