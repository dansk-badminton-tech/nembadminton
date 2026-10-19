<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\CanonicalTournamentTier;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Random\Engine\Mt19937;
use Random\Randomizer;

/**
 * The 2026/2027 Season of BC37 Amager in the testing Clubhouse, showing the paper-team rule (§38 stk. 7):
 *
 *   - Four Teams with the official match counts (§37): Badmintonligaen and 1. division with 9 matches, 2. and
 *     3. division with 13.
 *   - Rounds 1–11 with one Squad per Team: rounds 1–7 are the group stage, rounds 8–9 too for Badmintonligaen.
 *   - Three Paper Squads (papirhold): the 2. division Team in round 4 (its opponent withdrew), the 1. division
 *     Team in round 6 (likewise) and the Badmintonligaen Team in round 10 (a bye in the quarter-final).
 *   - The round 4 Paper Squad meets the rule. The round 6 one has a player back from injury without an earlier match
 *     and a player in a category she hasn't played; the round 10 one a player back from injury with one earlier
 *     match where the play-offs need two. That match is in round 9, whose lineups have no Team linked, as many do in
 *     production; the finding names round 9 as a match that couldn't be counted. PaperSquadSeasonSeedTest pins these
 *     findings.
 *   - Lineups come from SeasonLineupGenerator with a fixed seed, so every Official Lineup is otherwise valid.
 *
 * Needs MemberSeeder, PointSeeder, SeasonSeeder, ClubhouseSeeder and TournamentTierSeeder first.
 */
class PaperSquadSeasonSeeder extends Seeder
{
    use SeedsSquadCategories;

    private const SEASON_ID = 2026;

    private const CLUBHOUSE_ID = 1;

    private const USER_ID = 1;

    private const CLUB_ID = 1087;

    private const SEED = 2026;

    /** The newest ranking in the points data; every TeamRound of the Season uses it. */
    private const RANKING_VERSION = '2026-02-02';

    /**
     * name, tier, group, format, men, women
     */
    private const TEAMS = [
        ['BC37 Amager 1', CanonicalTournamentTier::BADMINTONLIGAEN, 'Badmintonligaen', SeasonLineupGenerator::FORMAT_9, 5, 4],
        ['BC37 Amager 2', CanonicalTournamentTier::DIVISION_1, 'Pulje 2', SeasonLineupGenerator::FORMAT_9, 4, 3],
        ['BC37 Amager 3', CanonicalTournamentTier::DIVISION_2, 'Pulje 1', SeasonLineupGenerator::FORMAT_13, 6, 4],
        ['BC37 Amager 4', CanonicalTournamentTier::DIVISION_3, 'Pulje 3', SeasonLineupGenerator::FORMAT_13, 6, 4],
    ];

    /**
     * round => [game date, opponent per Team; null for a Paper Squad]
     */
    private const ROUNDS = [
        1 => ['2026-09-19', ['Skovshoved IF', 'Greve Strand 2', 'Hillerød', 'Tårnby']],
        2 => ['2026-10-03', ['Højbjerg', 'Lyngby', 'Ballerup', 'Dragør']],
        3 => ['2026-10-31', ['Gentofte', 'Holte', 'Roskilde', 'Sundby']],
        4 => ['2026-11-14', ['Vendsyssel', 'Køge', null, 'Kastrup-Magleby']],
        5 => ['2026-11-28', ['Odense', 'Frederiksberg', 'Taastrup', 'Valby']],
        6 => ['2027-01-09', ['Solrød Strand', null, 'Charlottenlund', 'Glostrup']],
        7 => ['2027-01-23', ['Hvidovre', 'KMB', 'Hørsholm', 'Herlev']],
        8 => ['2027-02-06', ['Skovshoved IF', 'Hillerød', 'Lyngby', 'Tårnby']],
        9 => ['2027-02-20', ['Højbjerg', 'Gentofte 2', 'Holte', 'Dragør']],
        10 => ['2027-03-06', [null, 'Ballerup', 'Køge', 'Sundby']],
        11 => ['2027-03-20', ['Gentofte (semifinale)', 'Roskilde', 'Taastrup', 'Valby']],
    ];

    /** Rounds whose Squads have no Team linked. */
    private const ROUNDS_WITHOUT_TEAMS = [9];

    /**
     * Players out injured: the senior pool place (0-based, by points, M or K) and the rounds they miss.
     */
    private const INJURED = [
        ['K', 1, [1, 2, 3, 4, 5, 6, 7, 8]],
        ['K', 6, [1, 2, 3, 4, 5]],
    ];

    public function run(): void
    {
        $generator = new SeasonLineupGenerator(
            SeasonLineupGenerator::clubPool(self::CLUB_ID, [self::RANKING_VERSION], 'SEN', 27, 18),
            ['M' => [], 'K' => []],
            new Randomizer(new Mt19937(self::SEED)),
        );

        $teamIds = $this->seedTeams();
        $absences = [];
        foreach (self::INJURED as [$gender, $place, $rounds]) {
            foreach ($rounds as $round) {
                $absences[$round][] = $generator->seniorAt($gender, $place)['refId'];
            }
        }

        foreach (self::ROUNDS as $round => [$gameDate, $opponents]) {
            $lineup = $generator->lineup(
                array_map(fn (array $team) => ['format' => $team[3], 'men' => $team[4], 'women' => $team[5], 'youth' => false], self::TEAMS),
                self::RANKING_VERSION,
                $absences[$round] ?? [],
            );

            $this->seedTeamRound($round, $gameDate, $opponents, $teamIds, $lineup);
        }
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
                'tier_id' => DB::table('tournament_tiers')->where('tier_name', $tier->value)->value('id'),
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
     * @param  list<string|null>  $opponents
     * @param  list<int>  $teamIds
     * @param  list<array<string, mixed>>  $lineup
     */
    private function seedTeamRound(int $round, string $gameDate, array $opponents, array $teamIds, array $lineup): void
    {
        // A fixed id keeps the order of TeamRounds on the same date and round number stable (Season History sorts by id).
        $teamRoundId = str_pad(sprintf('bc37amager2026runde%02d', $round), 24, 'x');
        DB::table('team_rounds')->insert([
            'id' => $teamRoundId,
            'name' => null,
            'user_id' => self::USER_ID,
            'game_date' => $gameDate,
            'version' => self::RANKING_VERSION,
            'clubhouse_id' => self::CLUBHOUSE_ID,
            'season_id' => self::SEASON_ID,
            'round' => $round,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach (self::TEAMS as $teamIndex => [, , , , $men, $women]) {
            $opponent = $opponents[$teamIndex];
            $squadId = DB::table('squads')->insertGetId([
                'playerLimit' => $men + $women,
                'team_round_id' => $teamRoundId,
                'team_id' => in_array($round, self::ROUNDS_WITHOUT_TEAMS, true) ? null : $teamIds[$teamIndex],
                'order' => $teamIndex + 1,
                'name' => $opponent === null ? 'Papirhold' : 'mod '.$opponent,
                'is_paper_squad' => $opponent === null,
                'playing_datetime' => $opponent === null ? null : $gameDate.' 13:00:00',
                'version' => self::RANKING_VERSION,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($lineup[$teamIndex]['categories'] as $category) {
                $this->seedCategory($squadId, null, $category, self::RANKING_VERSION);
            }
        }
    }
}
