<?php

namespace Tests\Feature;

use App\Actions\RegisterSurveyTree;
use App\Enums\PermissionName;
use App\Enums\SurveyReviewStatus;
use App\Enums\TreeCondition;
use App\Enums\VerificationStatus;
use App\Models\CampusArea;
use App\Models\Species;
use App\Models\TreeSurveyPoint;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterSurveyTreeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('The survey workflow test requires the PDO SQLite extension.');
        }

        parent::setUp();
    }

    public function test_a_survey_point_can_be_registered_once_as_a_verified_tree(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $user->givePermissionTo(PermissionName::ManageVerifications->value);
        $species = Species::factory()->create();
        $campusArea = CampusArea::factory()->create();
        $surveyPoint = TreeSurveyPoint::query()->create([
            'source_file' => 'test.xlsx',
            'source_row' => 2,
            'raw_species_name' => 'Narra',
            'approved_species_name' => 'Narra',
            'easting' => 301994,
            'northing' => 1828890,
            'height_m' => 5.5,
            'latitude' => 16.480696,
            'longitude' => 121.140771,
            'coordinate_status' => 'valid',
            'campus_area_id' => $campusArea->getKey(),
            'review_status' => SurveyReviewStatus::Approved,
        ]);

        $tree = app(RegisterSurveyTree::class)->handle($surveyPoint, [
            'species_id' => $species->getKey(),
            'campus_area_id' => $campusArea->getKey(),
            'descriptive_location' => 'Test survey location',
            'current_condition' => TreeCondition::Healthy->value,
        ], $user);

        $this->assertSame(VerificationStatus::Verified, $tree->verification_status);
        $this->assertNotNull($tree->published_at);
        $this->assertSame($tree->getKey(), $surveyPoint->fresh()->registered_tree_id);
        $this->assertSame(SurveyReviewStatus::Converted, $surveyPoint->fresh()->review_status);
        $this->assertFalse(TreeSurveyPoint::query()->publiclyReviewable()->whereKey($surveyPoint)->exists());
    }
}
