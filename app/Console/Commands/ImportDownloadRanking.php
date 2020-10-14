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
    protected $signature = 'import:download-ranklist';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle() : int
    {
        $downloadUrl = 'http://badmintonplayer.dk/DBF/DownloadRankings';
        $this->line('Downloading ranking from '.$downloadUrl);
        $filename = 'ranking-'.date('dmy').'.zip';
        $tempZip = tempnam(sys_get_temp_dir(), $filename);
        $client = new Client();
        $client->get($downloadUrl, ['sink' => $tempZip]);

        $tmpDir = '/tmp';
        $this->line('Unzipping '.$tempZip.' to '.$tmpDir);
        $zip = new ZipArchive();
        $res = $zip->open($tempZip);
        if ($res === true) {
            $zip->extractTo($tmpDir);
            $zip->close();
        } else {
            $this->output->writeln('Failed unzipping');
            return 0;
        }
        $filePath = Path::getRankingPath(date('dmy'));
        $this->line('Saving '.$filePath);
        Storage::put( $filePath, file_get_contents('/tmp/DBF.stm'));

        Cache::put('last-ranklist', date('dmy'));
        return 0;
    }
}
