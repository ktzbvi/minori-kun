<?php

namespace App\Enums;

enum PaymentState: string
{
    case Pending = 'pending';
    case RequiresAction = 'requires_action';
    case Succeeded = 'succeeded';
    case Failed = 'failed';
    case Refunded = 'refunded';
}
