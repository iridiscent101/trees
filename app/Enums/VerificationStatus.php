<?php

namespace App\Enums;

enum VerificationStatus: string
{
    case Pending = 'pending';
    case Verified = 'verified';
    case NeedsCorrection = 'needs_correction';
    case Rejected = 'rejected';
}
