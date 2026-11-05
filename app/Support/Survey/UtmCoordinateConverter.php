<?php

namespace App\Support\Survey;

class UtmCoordinateConverter
{
    private const WGS84_SEMI_MAJOR_AXIS = 6378137.0;

    private const WGS84_ECCENTRICITY_SQUARED = 0.00669438;

    private const UTM_SCALE_FACTOR = 0.9996;

    /** @return array{latitude: float, longitude: float} */
    public function toWgs84(float $easting, float $northing, int $zone = 51): array
    {
        $eccentricitySquared = self::WGS84_ECCENTRICITY_SQUARED;
        $eccentricityPrimeSquared = $eccentricitySquared / (1 - $eccentricitySquared);
        $x = $easting - 500000.0;
        $meridionalArc = $northing / self::UTM_SCALE_FACTOR;
        $mu = $meridionalArc / (self::WGS84_SEMI_MAJOR_AXIS * (1 - $eccentricitySquared / 4 - 3 * $eccentricitySquared ** 2 / 64 - 5 * $eccentricitySquared ** 3 / 256));
        $eccentricityOne = (1 - sqrt(1 - $eccentricitySquared)) / (1 + sqrt(1 - $eccentricitySquared));

        $latitudeFootpoint = $mu
            + (3 * $eccentricityOne / 2 - 27 * $eccentricityOne ** 3 / 32) * sin(2 * $mu)
            + (21 * $eccentricityOne ** 2 / 16 - 55 * $eccentricityOne ** 4 / 32) * sin(4 * $mu)
            + (151 * $eccentricityOne ** 3 / 96) * sin(6 * $mu)
            + (1097 * $eccentricityOne ** 4 / 512) * sin(8 * $mu);

        $radiusOfCurvature = self::WGS84_SEMI_MAJOR_AXIS / sqrt(1 - $eccentricitySquared * sin($latitudeFootpoint) ** 2);
        $tangentSquared = tan($latitudeFootpoint) ** 2;
        $eccentricityTerm = $eccentricityPrimeSquared * cos($latitudeFootpoint) ** 2;
        $meridionalRadius = self::WGS84_SEMI_MAJOR_AXIS * (1 - $eccentricitySquared) / (1 - $eccentricitySquared * sin($latitudeFootpoint) ** 2) ** 1.5;
        $normalizedEasting = $x / ($radiusOfCurvature * self::UTM_SCALE_FACTOR);

        $latitudeRadians = $latitudeFootpoint - ($radiusOfCurvature * tan($latitudeFootpoint) / $meridionalRadius) * (
            $normalizedEasting ** 2 / 2
            - (5 + 3 * $tangentSquared + 10 * $eccentricityTerm - 4 * $eccentricityTerm ** 2 - 9 * $eccentricityPrimeSquared) * $normalizedEasting ** 4 / 24
            + (61 + 90 * $tangentSquared + 298 * $eccentricityTerm + 45 * $tangentSquared ** 2 - 252 * $eccentricityPrimeSquared - 3 * $eccentricityTerm ** 2) * $normalizedEasting ** 6 / 720
        );

        $longitudeOrigin = ($zone - 1) * 6 - 180 + 3;
        $longitudeRadians = (
            $normalizedEasting
            - (1 + 2 * $tangentSquared + $eccentricityTerm) * $normalizedEasting ** 3 / 6
            + (5 - 2 * $eccentricityTerm + 28 * $tangentSquared - 3 * $eccentricityTerm ** 2 + 8 * $eccentricityPrimeSquared + 24 * $tangentSquared ** 2) * $normalizedEasting ** 5 / 120
        ) / cos($latitudeFootpoint);

        return [
            'latitude' => rad2deg($latitudeRadians),
            'longitude' => $longitudeOrigin + rad2deg($longitudeRadians),
        ];
    }
}
