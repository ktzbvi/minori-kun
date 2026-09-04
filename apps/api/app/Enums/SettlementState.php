<?php

namespace App\Enums;

enum SettlementState: string
{
    case Provisional = 'provisional';
    case Finalized = 'finalized';
}
