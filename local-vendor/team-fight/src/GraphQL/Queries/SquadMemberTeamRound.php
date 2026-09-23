<?php

declare(strict_types=1);

namespace FlyCompany\TeamFight\GraphQL\Queries;

use App\Models\Squad;
use App\Models\SquadMember;

/**
 * Finds the team round a placed player (SquadMember) belongs to, for resolvers on SquadMember fields.
 */
class SquadMemberTeamRound
{
    /**
     * In-memory cache mapping squadCategoryId -> teamRoundId
     * Avoids re-querying which team round a squad category belongs to when resolving multiple players.
     *
     * @var array<int|string, string>
     */
    private static array $categoryToRoundCache = [];

    /**
     * @return string The team round id, or '' when it cannot be determined.
     */
    public static function of(SquadMember $squadMember): string
    {
        // Use in-memory Eloquent relations if already loaded
        if ($squadMember->relationLoaded('category') && $squadMember->category?->relationLoaded('squad') && $squadMember->category->squad?->team_round_id !== null) {
            return (string) $squadMember->category->squad->team_round_id;
        }

        // Otherwise, look up and cache the team_round_id for this category
        $categoryId = $squadMember->squad_category_id;
        if ($categoryId === null) {
            return '';
        }

        if (!isset(self::$categoryToRoundCache[$categoryId])) {
            $roundId = Squad::query()
                ->whereHas('categories', fn ($query) => $query->whereKey($categoryId))
                ->value('team_round_id');

            self::$categoryToRoundCache[$categoryId] = (string) ($roundId ?? '');
        }

        return self::$categoryToRoundCache[$categoryId];
    }

    /**
     * Clear the static request cache (primarily for test teardowns).
     */
    public static function clearCache(): void
    {
        self::$categoryToRoundCache = [];
    }
}
