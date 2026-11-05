<?php

namespace App\Actions;

use App\Enums\SurveyReviewStatus;
use App\Models\TreeSurveyPoint;
use App\Models\User;
use App\Support\Survey\SurveyReviewReadiness;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ReviewSurveyPoint
{
    public function __construct(private SurveyReviewReadiness $readiness) {}

    /** @param array{campus_area_id: int|string, approved_species_name: string, review_notes?: ?string} $data */
    public function approve(TreeSurveyPoint $surveyPoint, array $data, User $reviewer): TreeSurveyPoint
    {
        Gate::forUser($reviewer)->authorize('update', $surveyPoint);

        return DB::transaction(function () use ($surveyPoint, $data, $reviewer): TreeSurveyPoint {
            $lockedPoint = TreeSurveyPoint::query()->lockForUpdate()->findOrFail($surveyPoint->getKey());

            if (! in_array($lockedPoint->review_status, [SurveyReviewStatus::Pending, SurveyReviewStatus::NeedsCorrection], true)) {
                throw new DomainException('Only pending records or records needing correction can be approved.');
            }

            $lockedPoint->fill([
                'campus_area_id' => $data['campus_area_id'],
                'approved_species_name' => trim($data['approved_species_name']),
                'review_notes' => $data['review_notes'] ?? null,
            ]);

            $this->readiness->assertReady($lockedPoint);

            $lockedPoint->update([
                'review_status' => SurveyReviewStatus::Approved,
                'reviewed_by' => $reviewer->getKey(),
                'reviewed_at' => now(),
            ]);

            activity('survey-records')
                ->causedBy($reviewer)
                ->performedOn($lockedPoint)
                ->event('survey.approved')
                ->withProperties([
                    'campus_area_id' => $lockedPoint->campus_area_id,
                    'approved_species_name' => $lockedPoint->approved_species_name,
                ])
                ->log("Survey row {$lockedPoint->source_row} was approved for registration.");

            return $lockedPoint;
        });
    }

    public function assignCampusArea(TreeSurveyPoint $surveyPoint, int $campusAreaId, User $reviewer): TreeSurveyPoint
    {
        Gate::forUser($reviewer)->authorize('update', $surveyPoint);

        $surveyPoint->update(['campus_area_id' => $campusAreaId]);

        activity('survey-records')
            ->causedBy($reviewer)
            ->performedOn($surveyPoint)
            ->event('survey.area-assigned')
            ->withProperties(['campus_area_id' => $campusAreaId])
            ->log("A campus area was assigned to survey row {$surveyPoint->source_row}.");

        return $surveyPoint;
    }

    public function needsCorrection(TreeSurveyPoint $surveyPoint, string $notes, User $reviewer): TreeSurveyPoint
    {
        return $this->mark($surveyPoint, SurveyReviewStatus::NeedsCorrection, $notes, $reviewer);
    }

    public function reject(TreeSurveyPoint $surveyPoint, string $reason, User $reviewer): TreeSurveyPoint
    {
        return $this->mark($surveyPoint, SurveyReviewStatus::Rejected, $reason, $reviewer);
    }

    private function mark(TreeSurveyPoint $surveyPoint, SurveyReviewStatus $status, string $notes, User $reviewer): TreeSurveyPoint
    {
        Gate::forUser($reviewer)->authorize('update', $surveyPoint);

        if ($surveyPoint->registered_tree_id !== null || $surveyPoint->review_status === SurveyReviewStatus::Converted) {
            throw new DomainException('A converted survey record can no longer be reviewed.');
        }

        $surveyPoint->update([
            'review_status' => $status,
            'reviewed_by' => $reviewer->getKey(),
            'reviewed_at' => now(),
            'review_notes' => trim($notes),
        ]);

        activity('survey-records')
            ->causedBy($reviewer)
            ->performedOn($surveyPoint)
            ->event('survey.'.$status->value)
            ->withProperties(['review_notes' => $surveyPoint->review_notes])
            ->log("Survey row {$surveyPoint->source_row} was marked {$status->value}.");

        return $surveyPoint;
    }
}
