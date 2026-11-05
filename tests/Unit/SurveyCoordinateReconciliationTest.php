<?php

namespace Tests\Unit;

use App\Models\TreeSurveyPoint;
use App\Support\Survey\SurveyCoordinateReconciliation;
use App\Support\Survey\UtmCoordinateConverter;
use PHPUnit\Framework\TestCase;

class SurveyCoordinateReconciliationTest extends TestCase
{
    public function test_it_classifies_aligned_and_materially_different_coordinates(): void
    {
        $converter = new UtmCoordinateConverter;
        $known = $converter->toWgs84(301755, 1822831);
        $report = (new SurveyCoordinateReconciliation($converter))->build(collect([
            $this->surveyPoint(10, 301755, 1822831, $known['latitude'], $known['longitude']),
            $this->surveyPoint(11, 301755, 1822831, $known['latitude'], $known['longitude'] + 0.0014),
        ]));

        $this->assertSame('aligned_within_1m', $report['rows'][0]['reconciliation_status']);
        $this->assertSame('manual_reconciliation_over_5m', $report['rows'][1]['reconciliation_status']);
        $this->assertSame(1, $report['summary']['Manual reconciliation required over 5 metres']);
    }

    public function test_it_previews_deterministic_repairs_and_keeps_zero_coordinates_unresolved(): void
    {
        $report = (new SurveyCoordinateReconciliation(new UtmCoordinateConverter))->build(collect([
            $this->surveyPoint(394, 301735, 18222829, 16.47896, 121.14269),
            $this->surveyPoint(565, 0, 0, null, null),
        ]));

        $this->assertSame('deterministic_grid_repair', $report['rows'][0]['reconciliation_status']);
        $this->assertSame(1822829.0, $report['rows'][0]['effective_northing']);
        $this->assertSame('unresolved_grid', $report['rows'][1]['reconciliation_status']);
        $this->assertNull($report['rows'][1]['converted_latitude']);
        $this->assertSame(0, $report['summary']['Rows in stored-to-converted baseline comparison']);
        $this->assertSame(0.0, $report['summary']['Average stored-to-converted difference (metres)']);
    }

    private function surveyPoint(int $sourceRow, float $easting, float $northing, ?float $latitude, ?float $longitude): TreeSurveyPoint
    {
        return new TreeSurveyPoint([
            'source_file' => 'trees.xls',
            'source_row' => $sourceRow,
            'raw_species_name' => 'Test tree',
            'easting' => $easting,
            'northing' => $northing,
            'latitude' => $latitude,
            'longitude' => $longitude,
        ]);
    }
}
