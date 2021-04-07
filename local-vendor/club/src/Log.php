<?php
declare(strict_types = 1);


namespace FlyCompany\Club;

class Log
{

    public static function createLog(int $clubId, string $log, string $component)
    {
        \App\Models\Log::query()->create([
            'club_id'   => $clubId,
            'log'       => $log,
            'component' => $component,
        ]);
    }

}
