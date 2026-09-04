<?php

namespace App\Enums;

enum ProductPublicationState: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Unpublished = 'unpublished';
}
