<?php
declare(strict_types = 1);

namespace App\Console\Commands;

use FlyCompany\Import\Util\Path;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class ImportDownloadRanking extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:download-ranklist {date : ranking to import format \'yyyy-mm-dd\'}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download ranklist';

    /**
     * Execute the console command.
     *
     * @return int
     * @throws \Exception
     */
    public function handle() : int
    {
        $dateStr = $this->argument('date');
        $date = Path::validateRankingDate($dateStr);

        $dateFormatted = $date->format('Y-m-d');
        $downloadUrl = 'http://badmintonplayer.dk/DBF/DownloadRankings?v=' . $dateFormatted;
        $this->line('Downloading ranking from ' . $downloadUrl);
        $filename = 'ranking-' . $dateFormatted . '.zip';
        $tempZip = tempnam(sys_get_temp_dir(), $filename);
        $client = new Client();
        $client->get($downloadUrl, ['sink' => $tempZip]);

        $tmpDir = '/tmp';
        $this->line('Unzipping ' . $tempZip . ' to ' . $tmpDir);
        $zip = new ZipArchive();
        $res = $zip->open($tempZip);
        if ($res === true) {
            $zip->extractTo($tmpDir);
            $zip->close();
        } else {
            $this->output->writeln('Failed unzipping');
            return 0;
        }
        $filePath = Path::getRankingPath($dateFormatted);
        $this->line('Saving ' . $filePath);
        Storage::put( $filePath, file_get_contents('/tmp/DBF.stm'));

        Cache::put('last-ranklist', date('dmy'));
        return 0;
    }
}
