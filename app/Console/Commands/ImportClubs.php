<?php

namespace App\Console\Commands;

use FlyCompany\Import\Club;
use FlyCompany\Import\Member;
use FlyCompany\Import\Ranking;
use Illuminate\Console\Command;

class ImportClubs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:clubs {path}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update clubs';

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
        $path = $this->argument('path');
        $content = file_get_contents($path);
        if($content === false){
            throw new \RuntimeException('Failed to load '.$path);
        }
        $data = simplexml_load_string($content);
        if($data === false){
            $this->error("Failed loading XML: ");
            foreach(libxml_get_errors() as $error) {
                $this->error($error->message);
            }
        }
        $ranking = Ranking::factory($data);

        return 0;
    }
}
