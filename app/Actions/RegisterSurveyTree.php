<?php

namespace App\Actions;

use App\Enums\TreeStatus;
use App\Enums\SurveyReviewStatus;
use App\Enums\VerificationStatus;
use App\Models\Tree;
use App\Models\TreePhoto;
use App\Models\TreeSurveyPoint;
use App\Models\User;
use App\Support\Survey\SurveyReviewReadiness;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class RegisterSurveyTree
{
    public function __construct(private SurveyReviewReadiness $readiness) {}

    /** @param array<string, mixed> $data */
    public function handle(TreeSurveyPoint $surveyPoint, array $data, User $user): Tree
    {
        Gate::forUser($user)->authorize('update', $surveyPoint);

        return DB::transaction(function () use ($surveyPoint, $data, $user): Tree {
            $lockedPoint = TreeSurveyPoint::query()->lockForUpdate()->findOrFail($surveyPoint->getKey());

            if ($lockedPoint->registered_tree_id !== null || $lockedPoint->review_status === SurveyReviewStatus::Converted) {
                throw new DomainException('This survey record has already been registered.');
            }

            if ($lockedPoint->review_status !== SurveyReviewStatus::Approved) {
                throw new DomainException('This survey record must be approved before it can be registered.');
            }

            $this->readiness->assertReady($lockedPoint);

            $tree = Tree::query()->create([
                'species_id' => $data['species_id'],
                'campus_area_id' => $data['campus_area_id'],
                'descriptive_location' => $data['descriptive_location'],
                'nearby_landmark' => $data['nearby_landmark'] ?? null,
                'latitude' => $lockedPoint->latitude,
                'longitude' => $lockedPoint->longitude,
                'trunk_diameter_cm' => $data['trunk_diameter_cm'] ?? null,
                'estimated_height_m' => $data['estimated_height_m'] ?? $lockedPoint->height_m,
                'estimated_age_years' => $data['estimated_age_years'] ?? null,
                'current_condition' => $data['current_condition'],
                'status' => TreeStatus::Existing,
                'verification_status' => VerificationStatus::Verified,
                'public_notes' => $data['public_notes'] ?? null,
                'internal_remarks' => $data['internal_remarks'] ?? null,
                'recorded_by' => $user->getKey(),
                'verified_by' => $user->getKey(),
                'verified_at' => now(),
                'published_at' => now(),
            ]);

            if (filled($data['photo'] ?? null)) {
                TreePhoto::query()->create([
                    'tree_id' => $tree->getKey(),
                    'type' => 'main',
                    'disk' => 'public',
                    'path' => $data['photo'],
                    'alt_text' => $tree->tree_code.' tree photograph',
                    'is_primary' => true,
                    'uploaded_by' => $user->getKey(),
                ]);
            }

            $lockedPoint->update([
                'review_status' => SurveyReviewStatus::Converted,
                'registered_tree_id' => $tree->getKey(),
                'reviewed_by' => $user->getKey(),
                'reviewed_at' => now(),
                'review_notes' => $data['review_notes'] ?? null,
            ]);

            activity('survey-records')
                ->causedBy($user)
                ->performedOn($tree)
                ->event('survey.registered')
                ->withProperties([
                    'survey_point_id' => $lockedPoint->getKey(),
                    'old' => ['review_status' => SurveyReviewStatus::Approved->value],
                    'attributes' => [
                        'tree_id' => $tree->getKey(),
                        'tree_code' => $tree->tree_code,
                        'review_status' => SurveyReviewStatus::Converted->value,
                    ],
                ])
                ->log("Survey row {$lockedPoint->source_row} was registered as {$tree->tree_code}.");

            return $tree;
        });
    }
}
