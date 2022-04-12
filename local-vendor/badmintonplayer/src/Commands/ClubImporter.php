<?php

declare(strict_types=1);

namespace FlyCompany\BadmintonPlayer\Commands;

use App\Models\Club;
use Illuminate\Console\Command;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Support\Arr;

class ClubImporter extends Command
{

    protected $signature = 'badmintonplayer-api-import:club';

    protected $description = 'Import clubs';

    public function handle(FilesystemManager $filesystemManager): int
    {
        $clubs = $filesystemManager->disk()->get('clubs.csv');
        $parsedClubs = $this->getParsedClubs($clubs);

        foreach ($parsedClubs as $club) {
            Club::query()->updateOrCreate([
                'id' => $club['ClubId'],
            ], [
                'name1'             => $club['Name'],
                'badmintonPlayerId' => $club['ClubId'],
            ]);
            $this->info("Update/Creates {$club['Name']}");
        }

        $clubIds = Arr::pluck($parsedClubs, 'ClubId');
        Club::query()->whereNotIn('id', $clubIds)->delete();

        return 0;
    }

    /**
     * @param string $clubs
     * @return array
     */
    private function getParsedClubs(string $clubs): array
    {
        $parsedClubs = [];
        $explode = explode(PHP_EOL, $clubs);
        foreach ($explode as $club) {
            $trimmed = trim($club);
            if (!empty($trimmed)) {
                $parsedClubs[] = str_getcsv($trimmed);
            }
        }
        $headers = array_shift($parsedClubs);
        foreach ($parsedClubs as $index => $club) {
            $parsedClubs[$index] = array_combine($headers, $club);
        }
        return $parsedClubs;
    }

}
