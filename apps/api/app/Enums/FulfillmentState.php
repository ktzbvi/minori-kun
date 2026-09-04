<?php

namespace App\Enums;

enum FulfillmentState: string
{
    case Received = 'received';
    case Processing = 'processing';
    case Shipped = 'shipped';
}
