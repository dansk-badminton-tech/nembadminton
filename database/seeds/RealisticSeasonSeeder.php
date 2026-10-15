<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\CanonicalTournamentTier;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Random\Engine\Mt19937;
use Random\Randomizer;

/**
 * A whole 2025/2026 Season of BC37 Amager in the testing Clubhouse, so the preview looks like a real club:
 *
 *   - Five Teams: Badmintonligaen, 2. division, Danmarksserien, Serie 2 and U17.
 *   - One TeamRound per round number with one Squad per Team. Rounds 1–7 have every Team, rounds 8–9 only
 *     Badmintonligaen (its group stage runs to round 9), round 10 is its play-off, round 11 its final as a draft
 *     Scenario only, and round 12 is qualification for the 2. division Team.
 *   - Lineups come from SeasonLineupGenerator with a fixed seed: one or two players rest each round, cancelled players
 *     are left out, and the rest are placed by points so every Official Lineup is valid.
 *   - Reserve Players (§43) in round 3 (Team 3 → Team 2) and round 5 (Team 2 → Team 1).
 *   - A cancellation collector with five cancellations.
 *
 * Needs MemberSeeder, PointSeeder, SeasonSeeder, ClubhouseSeeder and TournamentTierSeeder first.
 */
class RealisticSeasonSeeder extends Seeder
{
    /** The play-off final: a draft Scenario only, with one deliberate ordering error. */
    public const DRAFT_ROUND = 11;

    private const SEASON_ID = 2025;

    private const CLUBHOUSE_ID = 1;

    private const USER_ID = 1;

    private const CLUB_ID = 1087;

    private const SEED = 2025;

    /**
     * Ranking versions in the points data; a TeamRound uses the newest one on or before its game date.
     */
    private const RANKING_VERSIONS = ['2025-09-02', '2025-10-02', '2026-01-02', '2026-02-02'];

    /**
     * name, tier (or custom tier name), group, format, men, women, youth
     */
    private const TEAMS = [
        ['BC37 Amager 1', CanonicalTournamentTier::BADMINTONLIGAEN, 'Pulje 1', SeasonLineupGenerator::FORMAT_LIGA, 6, 4, false],
        ['BC37 Amager 2', CanonicalTournamentTier::DIVISION_2, 'Pulje 2', SeasonLineupGenerator::FORMAT_13, 6, 4, false],
        ['BC37 Amager 3', CanonicalTournamentTier::DANMARKSSERIEN, 'Pulje 4', SeasonLineupGenerator::FORMAT_13, 6, 4, false],
        ['BC37 Amager 4', CanonicalTournamentTier::SERIE_2, 'Pulje 3', SeasonLineupGenerator::FORMAT_11, 5, 3, false],
        ['BC37 Amager U17', 'U17', 'Pulje 1', SeasonLineupGenerator::FORMAT_11, 5, 3, true],
    ];

    /**
     * round => [game date, Teams (0-based) that play, opponent label per playing Team]
     */
    private const ROUNDS = [
        1 => ['2025-09-20', [0, 1, 2, 3, 4], ['Skovshoved IF', 'Greve Strand', 'Hørsholm', 'Tårnby', 'Kastrup-Magleby']],
        2 => ['2025-10-04', [0, 1, 2, 3, 4], ['Højbjerg', 'Hillerød', 'Charlottenlund', 'Dragør', 'Hvidovre']],
        3 => ['2025-10-25', [0, 1, 2, 3, 4], ['Gentofte', 'Ballerup', 'KMB', 'Sundby', 'Greve Strand']],
        4 => ['2025-11-15', [0, 1, 2, 3, 4], ['Vendsyssel', 'Køge', 'Taastrup', 'Kastrup-Magleby', 'Gentofte']],
        5 => ['2025-11-29', [0, 1, 2, 3, 4], ['Odense', 'Frederiksberg', 'Roskilde', 'Tårnby', 'Skovshoved IF']],
        6 => ['2026-01-10', [0, 1, 2, 3, 4], ['Solrød Strand', 'Holte', 'Valby', 'Dragør', 'Lyngby']],
        7 => ['2026-01-24', [0, 1, 2, 3, 4], ['Hvidovre', 'Lyngby', 'Glostrup', 'Sundby', 'Holte']],
        8 => ['2026-02-07', [0], ['Skovshoved IF']],
        9 => ['2026-02-21', [0], ['Højbjerg']],
        10 => ['2026-03-07', [0], ['Gentofte (semifinale)']],
        self::DRAFT_ROUND => ['2026-03-21', [0], ['Skovshoved IF (finale)']],
        12 => ['2026-03-28', [1], ['Vejle (kvalifikation)']],
    ];

    /**
     * round => Team (0-based) that takes a Reserve Player from a lower Team
     */
    private const RESERVES = [3 => 1, 5 => 0];

    /**
     * Cancellations: the senior pool place (0-based, by points, M or K) and the rounds they miss.
     */
    private const CANCELLATIONS = [
        ['M', 2, [2], 'Til bryllup i Aarhus'],
        ['M', 9, [4, 5], 'Forstuvet ankel'],
        ['K', 1, [6], 'Ferie'],
        ['K', 7, [3], 'Arbejde'],
        ['M', 14, [7], null],
    ];

    public function run(): void
    {
        $generator = new SeasonLineupGenerator(
            $this->pool(self::RANKING_VERSIONS, 'SEN', 27, 18),
            $this->pool(self::RANKING_VERSIONS, 'U17', 7, 5),
            new Randomizer(new Mt19937(self::SEED)),
        );

        $teamIds = $this->seedTeams();
        $absences = $this->seedCancellations($generator);

        foreach (self::ROUNDS as $round => [$gameDate, $playing, $opponents]) {
            $version = $this->rankingVersion($gameDate);
            $lineup = $generator->lineup(
                array_map(fn (array $team) => ['format' => $team[3], 'men' => $team[4], 'women' => $team[5], 'youth' => $team[6]], self::TEAMS),
                $version,
                $absences[$round] ?? [],
            );

            $reserve = isset(self::RESERVES[$round]) ? $generator->reserve($lineup, self::RESERVES[$round], $version) : null;
            if ($round === self::DRAFT_ROUND) {
                $lineup = $generator->withOrderingError($lineup, 0, $version);
            }

            $this->seedTeamRound($round, $gameDate, $version, $playing, $opponents, $teamIds, $lineup, $reserve);
        }
    }

    /**
     * Members of the club with points in every category of their gender at every version, all with the given vintage,
     * strongest first by their points at the first version.
     *
     * @param  list<string>  $versions
     * @return array{M: list<array<string, mixed>>, K: list<array<string, mixed>>}
     */
    private function pool(array $versions, string $vintage, int $men, int $women): array
    {
        $members = DB::table('members')
            ->join('club_member', 'club_member.member_id', '=', 'members.id')
            ->where('club_member.club_id', self::CLUB_ID)
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
            $categories = SeasonLineupGenerator::RANKING_CATEGORIES[$member->gender] ?? null;
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
            usort($players, fn (array $a, array $b) => SeasonLineupGenerator::strength($b, $versions[0]) <=> SeasonLineupGenerator::strength($a, $versions[0]) ?: $a['id'] <=> $b['id']);
            $pool[$gender] = array_slice($players, 0, $gender === 'M' ? $men : $women);
        }

        return $pool;
    }

    /**
     * @return list<int>
     */
    private function seedTeams(): array
    {
        $ids = [];
        foreach (self::TEAMS as [$name, $tier, $group]) {
            $ids[] = DB::table('teams')->insertGetId([
                'name' => $name,
                'tier_id' => $tier instanceof CanonicalTournamentTier ? DB::table('tournament_tiers')->where('tier_name', $tier->value)->value('id') : null,
                'custom_tier_name' => $tier instanceof CanonicalTournamentTier ? null : $tier,
                'group_name' => $group,
                'clubhouse_id' => self::CLUBHOUSE_ID,
                'season_id' => self::SEASON_ID,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $ids;
    }

    /**
     * @return array<int, list<string>> round => refIds that are away
     */
    private function seedCancellations(SeasonLineupGenerator $generator): array
    {
        $collectorId = DB::table('cancellation_collectors')->insertGetId([
            'user_id' => self::USER_ID,
            'email' => 'testing@gmail.com',
            'sharing_id' => 'bc37amagerafbud2025xxxxx',
            'clubhouse_id' => self::CLUBHOUSE_ID,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('cancellation_collector_clubs')->insert([
            'cancellation_collector_id' => $collectorId,
            'club_id' => self::CLUB_ID,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $absences = [];
        foreach (self::CANCELLATIONS as [$gender, $place, $rounds, $message]) {
            $player = $generator->seniorAt($gender, $place);
            $cancellationId = DB::table('cancellations')->insertGetId([
                'refId' => $player['refId'],
                'team_round_id' => null,
                'cancellation_collector_id' => $collectorId,
                'message' => $message,
                'email' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            foreach ($rounds as $round) {
                DB::table('cancellation_dates')->insert([
                    'cancellation_id' => $cancellationId,
                    'date' => self::ROUNDS[$round][0],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $absences[$round][] = $player['refId'];
            }
        }

        return $absences;
    }

    /**
     * @param  list<int>  $playing
     * @param  list<string>  $opponents
     * @param  list<int>  $teamIds
     * @param  list<array<string, mixed>>  $lineup
     * @param  array{squad: int, reserve: array<string, mixed>, replaced: array<string, mixed>}|null  $reserve
     */
    private function seedTeamRound(int $round, string $gameDate, string $version, array $playing, array $opponents, array $teamIds, array $lineup, ?array $reserve): void
    {
        $teamRoundId = Str::random(24);
        DB::table('team_rounds')->insert([
            'id' => $teamRoundId,
            'name' => null,
            'user_id' => self::USER_ID,
            'game_date' => $gameDate,
            'version' => $version,
            'clubhouse_id' => self::CLUBHOUSE_ID,
            'season_id' => self::SEASON_ID,
            'round' => $round,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $scenarioId = null;
        if ($round === self::DRAFT_ROUND) {
            $scenarioId = DB::table('team_round_scenarios')->insertGetId([
                'team_round_id' => $teamRoundId,
                'name' => 'Udkast til finalen',
                'is_official' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach ($playing as $order => $teamIndex) {
            $squadId = DB::table('squads')->insertGetId([
                'playerLimit' => SeasonLineupGenerator::PLAYER_LIMITS[self::TEAMS[$teamIndex][3]],
                'team_round_id' => $teamRoundId,
                'team_id' => $teamIds[$teamIndex],
                'order' => $order + 1,
                'name' => 'mod '.$opponents[$order],
                'playing_datetime' => $gameDate.' 13:00:00',
                'version' => $version,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($lineup[$teamIndex]['categories'] as $category) {
                $this->seedCategory($squadId, $scenarioId, $category, $version);
            }

            if ($reserve !== null && $reserve['squad'] === $teamIndex) {
                DB::table('squad_reserves')->insert([
                    'squad_id' => $squadId,
                    'team_round_scenario_id' => $scenarioId,
                    'reserve_ref_id' => $reserve['reserve']['refId'],
                    'reserve_name' => $reserve['reserve']['name'],
                    'reserve_gender' => $reserve['reserve']['gender'],
                    'replaced_ref_id' => $reserve['replaced']['refId'],
                    'replaced_name' => $reserve['replaced']['name'],
                    'replaced_gender' => $reserve['replaced']['gender'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * @param  array{category: string, name: string, players: list<array<string, mixed>>}  $category
     */
    private function seedCategory(int $squadId, ?int $scenarioId, array $category, string $version): void
    {
        $categoryId = DB::table('squad_categories')->insertGetId([
            'category' => $category['category'],
            'name' => $category['name'],
            'squad_id' => $squadId,
            'team_round_scenario_id' => $scenarioId,
        ]);

        foreach ($category['players'] as $player) {
            $squadMemberId = DB::table('squad_members')->insertGetId([
                'name' => $player['name'],
                'gender' => $player['gender'],
                'member_ref_id' => $player['refId'],
                'squad_category_id' => $categoryId,
            ]);

            DB::table('squad_points')->insert(array_map(fn (object $point) => [
                'points' => $point->points,
                'category' => $point->category,
                'position' => $point->position,
                'squad_member_id' => $squadMemberId,
                'vintage' => $point->vintage,
                'corrected_manually' => 0,
                'version' => $version,
            ], $player['versions'][$version]['rows']));
        }
    }

    private function rankingVersion(string $gameDate): string
    {
        $current = self::RANKING_VERSIONS[0];
        foreach (self::RANKING_VERSIONS as $version) {
            if ($version <= $gameDate) {
                $current = $version;
            }
        }

        return $current;
    }
}
