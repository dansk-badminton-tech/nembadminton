<?php

namespace App\Jobs;

use App\Import\Import;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportClubs implements ShouldQueue
{

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private string $date;

    private array  $clubIds;

    /**
     * Create a new job instance.
     *
     * @param string $date
     * @param array  $clubIds
     */
    public function __construct(string $date, array $clubIds)
    {
        $this->date = $date;
        $this->clubIds = $clubIds;
    }

    /**
     * Execute the job.
     *
     * @param Import $import
     *
     * @return void
     */
    public function handle(Import $import) : void
    {
        $import->importClubs($this->date, $this->clubIds);
    }
}
