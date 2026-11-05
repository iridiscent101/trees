<?php

namespace Tests\Feature;

use App\Actions\ReviewSurveyPoint;
use App\Enums\PermissionName;
use App\Enums\SurveyReviewStatus;
use App\Models\CampusArea;
use App\Models\TreeSurveyPoint;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SurveyReviewWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('The survey workflow test requires the PDO SQLite extension.');
        }

        parent::setUp();
    }

    public function test_an_authorized_reviewer_can_approve_a_ready_survey_record(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $reviewer = User::factory()->create();
        $reviewer->givePermissionTo(PermissionName::ManageVerifications->value);
        $campusArea = CampusArea::factory()->create();
        $surveyPoint = $this->surveyPoint();

        $approvedPoint = app(ReviewSurveyPoint::class)->approve($surveyPoint, [
            'campus_area_id' => $campusArea->getKey(),
            'approved_species_name' => 'Narra',
            'review_notes' => 'Coordinate and identity checked against the campus map.',
        ], $reviewer);

        $this->assertSame(SurveyReviewStatus::Approved, $approvedPoint->review_status);
        $this->assertSame($reviewer->getKey(), $approvedPoint->reviewed_by);
        $this->assertDatabaseHas('activity_log', [
            'event' => 'survey.approved',
            'subject_id' => $surveyPoint->getKey(),
        ]);
    }

    public function test_approval_is_blocked_for_invalid_coordinates(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $reviewer = User::factory()->create();
        $reviewer->givePermissionTo(PermissionName::ManageVerifications->value);
        $campusArea = CampusArea::factory()->create();
        $surveyPoint = $this->surveyPoint(['coordinate_status' => 'invalid']);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Coordinates must be marked valid.');

        app(ReviewSurveyPoint::class)->approve($surveyPoint, [
            'campus_area_id' => $campusArea->getKey(),
            'approved_species_name' => 'Narra',
        ], $reviewer);
    }

    /** @param array<string, mixed> $overrides */
    private function surveyPoint(array $overrides = []): TreeSurveyPoint
    {
        return TreeSurveyPoint::query()->create(array_merge([
            'source_file' => 'test.xlsx',
            'source_row' => 2,
            'raw_species_name' => 'Narra 12',
            'easting' => 301994,
            'northing' => 1828890,
            'height_m' => 5.5,
            'latitude' => 16.480696,
            'longitude' => 121.140771,
            'coordinate_status' => 'valid',
        ], $overrides));
    }
}
