<?php

namespace App\Enums;

enum RefundState: string
{
    case None = 'none';
    case Pending = 'pending';
    case Partial = 'partial';
    case Refunded = 'refunded';
    case Failed = 'failed';
}
