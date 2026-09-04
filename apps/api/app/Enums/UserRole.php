<?php

namespace App\Enums;

enum UserRole: string
{
    case Buyer = 'buyer';
    case Producer = 'producer';
    case Admin = 'admin';
}
