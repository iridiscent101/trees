<?php

namespace App\Enums;

enum SurveyReviewStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case NeedsCorrection = 'needs_correction';
    case Rejected = 'rejected';
    case Converted = 'verified';
}
