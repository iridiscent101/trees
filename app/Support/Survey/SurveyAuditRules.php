<?php

namespace App\Support\Survey;

use Illuminate\Support\Str;

class SurveyAuditRules
{
    public const CAMPUS_LATITUDE = 16.480696;

    public const CAMPUS_LONGITUDE = 121.140771;

    public static function normalizedSpeciesKey(?string $speciesName): ?string
    {
        if (blank($speciesName)) {
            return null;
        }

        $normalizedName = Str::of($speciesName)
            ->squish()
            ->replaceMatches('/\s*\d+\s*$/u', '')
            ->squish()
            ->lower()
            ->toString();

        return $normalizedName !== '' ? $normalizedName : null;
    }

    public static function coordinateIssue(
        ?float $latitude,
        ?float $longitude,
        ?float $easting,
        ?float $northing,
    ): ?string {
        if ($easting === 0.0 && $northing === 0.0) {
            return 'zero_grid_coordinates';
        }

        if ($latitude === null || $longitude === null) {
            if (! self::gridCoordinatesArePlausible($easting, $northing)) {
                return 'grid_value_outside_expected_range';
            }

            return 'missing_geographic_coordinates';
        }

        if (abs($latitude) > 90 || abs($longitude) > 180) {
            return 'invalid_geographic_range';
        }

        if (! self::gridCoordinatesArePlausible($easting, $northing)) {
            return 'grid_value_outside_expected_range';
        }

        if (self::distanceMeters($latitude, $longitude, self::CAMPUS_LATITUDE, self::CAMPUS_LONGITUDE) > 1000) {
            return 'outside_one_kilometer_campus_radius';
        }

        return null;
    }

    public static function distanceMeters(
        float $latitudeA,
        float $longitudeA,
        float $latitudeB,
        float $longitudeB,
    ): float {
        $earthRadiusMeters = 6371000;
        $latitudeARadians = deg2rad($latitudeA);
        $latitudeBRadians = deg2rad($latitudeB);
        $latitudeDelta = deg2rad($latitudeB - $latitudeA);
        $longitudeDelta = deg2rad($longitudeB - $longitudeA);
        $haversine = sin($latitudeDelta / 2) ** 2
            + cos($latitudeARadians) * cos($latitudeBRadians) * sin($longitudeDelta / 2) ** 2;

        return $earthRadiusMeters * 2 * atan2(sqrt($haversine), sqrt(1 - $haversine));
    }

    public static function gridCoordinatesArePlausible(?float $easting, ?float $northing): bool
    {
        if ($easting === null || $northing === null) {
            return true;
        }

        return $easting >= 250000
            && $easting <= 350000
            && $northing >= 1750000
            && $northing <= 1900000;
    }
}
