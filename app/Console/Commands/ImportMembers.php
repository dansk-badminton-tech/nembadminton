<?php

namespace App\Console\Commands;

use FlyCompany\Import\Ranking;
use FlyCompany\Import\Util\Path;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ImportMembers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:members {date : ranking to import format \'ddmmyy\'}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import members to club';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

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
        $ranking = Ranking::factory($data);

        foreach ($ranking->getClubs() as $club) {
            $this->info('Updating '.$club->getName1());
            if (!is_numeric($club->getId())) {
                continue;
            }
            /** @var \App\Models\Club $clubModel */
            $clubModel = \App\Models\Club::query()->where(['id' => $club->getId()])->firstOrFail();
            $syncIds = [];
            $this->info('Adding members:');
            foreach ($club->getMembers() as $member) {
                $this->output->write('.');
                $memberModel = \App\Models\Member::query()->where('refId', $member->getId())->first();
                if($memberModel !== null){
                    $memberModel->update([
                        'name'     => $member->getName(),
                        'gender'   => $member->getGender(),
                        'birthday' => $member->getBirthday(),
                    ]);
                }else{
                    $memberModel = \App\Models\Member::create([
                        'refId' => $member->getId(),
                        'name'     => $member->getName(),
                        'gender'   => $member->getGender(),
                        'birthday' => $member->getBirthday(),
                    ]);
                }
                $syncIds[] = $memberModel->id;
            }
            $clubModel->members()->sync($syncIds);
            $this->line('');
        }
        return 0;
    }
}
