<?php

namespace App\Enums;

enum AccountState: string
{
    case Active = 'active';
    case Suspended = 'suspended';
    case Closed = 'closed';
}
