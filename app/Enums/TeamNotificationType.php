<?php

namespace App\Enums;

enum TeamNotificationType : string
{
    case TEAM_PUBLISH = 'team_publish';
    case TEAM_UPDATED = 'team_updated';
}
