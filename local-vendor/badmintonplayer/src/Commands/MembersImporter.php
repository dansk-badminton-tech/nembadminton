<?php

declare(strict_types=1);

namespace FlyCompany\BadmintonPlayer\Commands;

use FlyCompany\BadmintonPlayer\Jobs\ImportMembers;
use FlyCompany\BadmintonPlayerAPI\BadmintonPlayerAPI;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class MembersImporter extends Command
{

    protected $signature = 'badmintonplayer-api-import:members {club-id* : BadmintonPlayer club id}';

    protected $description = 'Import members for a club';

    public function handle(): int
    {
        ImportMembers::dispatch(array_map('intval', $this->argument('club-id')));

        return 0;
    }

}
