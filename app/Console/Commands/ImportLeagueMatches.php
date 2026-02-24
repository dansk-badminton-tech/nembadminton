<?php

namespace App\Console\Commands;

use App\Models\AgeGroup;
use App\Models\Club;
use App\Models\Division;
use App\Models\LeagueMatch;
use App\Models\LeagueTeam;
use App\Models\Season;
use App\Models\Venue;
use FlyCompany\BadmintonPlayerAPI\BadmintonPlayerAPI;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportLeagueMatches extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'badmintonplayer-api-import:league-matches';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import league match data from JSON file into normalized database structure';

    /**
     * Execute the console command.
     */
    public function handle(BadmintonPlayerAPI $badmintonPlayerAPI)
    {
        $matches = $badmintonPlayerAPI->getCurrentLeagueMatches();

        $this->info("Found " . count($matches) . " matches to import");

        $json = json_encode($matches, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);

        $matchData = json_decode($json, true);

    
        DB::beginTransaction();

        try {
            $this->importData($matchData);
            DB::commit();
            $this->info('Successfully imported all league match data!');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Import failed: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    private function importData(array $matchData): void
    {
        $progress = $this->output->createProgressBar(count($matchData));
        $progress->start();

        // Track created entities to avoid duplicates
        $seasons = [];
        $divisions = [];
        $ageGroups = [];
        $venues = [];
        $leagueTeams = [];
        $processedMatches = [];

        foreach ($matchData as $match) {
            // Skip duplicate matches (same external match ID)
            if (isset($processedMatches[$match['leagueMatchId']])) {
                $progress->advance();
                continue;
            }

            // Create/get season
            if (!isset($seasons[$match['seasonId']])) {
                $seasons[$match['seasonId']] = $this->createOrGetSeason($match['seasonId']);
            }

            // Create/get division
            $divisionKey = $match['divisionName'];
            if (!isset($divisions[$divisionKey])) {
                $divisions[$divisionKey] = $this->createOrGetDivision($match['divisionName']);
            }

            // Create/get age group
            $ageGroupKey = $match['ageGroupId'];
            if (!isset($ageGroups[$ageGroupKey])) {
                $ageGroups[$ageGroupKey] = $this->createOrGetAgeGroup($match['ageGroupId']);
            }

            // Create/get venue (handle null venues)
            $venueId = null;
            if (!empty($match['venueName'])) {
                $venueKey = $match['venueName'];
                if (!isset($venues[$venueKey])) {
                    $venues[$venueKey] = $this->createOrGetVenue($match['venueName']);
                }
                $venueId = $venues[$venueKey]->id;
            }

            $teamName1 = $match['teamName1'] . ' ' . $match['groupName'];
            // Create/get teams
            $team1Key = $this->getTeamKey($teamName1, $match['clubId1']);
            if (!isset($leagueTeams[$team1Key])) {
                $leagueTeams[$team1Key] = $this->createOrGetLeagueTeam(
                    $teamName1,
                    $match['clubId1'],
                    $divisions[$divisionKey]->id
                );
            }

            $teamName2 = $match['teamName2'] . ' ' . $match['groupName'];
            $team2Key = $this->getTeamKey($teamName2, $match['clubId2']);
            if (!isset($leagueTeams[$team2Key])) {
                $leagueTeams[$team2Key] = $this->createOrGetLeagueTeam(
                    $teamName2,
                    $match['clubId2'],
                    $divisions[$divisionKey]->id
                );
            }

            // Create match
            $this->createLeagueMatch($match, [
                'season_id' => $seasons[$match['seasonId']]->id,
                'division_id' => $divisions[$divisionKey]->id,
                'age_group_id' => $ageGroups[$ageGroupKey]->id,
                'venue_id' => $venueId,
                'team1_id' => $leagueTeams[$team1Key]->id,
                'team2_id' => $leagueTeams[$team2Key]->id,
            ]);

            $processedMatches[$match['leagueMatchId']] = true;
            $progress->advance();
        }

        $progress->finish();
        $this->newLine();
    }

    private function createOrGetSeason(int $year): Season
    {
        return Season::firstOrCreate(
            ['year' => $year],
            ['name' => "Season {$year}"]
        );
    }

    private function createOrGetDivision(string $name): Division
    {
        $code = $this->generateDivisionCode($name);
        $order = $this->getDivisionOrder($name);
        
        return Division::firstOrCreate(
            ['code' => $code],
            ['name' => trim($name), 'display_order' => $order]
        );
    }

    private function createOrGetAgeGroup(string $ageGroupId): AgeGroup
    {
        $name = $this->getAgeGroupName($ageGroupId);
        
        return AgeGroup::firstOrCreate(
            ['code' => $ageGroupId],
            ['name' => $name]
        );
    }

    private function createOrGetVenue(string $name): Venue
    {
        return Venue::firstOrCreate(
            ['name' => $name],
            ['name' => $name]
        );
    }

    private function createOrGetLeagueTeam(string $name, ?int $clubId, int $divisionId): LeagueTeam
    {
        $attributes = [
            'name' => $name,
            'division_id' => $divisionId,
            'created_by_system' => true, // Teams imported from external system are system-created
        ];
        
        // If we have a club ID, try to link it
        if ($clubId && Club::find($clubId)) {
            $attributes['club_id'] = $clubId;
        }

        // Try to find existing team by name, club
        $existingTeam = null;
        
        if ($clubId) {
            $existingTeam = LeagueTeam::where('name', $name)
                ->where('club_id', $clubId)
                ->where('division_id', $divisionId)
                ->where('created_by_system', true)
                ->first();
        }
        
        if (!$existingTeam) {
            $existingTeam = LeagueTeam::where('name', $name)
                ->whereNull('club_id')
                ->where('division_id', $divisionId)
                ->where('created_by_system', true)
                ->first();
        }

        // If we still don't have a team, try to find by name, division and system-created
        if (!$existingTeam) {
            $existingTeam = LeagueTeam::where('name', $name)
                ->where('division_id', $divisionId)
                ->where('created_by_system', true)
                ->first();
        }

        return $existingTeam ?: LeagueTeam::create($attributes);
    }



    private function createLeagueMatch(array $match, array $relationships): void
    {
        // Check if match already exists
        $existingMatch = LeagueMatch::where('external_match_id', $match['leagueMatchId'])->first();
        
        if ($existingMatch) {
            return; // Skip duplicate
        }

        LeagueMatch::create([
            'external_match_id' => $match['leagueMatchId'],
            'division_id' => $relationships['division_id'],
            'age_group_id' => $relationships['age_group_id'],
            'team1_id' => $relationships['team1_id'],
            'team2_id' => $relationships['team2_id'],
            'venue_id' => $relationships['venue_id'],
            'season_id' => $relationships['season_id'],
            'created_by_system' => true, // Matches imported from external system are system-created
            'match_time' => $match['matchTime'],
            'score1' => $match['score1'],
            'score2' => $match['score2'],
        ]);
    }

    private function getTeamKey(string $teamName, ?int $clubId): string
    {
        if ($clubId) {
            return "club_{$clubId}_{$teamName}";
        }
        
        return "team_{$teamName}";
    }

    private function generateDivisionCode(string $name): string
    {
        $name = trim($name);
        
        $codeMap = [
            'Badmintonligaen' => 'LIGA',
            'Danmarksserien' => 'DANMARK',
            '1. division' => 'DIV1',
            '2. division' => 'DIV2',
            '3. division' => 'DIV3',
        ];
        
        return $codeMap[$name] ?? strtoupper(str_replace(' ', '_', $name));
    }

    private function getDivisionOrder(string $name): int
    {
        $name = trim($name);
        
        $orderMap = [
            'Badmintonligaen' => 1,
            '1. division' => 2,
            '2. division' => 3,
            '3. division' => 4,
            'Danmarksserien' => 5,
        ];
        
        return $orderMap[$name] ?? 99;
    }

    private function getAgeGroupName(string $code): string
    {
        $nameMap = [
            '1' => 'Senior',
            '2' => 'Junior',
            '3' => 'Youth',
        ];
        
        return $nameMap[$code] ?? "Age Group {$code}";
    }
}
