<?php

namespace App\Actions;

use App\Enums\VerificationStatus;
use App\Models\TreeMonitoring;
use App\Models\TreeVerification;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ReviewTreeMonitoring
{
    public function handle(TreeMonitoring $monitoring, VerificationStatus $status, User $verifier, ?string $remarks = null, bool $isPublic = false): TreeMonitoring
    {
        if ($monitoring->verification_status === VerificationStatus::Verified) {
            throw new InvalidArgumentException('A verified monitoring record cannot be reviewed again.');
        }

        return DB::transaction(function () use ($monitoring, $status, $verifier, $remarks, $isPublic): TreeMonitoring {
            $verified = $status === VerificationStatus::Verified;
            $monitoring->update([
                'verification_status' => $status, 'verified_by' => $verified ? $verifier->getKey() : null,
                'verified_at' => $verified ? now() : null, 'is_public' => $verified && $isPublic,
            ]);
            TreeVerification::query()->create([
                'tree_id' => $monitoring->tree_id, 'tree_monitoring_id' => $monitoring->getKey(),
                'verifier_id' => $verifier->getKey(), 'status' => $status, 'remarks' => $remarks,
                'reviewed_fields' => ['condition', 'trunk_diameter_cm', 'estimated_height_m', 'remarks', 'public_notes'],
                'decided_at' => now(),
            ]);

            if ($verified) {
                $monitoring->tree->update(Arr::whereNotNull([
                    'current_condition' => $monitoring->condition,
                    'trunk_diameter_cm' => $monitoring->trunk_diameter_cm,
                    'estimated_height_m' => $monitoring->estimated_height_m,
                ]));
            }

            activity('monitoring')->causedBy($verifier)->performedOn($monitoring)
                ->withProperties(['status' => $status->value, 'is_public' => $monitoring->is_public])
                ->log('Reviewed a tree monitoring inspection.');

            return $monitoring->fresh(['tree', 'verifier', 'verifications']);
        });
    }
}
