<?php

namespace App\Enums;

enum PayoutState: string
{
    case Scheduled = 'scheduled';
    case CarryForward = 'carry_forward';
    case Processing = 'processing';
    case Paid = 'paid';
    case Failed = 'failed';
}
