<?php

namespace FlyCompany\Members\Enums;

enum Category: string
{
    case MENS_SINGLE = 'HS';
    case WOMENS_SINGLE = 'DS';
    case MENS_DOUBLE = 'HD';
    case WOMENS_DOUBLE = 'DD';
    case MEN_MIX = 'MxH';
    case WOMEN_MIX = 'MxD';
}
