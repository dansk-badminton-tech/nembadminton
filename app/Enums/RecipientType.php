<?php

namespace App\Enums;

enum RecipientType : string
{

    case PLATFORM_USERS = 'PLATFORM_USERS';
    case MANUAL_EMAILS = 'MANUAL_EMAILS';
    case TEST_SELF = 'TEST_SELF';

}
