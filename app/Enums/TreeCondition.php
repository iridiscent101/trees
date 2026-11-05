<?php

namespace App\Enums;

enum TreeCondition: string
{
    case Healthy = 'healthy';
    case Fair = 'fair';
    case Poor = 'poor';
    case Damaged = 'damaged';
    case Diseased = 'diseased';
    case Dead = 'dead';
    case Other = 'other';
}
