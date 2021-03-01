<?php


namespace App\Console\Commands;

use DateTime;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class ImportSeason extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:season {year : ranking to import format \'yyyy\'}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import a hole season';

    /**
     * Execute the console command.
     *
     * @return int
     * @throws \Exception
     */
    public function handle()
    {
        $year = (int)$this->argument('year');
        $dates = array_merge($this->printWeekDay('monday', $year), $this->printWeekDay('wednesday', $year), $this->printWeekDay('friday', $year));

        foreach ($dates as $date) {
            $this->line('Queuing ' . $date);
            Artisan::queue('import:download-ranking', [
                'date'             => $date,
                '--import-members' => true,
            ]);
        }

        return 0;
    }

    private function printWeekDay(string $weekDay, int $year) : array
    {
        $dates = [];
        $weekDayDate = strtotime($weekDay, strtotime('01.08.' . $year));
        $date = new DateTime("@$weekDayDate");
        $nextYear = $year + 1;
        $seasonEnd = new DateTime('01.05.' . $nextYear);
        $now = new DateTime();
        while ($date < $seasonEnd && $date <= $now) {
            $dates[] = $date->format('Y-m-d');
            $date->modify('+1 week');
        }

        return $dates;
    }
}
