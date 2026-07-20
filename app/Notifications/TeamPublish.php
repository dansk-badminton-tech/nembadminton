<?php

namespace App\Notifications;

class TeamPublish extends TeamRoundNotification
{
    protected function getTitle() : string
    {
        return 'Holdrunden er klar på ' . $this->team->name;
    }

    protected function getBody() : string
    {
        return 'Klik her for at se holdet';
    }
}
