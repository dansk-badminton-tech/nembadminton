<?php
declare(strict_types = 1);


namespace App\Jobs;

use App\Import\Import;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportMembers implements ShouldQueue
{

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private array  $clubIds;

    private string $date;

    /**
     * Create a new job instance.
     *
     * @param string $date
     * @param array  $clubIds
     */
    public function __construct(string $date, array $clubIds)
    {
        $this->clubIds = $clubIds;
        $this->date = $date;
    }

    /**
     * Execute the job.
     *
     * @param Import $import
     *
     * @return void
     */
    public function handle(Import $import)
    {
        $import->importMembers($this->date, $this->clubIds, true);
    }
}
