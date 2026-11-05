<?php

namespace Tests\Unit;

use App\Models\TreeSurveyPoint;
use App\Support\Survey\SurveyReviewReadiness;
use Tests\TestCase;

class SurveyReviewReadinessTest extends TestCase
{
    public function test_a_complete_unique_record_is_ready_without_a_database_lookup(): void
    {
        $surveyPoint = $this->surveyPoint();

        $this->assertTrue(app(SurveyReviewReadiness::class)->isReady($surveyPoint, false));
    }

    public function test_invalid_coordinates_and_combined_species_are_blocked(): void
    {
        $surveyPoint = $this->surveyPoint([
            'coordinate_status' => 'invalid',
            'approved_species_name' => 'Narra / Mahogany',
        ]);

        $issues = app(SurveyReviewReadiness::class)->issues($surveyPoint, false);

        $this->assertContains('Coordinates must be marked valid.', $issues);
        $this->assertContains('The approved species name must identify one species only.', $issues);
    }

    public function test_species_suggestions_strip_numeric_identifiers_but_not_combined_labels(): void
    {
        $readiness = app(SurveyReviewReadiness::class);

        $this->assertSame('Narra', $readiness->suggestedSpeciesName('  Narra 12 '));
        $this->assertNull($readiness->suggestedSpeciesName('Narra / Mahogany'));
    }

    /** @param array<string, mixed> $overrides */
    private function surveyPoint(array $overrides = []): TreeSurveyPoint
    {
        return new TreeSurveyPoint(array_merge([
            'source_file' => 'test.xlsx',
            'source_row' => 2,
            'raw_species_name' => 'Narra 12',
            'approved_species_name' => 'Narra',
            'latitude' => 16.480696,
            'longitude' => 121.140771,
            'coordinate_status' => 'valid',
            'campus_area_id' => 1,
        ], $overrides));
    }
}
