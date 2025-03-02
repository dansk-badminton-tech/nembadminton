<?php


namespace App\Enums;

enum Role : string
{

    case CLUB_ADMIN = 'club-admin';
    case COACH = 'coach';
    case SUPERADMIN = 'super-admin';
    case PLAYER = 'player';

}
