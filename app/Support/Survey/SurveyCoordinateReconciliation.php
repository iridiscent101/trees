<?php

namespace App\Support\Survey;

use App\Models\TreeSurveyPoint;
use Illuminate\Support\Collection;

class SurveyCoordinateReconciliation
{
    public function __construct(private readonly UtmCoordinateConverter $converter) {}

    /**
     * @param  Collection<int, TreeSurveyPoint>  $surveyPoints
     * @return array{summary: array<string, int|float|string>, rows: Collection<int, array<string, mixed>>, clusters: Collection<int, array<string, mixed>>}
     */
    public function build(Collection $surveyPoints): array
    {
        $rows = $surveyPoints
            ->map(fn (TreeSurveyPoint $surveyPoint): array => $this->reconcile($surveyPoint))
            ->values();

        $baselineRows = $rows->whereIn('reconciliation_status', [
            'aligned_within_1m',
            'minor_difference_1_to_5m',
            'manual_reconciliation_over_5m',
        ]);
        $differences = $baselineRows->pluck('difference_meters')->filter(fn (mixed $value): bool => $value !== null);

        return [
            'summary' => [
                'Generated at' => now()->toIso8601String(),
                'Coordinate reference system' => 'EPSG:32651 — WGS 84 / UTM zone 51N',
                'Total survey records' => $rows->count(),
                'Rows converted from plausible grid coordinates' => $rows->whereNotNull('converted_latitude')->count(),
                'Rows in stored-to-converted baseline comparison' => $baselineRows->count(),
                'Aligned within 1 metre' => $rows->where('reconciliation_status', 'aligned_within_1m')->count(),
                'Minor difference over 1 to 5 metres' => $rows->where('reconciliation_status', 'minor_difference_1_to_5m')->count(),
                'Manual reconciliation required over 5 metres' => $rows->where('reconciliation_status', 'manual_reconciliation_over_5m')->count(),
                'Deterministic grid typo repairs awaiting approval' => $rows->where('reconciliation_status', 'deterministic_grid_repair')->count(),
                'Unresolved or implausible grid rows' => $rows->whereIn('reconciliation_status', ['unresolved_grid', 'implausible_grid', 'repair_guard_mismatch'])->count(),
                'Average stored-to-converted difference (metres)' => round((float) $differences->average(), 2),
                'Maximum stored-to-converted difference (metres)' => round((float) $differences->max(), 2),
                'Database records changed' => 'No — review workbook only',
            ],
            'rows' => $rows,
            'clusters' => $this->clusters($rows),
        ];
    }

    /** @return array<string, mixed> */
    private function reconcile(TreeSurveyPoint $surveyPoint): array
    {
        $easting = $this->toFloat($surveyPoint->easting);
        $northing = $this->toFloat($surveyPoint->northing);
        $effectiveEasting = $easting;
        $effectiveNorthing = $northing;
        $status = null;
        $action = null;

        $correction = SurveyCoordinateRepairPlan::deterministicCorrections()[$surveyPoint->source_row] ?? null;

        if ($correction !== null) {
            if ($easting === (float) $correction['expected_easting'] && $northing === (float) $correction['expected_northing']) {
                $effectiveEasting = (float) $correction['corrected_easting'];
                $effectiveNorthing = (float) $correction['corrected_northing'];
                $status = 'deterministic_grid_repair';
                $action = $correction['reason'];
            } else {
                $status = 'repair_guard_mismatch';
                $action = 'Do not apply the proposed repair because the stored source values have changed.';
            }
        } elseif (array_key_exists((int) $surveyPoint->source_row, SurveyCoordinateRepairPlan::unresolvedRows())) {
            $status = 'unresolved_grid';
            $action = SurveyCoordinateRepairPlan::unresolvedRows()[(int) $surveyPoint->source_row];
        }

        $converted = null;

        if ($status !== 'repair_guard_mismatch' && SurveyAuditRules::gridCoordinatesArePlausible($effectiveEasting, $effectiveNorthing)) {
            $converted = $this->converter->toWgs84($effectiveEasting, $effectiveNorthing);
        } elseif ($status === null) {
            $status = 'implausible_grid';
            $action = 'Verify the grid coordinates against the source workbook or resurvey the tree.';
        }

        $storedLatitude = $this->toFloat($surveyPoint->latitude);
        $storedLongitude = $this->toFloat($surveyPoint->longitude);
        $differenceMeters = null;
        $eastOffsetMeters = null;
        $northOffsetMeters = null;

        if ($converted !== null && $storedLatitude !== null && $storedLongitude !== null) {
            $differenceMeters = SurveyAuditRules::distanceMeters(
                $storedLatitude,
                $storedLongitude,
                $converted['latitude'],
                $converted['longitude'],
            );
            $northOffsetMeters = ($storedLatitude - $converted['latitude']) * 111320;
            $eastOffsetMeters = ($storedLongitude - $converted['longitude']) * 111320 * cos(deg2rad($converted['latitude']));

            if ($status === null) {
                [$status, $action] = match (true) {
                    $differenceMeters <= 1 => ['aligned_within_1m', 'Retain after landmark spot-checking.'],
                    $differenceMeters <= 5 => ['minor_difference_1_to_5m', 'Spot-check against a known campus landmark before verification.'],
                    default => ['manual_reconciliation_over_5m', 'Compare the stored point and converted point with the official campus map.'],
                };
            }
        } elseif ($converted !== null && $status === null) {
            $status = 'missing_stored_geographic_coordinates';
            $action = 'Use the converted WGS 84 coordinates after landmark validation.';
        }

        return [
            'source_file' => $surveyPoint->source_file,
            'source_row' => $surveyPoint->source_row,
            'recorded_species' => $surveyPoint->raw_species_name,
            'stored_easting' => $easting,
            'stored_northing' => $northing,
            'effective_easting' => $effectiveEasting,
            'effective_northing' => $effectiveNorthing,
            'stored_latitude' => $storedLatitude,
            'stored_longitude' => $storedLongitude,
            'converted_latitude' => $converted !== null ? round($converted['latitude'], 9) : null,
            'converted_longitude' => $converted !== null ? round($converted['longitude'], 9) : null,
            'east_offset_meters' => $eastOffsetMeters !== null ? round($eastOffsetMeters, 2) : null,
            'north_offset_meters' => $northOffsetMeters !== null ? round($northOffsetMeters, 2) : null,
            'difference_meters' => $differenceMeters !== null ? round($differenceMeters, 2) : null,
            'reconciliation_status' => $status,
            'recommended_action' => $action,
        ];
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $rows
     * @return Collection<int, array<string, mixed>>
     */
    private function clusters(Collection $rows): Collection
    {
        return $rows
            ->filter(fn (array $row): bool => $row['east_offset_meters'] !== null && $row['north_offset_meters'] !== null)
            ->groupBy(fn (array $row): string => $this->roundedOffset((float) $row['east_offset_meters']).':'.$this->roundedOffset((float) $row['north_offset_meters']))
            ->map(function (Collection $cluster): array {
                $first = $cluster->first();

                return [
                    'east_offset_center_meters' => $this->roundedOffset((float) $first['east_offset_meters']),
                    'north_offset_center_meters' => $this->roundedOffset((float) $first['north_offset_meters']),
                    'records' => $cluster->count(),
                    'minimum_source_row' => $cluster->min('source_row'),
                    'maximum_source_row' => $cluster->max('source_row'),
                    'average_difference_meters' => round((float) $cluster->average('difference_meters'), 2),
                ];
            })
            ->sortByDesc('records')
            ->values();
    }

    private function roundedOffset(float $offset): int
    {
        return (int) (round($offset / 5) * 5);
    }

    private function toFloat(mixed $value): ?float
    {
        return $value !== null ? (float) $value : null;
    }
}
