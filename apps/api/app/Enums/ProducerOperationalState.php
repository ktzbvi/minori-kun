<?php

namespace App\Enums;

enum ProducerOperationalState: string
{
    case Onboarding = 'onboarding';
    case Active = 'active';
    case Suspended = 'suspended';
}
