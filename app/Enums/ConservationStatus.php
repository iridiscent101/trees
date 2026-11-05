<?php

namespace App\Enums;

enum ConservationStatus: string
{
    case NotEvaluated = 'not_evaluated';
    case DataDeficient = 'data_deficient';
    case LeastConcern = 'least_concern';
    case NearThreatened = 'near_threatened';
    case Vulnerable = 'vulnerable';
    case Endangered = 'endangered';
    case CriticallyEndangered = 'critically_endangered';
    case ExtinctInTheWild = 'extinct_in_the_wild';
    case Extinct = 'extinct';
}
