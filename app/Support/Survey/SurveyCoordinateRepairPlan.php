<?php

namespace App\Support\Survey;

class SurveyCoordinateRepairPlan
{
    public const SOURCE_FILE = 'trees.xls';

    /**
     * @return array<int, array{expected_easting: float, expected_northing: float, corrected_easting: float, corrected_northing: float, reason: string}>
     */
    public static function deterministicCorrections(): array
    {
        return [
            394 => self::northingCorrection(301735, 18222829, 1822829),
            395 => self::northingCorrection(301735, 18222829, 1822829),
            396 => self::northingCorrection(301741, 18222838, 1822838),
            397 => self::northingCorrection(301744, 18222838, 1822838),
            398 => self::northingCorrection(301747, 18222838, 1822838),
            399 => self::northingCorrection(301745, 18222838, 1822838),
            400 => self::northingCorrection(301750, 18222835, 1822835),
            401 => [
                'expected_easting' => 131737,
                'expected_northing' => 1822828,
                'corrected_easting' => 301737,
                'corrected_northing' => 1822828,
                'reason' => 'Easting has transposed or missing digits compared with adjacent survey points.',
            ],
            634 => self::northingCorrection(301397, 18223240, 1823240),
            635 => [
                'expected_easting' => 30362,
                'expected_northing' => 1822249,
                'corrected_easting' => 301362,
                'corrected_northing' => 1823249,
                'reason' => 'Easting is missing a digit and Northing contains a mistyped digit in the surrounding coordinate sequence.',
            ],
        ];
    }

    /** @return array<int, string> */
    public static function unresolvedRows(): array
    {
        return [565 => 'The source workbook contains zero for both Easting and Northing; field verification or resurvey is required.'];
    }

    /** @return array{expected_easting: float, expected_northing: float, corrected_easting: float, corrected_northing: float, reason: string} */
    private static function northingCorrection(float $easting, float $northing, float $correctedNorthing): array
    {
        return [
            'expected_easting' => $easting,
            'expected_northing' => $northing,
            'corrected_easting' => $easting,
            'corrected_northing' => $correctedNorthing,
            'reason' => 'Northing contains one extra digit compared with adjacent survey points.',
        ];
    }
}
