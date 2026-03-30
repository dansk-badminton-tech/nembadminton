<?php

declare(strict_types=1);

namespace FlyCompany\BadmintonPlayer\Commands;

use FlyCompany\BadmintonPlayer\Jobs\ImportMembers;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class MembersImporter extends Command
{

    protected $signature = 'badmintonplayer-api-import:members {club-id* : BadmintonPlayer club id} {--sync}';

    protected $description = 'Import members for a club';

    public function handle(): int
    {
        $clubIds = array_map('intval', $this->argument('club-id'));
        if ($this->option('sync')) {
            ImportMembers::dispatchSync($clubIds);
        } else {
            ImportMembers::dispatch($clubIds);
        }

        return 0;
    }

}
