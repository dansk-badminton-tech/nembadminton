<?php

namespace App\Notifications;

class TeamUpdated extends TeamRoundNotification
{
    protected function getTitle() : string
    {
        return 'Ny opdatering på ' . $this->team->name;
    }

    protected function getBody() : string
    {
        return 'Klik her for at se holdet';
    }
}
