<?php
declare(strict_types = 1);

namespace App\Console\Commands;

use FlyCompany\Import\Util\Path;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class ImportDownloadRanking extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:download-ranking {date : ranking to import format \'yyyy-mm-dd\'} {--import-members : queue import members job}';

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
        Path::validateRankingDate($dateStr);
        $filePath = Path::getRankingPath($dateStr);

        if (Storage::exists($filePath)) {
            $this->line($filePath . ' already downloaded');

            return 0;
        }

        $downloadUrl = 'http://badmintonplayer.dk/DBF/DownloadRankings?v=' . $dateStr;
        $this->line('Downloading ranking from ' . $downloadUrl);
        $filename = 'ranking-' . $dateStr . '.zip';
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
        $this->line('Saving ' . $filePath);
        Storage::put($filePath, file_get_contents('/tmp/DBF.stm'));

        $this->line('Queuing import:update-members');
        Artisan::queue('import:update-members', [
            'date' => $dateStr,
        ]);

        return 0;
    }
}
