<?php

namespace Tests\Unit;

use App\Support\Survey\SurveyCoordinateRepairPlan;
use App\Support\Survey\UtmCoordinateConverter;
use PHPUnit\Framework\TestCase;

class SurveyCoordinateRepairPlanTest extends TestCase
{
    public function test_utm_zone_51_conversion_matches_known_survey_coordinates(): void
    {
        $coordinates = (new UtmCoordinateConverter)->toWgs84(301755, 1822831);

        $this->assertEqualsWithDelta(16.4789867028, $coordinates['latitude'], 0.000000001);
        $this->assertEqualsWithDelta(121.1428823243, $coordinates['longitude'], 0.000000001);
    }

    public function test_repair_plan_is_guarded_by_the_original_values(): void
    {
        $corrections = SurveyCoordinateRepairPlan::deterministicCorrections();

        $this->assertCount(10, $corrections);
        $this->assertSame(18222829.0, $corrections[394]['expected_northing']);
        $this->assertSame(1822829.0, $corrections[394]['corrected_northing']);
        $this->assertEquals(301362.0, $corrections[635]['corrected_easting']);
        $this->assertArrayHasKey(565, SurveyCoordinateRepairPlan::unresolvedRows());
    }
}
