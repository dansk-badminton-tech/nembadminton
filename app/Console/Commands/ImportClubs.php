<?php
declare(strict_types = 1);

namespace App\Console\Commands;

use FlyCompany\Import\Ranking;
use FlyCompany\Import\Util\Path;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ImportClubs extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:clubs {date : ranking to import format \'ddmmyy\'}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update clubs';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $date = $this->argument('date');
        $path = Path::getRankingPath($date);

        $this->info('Loading ' . $path);
        $data = XMLHelper::loadXML($path, Storage::disk());
        $this->info('Mapping to objects');
        $ranking = Ranking::factoryClubWithoutMembers($data);

        foreach ($ranking->getClubs() as $club) {
            $this->info('Updating '.$club->getName1());
            if (!is_numeric($club->getId())) {
                continue;
            }
            \App\Models\Club::updateOrCreate([
                'id' => $club->getId(),
            ], [
                'name1'    => $club->getName1(),
                'name2'    => $club->getName2(),
                'address'  => $club->getAddress(),
                'zipCode'  => $club->getZipCode(),
                'city'     => $club->getCity(),
                'email'    => $club->getEmail(),
                'memberOf' => $club->getMemberOf(),
                'union'    => $club->getUnion(),
            ]);
        }

        return 0;
    }
}
