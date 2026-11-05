<?php

namespace App\Enums;

enum TreeStatus: string
{
    case Existing = 'existing';
    case Removed = 'removed';
    case Missing = 'missing';
    case Archived = 'archived';
}
