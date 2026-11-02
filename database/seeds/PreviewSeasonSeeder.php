<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\CanonicalTournamentTier;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Random\Engine\Mt19937;
use Random\Randomizer;

/**
 * BC37 Clubhouse 126's 2025/2026 dates, Ranking Lists and historical participants.
 * Team links/tiers are reconstructed from its later Team setup. Lineups are normalized
 * using the real rankings; absences, reserves and Paper Squads are illustrative alterations,
 * not claims about actual matches. No production account, contact or sharing data is used.
 */
class PreviewSeasonSeeder extends Seeder
{
    use SeedsSquadCategories;

    public const HINTS = [
        1 => 'Gyldig opstilling',
        2 => 'Afbud',
        3 => 'Reservespiller',
        4 => 'Papirhold med tidligere deltagelse',
        5 => 'Tilbage efter afbud',
        6 => 'Papirhold: debut og ny kategori',
        7 => 'Grundspil og sæsonhistorik',
        8 => 'Slutspil',
        9 => 'Tilbage efter skade',
        10 => 'Papirhold med kun én tidligere kamp',
        11 => 'Udkast med forkert rækkefølge',
    ];

    private const TIERS = [
        CanonicalTournamentTier::DIVISION_3,
        CanonicalTournamentTier::DANMARKSSERIEN,
        CanonicalTournamentTier::KOEBENHAVNSSERIEN,
        CanonicalTournamentTier::SERIE_1,
    ];

    public static function roundId(int $round): string
    {
        return str_pad(sprintf('previewbc372025round%02d', $round), 24, 'x');
    }

    public function run(): void
    {
        $source = PreviewClubDataSeeder::fixture();
        $versions = array_values(array_unique(array_column($source['rounds'], 'version')));
        $pool = SeasonLineupGenerator::clubPool(1087, $versions, 'SEN', 100, 100);
        // Two strong Members return after illustrative injuries: one in round 6, one in round 9.
        $returning = [$pool['K'][0], $pool['K'][1]];
        $cancelled = $pool['M'][2];
        $this->seedCancellations($source['rounds'], $returning, $cancelled);
        $teams = $this->seedTeams();
        $quarantined = null;
        $lineups = [];

        foreach ($source['rounds'] as $historical) {
            $round = $historical['round'];
            $version = $historical['version'];
            $away = [];
            foreach ([0 => 6, 1 => 9] as $index => $returnRound) {
                if ($round < $returnRound) {
                    $away[] = $returning[$index]['refId'];
                }
            }
            if ($round === 2) {
                $away[] = $cancelled['refId'];
            }
            if ($round === 4) {
                $away[] = $quarantined;
            }

            // A Paper Squad needs a lower Team that plays. Round 6 therefore includes Team 4 as well.
            $count = $round === 6 ? 4 : count($historical['squads']);
            $participants = array_unique(array_merge(...array_column($historical['squads'], 'members')));
            $roundPool = [];
            foreach (['M' => 6, 'K' => 4] as $gender => $perTeam) {
                $candidates = array_values(array_filter($pool[$gender], fn (array $member) => ! in_array($member['refId'], $away, true)));
                // Prefer this round's actual historical participants, then fill its reserve pool.
                usort($candidates, fn (array $a, array $b) => (int) in_array($b['refId'], $participants, true) <=> (int) in_array($a['refId'], $participants, true)
                    ?: SeasonLineupGenerator::strength($b, $version) <=> SeasonLineupGenerator::strength($a, $version));
                $roundPool[$gender] = array_slice($candidates, 0, $count * $perTeam + 2);
                // Returning Members must be available for their named showcase rounds.
                foreach ($returning as $index => $member) {
                    if ($gender === 'K' && $round >= ($index === 0 ? 6 : 9) && ! in_array($member['refId'], array_column($roundPool[$gender], 'refId'), true)) {
                        $roundPool[$gender][] = $member;
                    }
                }
            }
            $generator = new SeasonLineupGenerator($roundPool, ['M' => [], 'K' => []], new Randomizer(new Mt19937(2025 + $round)));
            $lineup = $generator->lineup(array_fill(0, $count, [
                'format' => SeasonLineupGenerator::FORMAT_13, 'men' => 6, 'women' => 4, 'youth' => false,
            ]), $version, []);

            $reserve = $round === 3 ? $generator->reserve($lineup, 2, $version) : null;
            if ($reserve !== null) {
                $lineup = $reserve['lineup'];
                $quarantined = $reserve['reserve']['refId'];
            }
            if ($round === 4) {
                // These exact Members/categories appeared on the same Team in round 3.
                $lineup[0] = $lineups[3][0];
            }
            $lineups[$round] = $lineup;
            $this->seedRound($historical, $teams, $lineup, $reserve, $generator);
        }
    }

    private function seedTeams(): array
    {
        return array_map(fn (CanonicalTournamentTier $tier, int $index) => DB::table('teams')->insertGetId([
            'name' => 'BC37 Amager '.($index + 1),
            'tier_id' => DB::table('tournament_tiers')->where('tier_name', $tier->value)->sole()->id,
            'clubhouse_id' => 1,
            'season_id' => 2025,
            'created_at' => now(), 'updated_at' => now(),
        ]), self::TIERS, array_keys(self::TIERS));
    }

    private function seedCancellations(array $rounds, array $returning, array $cancelled): void
    {
        $collector = DB::table('cancellation_collectors')->insertGetId([
            'user_id' => 1, 'clubhouse_id' => 1, 'email' => 'testing@gmail.com',
            'sharing_id' => str_pad('previewbc37afbud2025', 24, 'x'),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('cancellation_collector_clubs')->insert(['cancellation_collector_id' => $collector, 'club_id' => 1087]);
        foreach ([[$cancelled, [2], 'Eksempel: afbud'], [$returning[0], range(1, 5), 'Eksempel: skade'], [$returning[1], range(1, 8), 'Eksempel: skade']] as [$member, $numbers, $message]) {
            $id = DB::table('cancellations')->insertGetId([
                'refId' => $member['refId'], 'cancellation_collector_id' => $collector, 'message' => $message,
                'created_at' => now(), 'updated_at' => now(),
            ]);
            foreach ($rounds as $round) {
                if (in_array($round['round'], $numbers, true)) {
                    DB::table('cancellation_dates')->insert(['cancellation_id' => $id, 'date' => $round['game_date']]);
                }
            }
        }
    }

    private function seedRound(array $source, array $teams, array $lineup, ?array $reserve, SeasonLineupGenerator $generator): void
    {
        $round = $source['round'];
        $version = $source['version'];
        $id = self::roundId($round);
        DB::table('team_rounds')->insert([
            'id' => $id, 'name' => 'Runde '.$round.' · '.self::HINTS[$round],
            'user_id' => 1, 'clubhouse_id' => 1, 'season_id' => 2025,
            'round' => $round, 'game_date' => $source['game_date'], 'version' => $version,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $scenario = $round === 11 ? DB::table('team_round_scenarios')->insertGetId([
            'team_round_id' => $id, 'name' => 'Udkast · forkert rækkefølge i herredouble', 'is_official' => false,
            'created_at' => now(), 'updated_at' => now(),
        ]) : null;
        $draft = $scenario === null ? null : $generator->withOrderingError($lineup, 0, $version);

        foreach ($lineup as $index => $squad) {
            $paper = $index === 0 && in_array($round, [4, 6, 10], true);
            $squadId = DB::table('squads')->insertGetId([
                'playerLimit' => 10, 'team_round_id' => $id, 'team_id' => $teams[$index], 'order' => $index + 1,
                'name' => $paper ? 'Papirhold · kampen spilles ikke' : null,
                'is_paper_squad' => $paper,
                'playing_datetime' => $paper ? null : $source['game_date'].' 13:00:00',
                'version' => $version,
                'created_at' => now(), 'updated_at' => now(),
            ]);
            foreach ($squad['categories'] as $category) {
                $this->seedCategory($squadId, null, $category, $version);
            }
            if ($draft !== null) {
                foreach ($draft[$index]['categories'] as $category) {
                    $this->seedCategory($squadId, $scenario, $category, $version);
                }
            }
            if ($reserve !== null && $reserve['squad'] === $index) {
                DB::table('squad_reserves')->insert([
                    'squad_id' => $squadId,
                    'reserve_ref_id' => $reserve['reserve']['refId'], 'reserve_name' => $reserve['reserve']['name'], 'reserve_gender' => $reserve['reserve']['gender'],
                    'replaced_ref_id' => $reserve['replaced']['refId'], 'replaced_name' => $reserve['replaced']['name'], 'replaced_gender' => $reserve['replaced']['gender'],
                ]);
            }
        }
    }
}
