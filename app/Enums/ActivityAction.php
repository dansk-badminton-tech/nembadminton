<?php

namespace App\Enums;

enum ActivityAction : string
{
    case TEAM_PUBLISH = 'team_publish';
    case TEST_EMAIL_SENT = 'test_email_sent';
    case TEAM_UPDATED = 'team_updated';
}
