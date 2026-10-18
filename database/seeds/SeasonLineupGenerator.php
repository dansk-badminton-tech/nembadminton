<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Random\Randomizer;
use RuntimeException;

/**
 * Lineups for RealisticSeasonSeeder and PaperSquadSeasonSeeder, from a club's players (clubPool). Each round one or two seniors rest and cancelled players are away; the others
 * fill the Teams by their doubles points, and within a Team every category is placed by points. A player plays at
 * most two matches and at most one single, so every lineup follows TeamValidator: no player is placed above a
 * stronger one in a category, on the same Squad or a lower one. Every senior on the higher Teams plays doubles, so
 * filling Teams by doubles points keeps anyone on a lower Team from being stronger in all their categories.
 *
 * A player is ['id', 'refId', 'name', 'gender', 'versions' => [version => ['points' => [category => points], 'rows']]].
 */
class SeasonLineupGenerator
{
    public const FORMAT_LIGA = 'liga';

    /** Badmintonligaen and 1. division (§37 stk. 1). FORMAT_LIGA is an earlier Badmintonligaen format with 10 matches. */
    public const FORMAT_9 = '9';

    /** 2. division, 3. division and Danmarksserien (§37 stk. 2). */
    public const FORMAT_13 = '13';

    public const FORMAT_11 = '11';

    /**
     * Matches per category, in the order the categories are listed on a Squad.
     */
    public const FORMATS = [
        self::FORMAT_LIGA => ['MD' => 1, 'DS' => 1, 'HS' => 3, 'DD' => 2, 'HD' => 3],
        self::FORMAT_9 => ['MD' => 2, 'DS' => 2, 'HS' => 2, 'DD' => 1, 'HD' => 2],
        self::FORMAT_13 => ['MD' => 2, 'DS' => 2, 'HS' => 4, 'DD' => 2, 'HD' => 3],
        self::FORMAT_11 => ['MD' => 2, 'DS' => 2, 'HS' => 4, 'DD' => 1, 'HD' => 2],
    ];

    public const PLAYER_LIMITS = [self::FORMAT_LIGA => 10, self::FORMAT_13 => 10, self::FORMAT_11 => 8];

    /**
     * Ranking categories per gender: single, double, mixed.
     */
    public const RANKING_CATEGORIES = ['M' => ['HS', 'HD', 'MxH'], 'K' => ['DS', 'DD', 'MxD']];

    private const DOUBLES_LIMIT = 100;

    private const CROSS_SQUAD_LIMIT = 50;

    /**
     * @param  array{M: list<array<string, mixed>>, K: list<array<string, mixed>>}  $seniors  Strongest first
     * @param  array{M: list<array<string, mixed>>, K: list<array<string, mixed>>}  $youth  Strongest first
     */
    public function __construct(
        private readonly array $seniors,
        private readonly array $youth,
        private readonly Randomizer $randomizer,
    ) {}

    /**
     * Members of the club with points in every category of their gender at every version, all with the given vintage,
     * strongest first by their points at the first version.
     *
     * @param  list<string>  $versions
     * @return array{M: list<array<string, mixed>>, K: list<array<string, mixed>>}
     */
    public static function clubPool(int $clubId, array $versions, string $vintage, int $men, int $women): array
    {
        $members = DB::table('members')
            ->join('club_member', 'club_member.member_id', '=', 'members.id')
            ->where('club_member.club_id', $clubId)
            ->where('members.playable', true)
            ->where('members.inactive', false)
            ->orderBy('members.id')
            ->get(['members.id', 'members.refId', 'members.name', 'members.gender']);

        $points = DB::table('points')
            ->whereIn('member_id', $members->pluck('id'))
            ->whereIn('version', $versions)
            ->get()
            ->groupBy(['member_id', 'version']);

        $pool = ['M' => [], 'K' => []];
        foreach ($members as $member) {
            $categories = self::RANKING_CATEGORIES[$member->gender] ?? null;
            if ($categories === null) {
                continue;
            }

            $byVersion = [];
            foreach ($versions as $version) {
                $rows = $points[$member->id][$version] ?? collect();
                if ($rows->isEmpty() || $rows->contains(fn (object $row) => $row->vintage !== $vintage)) {
                    continue 2;
                }
                $byCategory = $rows->pluck('points', 'category')->map(fn ($value) => (int) $value)->all();
                if (array_diff($categories, array_keys($byCategory)) !== []) {
                    continue 2;
                }
                $byVersion[$version] = ['points' => $byCategory, 'rows' => $rows->all()];
            }

            $pool[$member->gender][] = [
                'id' => $member->id,
                'refId' => $member->refId,
                'name' => $member->name,
                'gender' => $member->gender,
                'versions' => $byVersion,
            ];
        }

        foreach ($pool as $gender => $players) {
            usort($players, fn (array $a, array $b) => self::strength($b, $versions[0]) <=> self::strength($a, $versions[0]) ?: $a['id'] <=> $b['id']);
            $pool[$gender] = array_slice($players, 0, $gender === 'M' ? $men : $women);
        }

        return $pool;
    }

    /**
     * @param  array<string, mixed>  $player
     */
    public static function strength(array $player, string $version): int
    {
        return array_sum($player['versions'][$version]['points']);
    }

    /**
     * @return array<string, mixed>
     */
    public function seniorAt(string $gender, int $place): array
    {
        return $this->seniors[$gender][$place] ?? throw new RuntimeException("No senior {$gender} at place {$place}");
    }

    /**
     * One lineup per Team, in Team order.
     *
     * @param  list<array{format: string, men: int, women: int, youth: bool}>  $teams
     * @param  list<string>  $away  refIds of cancelled players
     * @return list<array{format: string, categories: list<array{category: string, name: string, players: list<array<string, mixed>>}>}>
     */
    public function lineup(array $teams, string $version, array $away): array
    {
        $resting = [$this->pickResting('M', $away)];
        if ($this->randomizer->getInt(0, 1) === 1) {
            $resting[] = $this->pickResting('K', $away);
        }
        $unavailable = [...$away, ...$resting];

        $available = [];
        foreach (['seniors' => $this->seniors, 'youth' => $this->youth] as $group => $pool) {
            foreach ($pool as $gender => $players) {
                $players = array_values(array_filter($players, fn (array $player) => ! in_array($player['refId'], $unavailable, true)));
                $available[$group][$gender] = $this->sortedBy($players, $gender === 'M' ? 'HD' : 'DD', $version);
            }
        }

        $lineup = [];
        foreach ($teams as $team) {
            $group = $team['youth'] ? 'youth' : 'seniors';
            $men = array_splice($available[$group]['M'], 0, $team['men']);
            $women = array_splice($available[$group]['K'], 0, $team['women']);
            if (count($men) < $team['men'] || count($women) < $team['women']) {
                throw new RuntimeException('Not enough players for a Team');
            }
            $lineup[] = ['format' => $team['format'], 'categories' => $this->place(self::FORMATS[$team['format']], $men, $women, $version)];
        }

        return $lineup;
    }

    /**
     * A Reserve Player for the Team at the index (§43): a player from a lower Team plays for the weakest player of the
     * Team they can replace. The reserve is the strongest player below with no more points than the replaced one in any
     * of their categories (§43 stk. 3), and with no senior left below who is stronger in all the reserve's categories
     * (the normal check across Teams). The lineup is the one as played: the Team is placed again by points with the
     * reserve instead of the replaced player, and the reserve keeps their slot below.
     *
     * @param  list<array<string, mixed>>  $lineup
     * @return array{squad: int, reserve: array<string, mixed>, replaced: array<string, mixed>, lineup: list<array<string, mixed>>}
     */
    public function reserve(array $lineup, int $teamIndex, string $version): array
    {
        $below = [];
        foreach (array_slice($lineup, $teamIndex + 1) as $team) {
            foreach (self::players($team) as $refId => $player) {
                $below[$refId] ??= $player;
            }
        }

        $onTeam = self::players($lineup[$teamIndex]);
        uasort($onTeam, fn (array $a, array $b) => self::strength($a, $version) <=> self::strength($b, $version));
        foreach ($onTeam as $replaced) {
            $categories = self::categoriesOf($lineup[$teamIndex], $replaced['refId']);
            $sameGender = array_filter($below, fn (array $player) => $player['gender'] === $replaced['gender']);
            $candidates = array_filter($sameGender, function (array $player) use ($categories, $replaced, $version) {
                foreach ($categories as $category) {
                    if (self::points($player, $category, $version) > self::points($replaced, $category, $version)) {
                        return false;
                    }
                }

                return true;
            });
            uasort($candidates, fn (array $a, array $b) => self::strength($b, $version) <=> self::strength($a, $version));

            foreach ($candidates as $reserve) {
                $players = [...array_values(array_filter($onTeam, fn (array $player) => $player['refId'] !== $replaced['refId'])), $reserve];
                $categories = $this->place(
                    self::FORMATS[$lineup[$teamIndex]['format']],
                    array_values(array_filter($players, fn (array $player) => $player['gender'] === 'M')),
                    array_values(array_filter($players, fn (array $player) => $player['gender'] === 'K')),
                    $version,
                );
                if (! $this->strongerBelow($below, $reserve, self::categoriesOf(['categories' => $categories], $reserve['refId']), $version)) {
                    $lineup[$teamIndex]['categories'] = $categories;

                    return ['squad' => $teamIndex, 'reserve' => $reserve, 'replaced' => $replaced, 'lineup' => $lineup];
                }
            }
        }

        throw new RuntimeException("No Reserve Player for Team {$teamIndex}");
    }

    /**
     * Whether a senior below, other than the reserve, has more than the limit above the reserve in all of the reserve's
     * categories on the Team (TeamValidator's check across Teams; youth players are left out of it).
     *
     * @param  array<string, array<string, mixed>>  $below
     * @param  array<string, mixed>  $reserve
     * @param  list<string>  $categories
     */
    private function strongerBelow(array $below, array $reserve, array $categories, string $version): bool
    {
        $youth = array_column([...$this->youth['M'], ...$this->youth['K']], 'refId');
        foreach ($below as $player) {
            if ($player['refId'] === $reserve['refId'] || $player['gender'] !== $reserve['gender'] || in_array($player['refId'], $youth, true)) {
                continue;
            }
            $stronger = array_filter($categories, fn (string $category) => self::points($player, $category, $version) > self::points($reserve, $category, $version) + self::CROSS_SQUAD_LIMIT);
            if (count($stronger) === count($categories)) {
                return true;
            }
        }

        return false;
    }

    /**
     * The lineup with two men's doubles pairs on the Team swapped, so a weaker pair stands above a stronger one by more
     * than the limit: one ordering error, reported for both players of the pair. The stronger pair swaps with the first
     * one more than the limit below it, so the pairs in between stay within the limit of both.
     *
     * @param  list<array<string, mixed>>  $lineup
     * @return list<array<string, mixed>>
     */
    public function withOrderingError(array $lineup, int $teamIndex, string $version): array
    {
        $categories = &$lineup[$teamIndex]['categories'];
        $pairs = array_keys(array_filter($categories, fn (array $category) => $category['category'] === 'HD'));
        $points = fn (int $slot) => array_sum(array_map(fn (array $player) => self::points($player, 'HD', $version), $categories[$slot]['players']));

        foreach ($pairs as $i => $upper) {
            foreach (array_slice($pairs, $i + 1) as $lower) {
                if ($points($upper) > $points($lower) + self::DOUBLES_LIMIT) {
                    [$categories[$upper]['players'], $categories[$lower]['players']] = [$categories[$lower]['players'], $categories[$upper]['players']];

                    return $lineup;
                }
            }
        }

        throw new RuntimeException('No men\'s doubles pairs far enough apart for an ordering error');
    }

    /**
     * @param  array<string, int>  $format
     * @param  list<array<string, mixed>>  $men
     * @param  list<array<string, mixed>>  $women
     * @return list<array{category: string, name: string, players: list<array<string, mixed>>}>
     */
    private function place(array $format, array $men, array $women, string $version): array
    {
        $matches = [];
        [$menSingles, $menDoubles] = $this->singlesAndDoubles($men, 'HS', $format['HS'], 'HD', $format['HD'], $matches, $version);
        [$womenSingles, $womenDoubles] = $this->singlesAndDoubles($women, 'DS', $format['DS'], 'DD', $format['DD'], $matches, $version);
        $mixedMen = $this->mixedPlayers($men, $format['MD'], $matches, $version);
        $mixedWomen = $this->mixedPlayers($women, $format['MD'], $matches, $version);

        $slots = [
            'MD' => array_map(null, $mixedMen, $mixedWomen),
            'DS' => array_map(fn (array $player) => [$player], $womenSingles),
            'HS' => array_map(fn (array $player) => [$player], $menSingles),
            'DD' => $womenDoubles,
            'HD' => $menDoubles,
        ];

        $categories = [];
        foreach ($slots as $category => $entries) {
            foreach ($entries as $index => $players) {
                $categories[] = ['category' => $category, 'name' => ($index + 1).'. '.$category, 'players' => $players];
            }
        }

        return $categories;
    }

    /**
     * The singles players (strongest first) and the doubles pairs (strongest pair first). Players without a single
     * play doubles first.
     *
     * @param  list<array<string, mixed>>  $players
     * @param  array<string, int>  $matches
     * @return array{0: list<array<string, mixed>>, 1: list<array{0: array<string, mixed>, 1: array<string, mixed>}>}
     */
    private function singlesAndDoubles(array $players, string $single, int $singles, string $double, int $doubles, array &$matches, string $version): array
    {
        $bySingle = $this->sortedBy($players, $single, $version);
        $singlesPlayers = array_slice($bySingle, 0, $singles);
        $others = array_slice($bySingle, $singles);

        $doublesPlayers = array_slice([...$others, ...$this->sortedBy($singlesPlayers, $double, $version)], 0, $doubles * 2);
        $doublesPlayers = $this->sortedBy($doublesPlayers, $double, $version);

        foreach ([...$singlesPlayers, ...$doublesPlayers] as $player) {
            $matches[$player['refId']] = ($matches[$player['refId']] ?? 0) + 1;
        }

        return [$singlesPlayers, array_chunk($doublesPlayers, 2)];
    }

    /**
     * The mixed doubles players of one gender, strongest first: those with the fewest matches so far.
     *
     * @param  list<array<string, mixed>>  $players
     * @param  array<string, int>  $matches
     * @return list<array<string, mixed>>
     */
    private function mixedPlayers(array $players, int $count, array &$matches, string $version): array
    {
        $eligible = array_values(array_filter($players, fn (array $player) => ($matches[$player['refId']] ?? 0) < 2));
        usort($eligible, fn (array $a, array $b) => ($matches[$a['refId']] ?? 0) <=> ($matches[$b['refId']] ?? 0)
            ?: self::points($b, 'MD', $version) <=> self::points($a, 'MD', $version));
        $chosen = $this->sortedBy(array_slice($eligible, 0, $count), 'MD', $version);
        if (count($chosen) < $count) {
            throw new RuntimeException('Not enough players for mixed doubles');
        }
        foreach ($chosen as $player) {
            $matches[$player['refId']] = ($matches[$player['refId']] ?? 0) + 1;
        }

        return $chosen;
    }

    /**
     * @param  list<array<string, mixed>>  $players
     * @return list<array<string, mixed>>
     */
    private function sortedBy(array $players, string $category, string $version): array
    {
        usort($players, fn (array $a, array $b) => self::points($b, $category, $version) <=> self::points($a, $category, $version) ?: $a['id'] <=> $b['id']);

        return $players;
    }

    /**
     * @param  list<string>  $away
     */
    private function pickResting(string $gender, array $away): string
    {
        $candidates = array_values(array_filter($this->seniors[$gender], fn (array $player) => ! in_array($player['refId'], $away, true)));

        return $candidates[$this->randomizer->getInt(0, count($candidates) - 1)]['refId'];
    }

    /**
     * Points in a Squad category (MD is the mixed ranking of the player's gender).
     *
     * @param  array<string, mixed>  $player
     */
    private static function points(array $player, string $category, string $version): int
    {
        if ($category === 'MD') {
            $category = $player['gender'] === 'M' ? 'MxH' : 'MxD';
        }

        return $player['versions'][$version]['points'][$category];
    }

    /**
     * @param  array<string, mixed>  $team
     * @return array<string, array<string, mixed>>
     */
    private static function players(array $team): array
    {
        $players = [];
        foreach ($team['categories'] as $category) {
            foreach ($category['players'] as $player) {
                $players[$player['refId']] ??= $player;
            }
        }

        return $players;
    }

    /**
     * @param  array<string, mixed>  $team
     * @return list<string>
     */
    private static function categoriesOf(array $team, string $refId): array
    {
        $categories = [];
        foreach ($team['categories'] as $category) {
            foreach ($category['players'] as $player) {
                if ($player['refId'] === $refId) {
                    $categories[] = $category['category'];
                }
            }
        }

        return array_values(array_unique($categories));
    }
}
