<?php

namespace Tests\Unit;

use App\Support\Survey\SurveyAuditRules;
use PHPUnit\Framework\TestCase;

class SurveyAuditRulesTest extends TestCase
{
    public function test_it_normalizes_numbered_species_labels(): void
    {
        $this->assertSame('mahogany', SurveyAuditRules::normalizedSpeciesKey('  Mahogany 17 '));
        $this->assertSame('mangga', SurveyAuditRules::normalizedSpeciesKey('Mangga4'));
        $this->assertSame('ipil-ipil', SurveyAuditRules::normalizedSpeciesKey(' Ipil-ipil '));
        $this->assertNull(SurveyAuditRules::normalizedSpeciesKey('  '));
    }

    public function test_it_classifies_coordinate_quality_issues(): void
    {
        $this->assertSame('zero_grid_coordinates', SurveyAuditRules::coordinateIssue(null, null, 0, 0));
        $this->assertSame('grid_value_outside_expected_range', SurveyAuditRules::coordinateIssue(null, null, 301735, 18222829));
        $this->assertSame('outside_one_kilometer_campus_radius', SurveyAuditRules::coordinateIssue(16.533750924, 121.144598163, 301994, 1828890));
        $this->assertNull(SurveyAuditRules::coordinateIssue(16.480696, 121.140771, 301994, 1822890));
    }

    public function test_it_calculates_duplicate_candidate_distances(): void
    {
        $this->assertSame(0.0, SurveyAuditRules::distanceMeters(16.480696, 121.140771, 16.480696, 121.140771));
        $this->assertLessThan(2, SurveyAuditRules::distanceMeters(16.480696, 121.140771, 16.480700, 121.140780));
    }

    public function test_it_identifies_plausible_utm_zone_51_grid_values(): void
    {
        $this->assertTrue(SurveyAuditRules::gridCoordinatesArePlausible(301755, 1822831));
        $this->assertFalse(SurveyAuditRules::gridCoordinatesArePlausible(301735, 18222829));
        $this->assertFalse(SurveyAuditRules::gridCoordinatesArePlausible(0, 0));
    }
}
