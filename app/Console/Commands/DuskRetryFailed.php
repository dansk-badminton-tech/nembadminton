<?php

namespace App\Console\Commands;

use App\Console\DuskFailureReport;
use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use UnexpectedValueException;

class DuskRetryFailed extends Command
{
    protected $signature = 'dusk:retry-failed {report : Path to the previous Dusk run\'s JUnit report}';

    protected $description = 'Retry only the failed tests in a Dusk JUnit report';

    public function handle(): int
    {
        $path = $this->argument('report');
        $xml = is_file($path) && is_readable($path) ? file_get_contents($path) : false;

        if ($xml === false) {
            $this->error("Cannot read Dusk JUnit report: {$path}");

            return self::FAILURE;
        }

        try {
            $filter = DuskFailureReport::failedTestFilter($xml);
        } catch (UnexpectedValueException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        if ($filter === null) {
            $this->info('All tests in the Dusk JUnit report passed; nothing to retry.');

            return self::SUCCESS;
        }

        $this->info('Retrying failed Dusk tests...');

        $process = new Process([PHP_BINARY, base_path('artisan'), 'dusk', '--without-tty', '--filter='.$filter], base_path());
        $process->setTimeout(null);

        return $process->run(function ($type, $output) {
            $this->output->write($output);
        });
    }
}
