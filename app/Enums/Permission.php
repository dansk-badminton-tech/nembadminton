<?php

namespace App\Enums;

enum Permission: string
{
    case VIEW_TEAMROUNDS = 'view teamrounds';
    case CREATE_TEAMROUNDS = 'create teamrounds';
    case EDIT_TEAMROUNDS = 'edit teamrounds';
    case DELETE_TEAMROUNDS = 'delete teamrounds';

    case VIEW_CANCELLATIONS_COLLECTORS = 'view cancellations collectors';
    case CREATE_CANCELLATIONS_COLLECTORS = 'create cancellations collectors';
    case EDIT_CANCELLATIONS_COLLECTORS = 'edit cancellations collectors';
    case DELETE_CANCELLATIONS_COLLECTORS = 'delete cancellations collectors';

    case VIEW_CLUBHOUSE = 'view clubhouse';
    case EDIT_CLUBHOUSE = 'edit clubhouse';

    case VIEW_MEMBERS = 'view members';
    case EDIT_MEMBERS = 'edit members';
    case CREATE_MEMBERS = 'create members';
    case DELETE_MEMBERS = 'delete members';

    case VIEW_TEAMS = 'view teams';
}
