<?php

namespace App\Enums;

enum ScreeningState: string
{
    case InReview = 'in_review';
    case Passed = 'passed';
    case Declined = 'declined';
}
