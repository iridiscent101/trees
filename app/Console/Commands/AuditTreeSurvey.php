<?php

namespace App\Console\Commands;

use App\Models\TreeSurveyPoint;
use App\Support\Survey\SurveyAuditRules;
use App\Support\Survey\UtmCoordinateConverter;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use JsonException;

class AuditTreeSurvey extends Command
{
    protected $signature = 'tree-survey:audit
        {--format=table : Output format: table or json}
        {--duplicate-radius=2 : Distance in meters for nearby duplicate candidates}
        {--limit=50 : Maximum rows shown per table section}';

    protected $description = 'Audit provisional tree survey data without changing database records';

    /** @throws JsonException */
    public function handle(UtmCoordinateConverter $converter): int
    {
        $format = strtolower((string) $this->option('format'));
        $duplicateRadius = (float) $this->option('duplicate-radius');
        $limit = (int) $this->option('limit');

        if (! in_array($format, ['table', 'json'], true)) {
            $this->error('The --format option must be table or json.');

            return self::INVALID;
        }

        if ($duplicateRadius < 0 || $duplicateRadius > 50) {
            $this->error('The --duplicate-radius option must be between 0 and 50 meters.');

            return self::INVALID;
        }

        if ($limit < 1 || $limit > 500) {
            $this->error('The --limit option must be between 1 and 500.');

            return self::INVALID;
        }

        $surveyPoints = TreeSurveyPoint::query()
            ->select([
                'id',
                'source_file',
                'source_row',
                'raw_species_name',
                'easting',
                'northing',
                'latitude',
                'longitude',
                'coordinate_status',
                'review_status',
                'registered_tree_id',
            ])
            ->oldest('id')
            ->get();

        $report = $this->buildReport($surveyPoints, $duplicateRadius, $converter);

        if ($format === 'json') {
            $this->line(json_encode($report, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }

        $this->renderTableReport($report, $limit);

        return self::SUCCESS;
    }

    /**
     * @param  Collection<int, TreeSurveyPoint>  $surveyPoints
     * @return array<string, mixed>
     */
    private function buildReport(Collection $surveyPoints, float $duplicateRadius, UtmCoordinateConverter $converter): array
    {
        $coordinateIssues = $surveyPoints
            ->map(function (TreeSurveyPoint $surveyPoint): ?array {
                $issue = SurveyAuditRules::coordinateIssue(
                    $this->toFloat($surveyPoint->latitude),
                    $this->toFloat($surveyPoint->longitude),
                    $this->toFloat($surveyPoint->easting),
                    $this->toFloat($surveyPoint->northing),
                );

                if ($issue === null && $surveyPoint->coordinate_status === 'valid') {
                    return null;
                }

                return [
                    'id' => $surveyPoint->getKey(),
                    'source_row' => $surveyPoint->source_row,
                    'species' => $surveyPoint->raw_species_name,
                    'stored_status' => $surveyPoint->coordinate_status,
                    'audit_issue' => $issue ?? 'stored_status_requires_review',
                    'easting' => $surveyPoint->easting,
                    'northing' => $surveyPoint->northing,
                    'latitude' => $surveyPoint->latitude,
                    'longitude' => $surveyPoint->longitude,
                ];
            })
            ->filter()
            ->values();

        $speciesGroups = $surveyPoints
            ->filter(fn (TreeSurveyPoint $surveyPoint): bool => filled($surveyPoint->raw_species_name))
            ->groupBy(fn (TreeSurveyPoint $surveyPoint): ?string => SurveyAuditRules::normalizedSpeciesKey($surveyPoint->raw_species_name))
            ->map(function (Collection $group, string $normalizedName): array {
                $variants = $group->pluck('raw_species_name')->filter()->unique()->sort()->values();

                return [
                    'normalized_name' => $normalizedName,
                    'records' => $group->count(),
                    'variants' => $variants->all(),
                    'variant_count' => $variants->count(),
                ];
            })
            ->filter(fn (array $group): bool => $group['variant_count'] > 1)
            ->sortByDesc('records')
            ->values();

        $validCoordinates = $surveyPoints
            ->filter(fn (TreeSurveyPoint $surveyPoint): bool => $surveyPoint->coordinate_status === 'valid'
                && $surveyPoint->latitude !== null
                && $surveyPoint->longitude !== null)
            ->values();

        $exactDuplicateGroups = $validCoordinates
            ->groupBy(fn (TreeSurveyPoint $surveyPoint): string => $surveyPoint->latitude.','.$surveyPoint->longitude)
            ->filter(fn (Collection $group): bool => $group->count() > 1)
            ->map(fn (Collection $group): array => [
                'latitude' => $group->first()->latitude,
                'longitude' => $group->first()->longitude,
                'record_ids' => $group->modelKeys(),
                'records' => $group->count(),
            ])
            ->sortByDesc('records')
            ->values();

        $nearbyDuplicatePairs = $this->nearbyDuplicatePairs($validCoordinates, $duplicateRadius);

        $coordinateConversionChecks = $surveyPoints
            ->filter(fn (TreeSurveyPoint $surveyPoint): bool => $surveyPoint->latitude !== null
                && $surveyPoint->longitude !== null
                && SurveyAuditRules::gridCoordinatesArePlausible(
                    $this->toFloat($surveyPoint->easting),
                    $this->toFloat($surveyPoint->northing),
                ))
            ->map(function (TreeSurveyPoint $surveyPoint) use ($converter): array {
                $converted = $converter->toWgs84((float) $surveyPoint->easting, (float) $surveyPoint->northing);
                $differenceMeters = SurveyAuditRules::distanceMeters(
                    (float) $surveyPoint->latitude,
                    (float) $surveyPoint->longitude,
                    $converted['latitude'],
                    $converted['longitude'],
                );

                return [
                    'id' => $surveyPoint->getKey(),
                    'source_row' => $surveyPoint->source_row,
                    'stored_latitude' => $surveyPoint->latitude,
                    'stored_longitude' => $surveyPoint->longitude,
                    'converted_latitude' => round($converted['latitude'], 9),
                    'converted_longitude' => round($converted['longitude'], 9),
                    'difference_meters' => round($differenceMeters, 2),
                ];
            })
            ->sortByDesc('difference_meters')
            ->values();

        return [
            'meta' => [
                'generated_at' => now()->toIso8601String(),
                'read_only' => true,
                'duplicate_radius_meters' => $duplicateRadius,
            ],
            'summary' => [
                'total_records' => $surveyPoints->count(),
                'valid_coordinates' => $surveyPoints->where('coordinate_status', 'valid')->count(),
                'invalid_coordinates' => $surveyPoints->where('coordinate_status', 'invalid')->count(),
                'coordinate_outliers' => $surveyPoints->where('coordinate_status', 'outlier')->count(),
                'pending_review' => $surveyPoints->where('review_status', 'pending')->count(),
                'converted_to_trees' => $surveyPoints->whereNotNull('registered_tree_id')->count(),
                'exact_species_labels' => $surveyPoints->pluck('raw_species_name')->filter()->unique()->count(),
                'case_insensitive_species_labels' => $surveyPoints
                    ->pluck('raw_species_name')
                    ->filter()
                    ->map(fn (string $name): string => mb_strtolower(trim($name)))
                    ->unique()
                    ->count(),
                'probable_species_after_label_cleanup' => $surveyPoints
                    ->pluck('raw_species_name')
                    ->map(fn (?string $name): ?string => SurveyAuditRules::normalizedSpeciesKey($name))
                    ->filter()
                    ->unique()
                    ->count(),
                'coordinate_issues_for_review' => $coordinateIssues->count(),
                'species_variant_groups' => $speciesGroups->count(),
                'exact_duplicate_coordinate_groups' => $exactDuplicateGroups->count(),
                'nearby_duplicate_pairs' => $nearbyDuplicatePairs->count(),
                'coordinate_conversion_rows_checked' => $coordinateConversionChecks->count(),
                'coordinate_conversion_average_difference_meters' => round((float) $coordinateConversionChecks->avg('difference_meters'), 2),
                'coordinate_conversion_maximum_difference_meters' => round((float) $coordinateConversionChecks->max('difference_meters'), 2),
                'coordinate_conversion_rows_over_one_meter' => $coordinateConversionChecks->where('difference_meters', '>', 1)->count(),
                'coordinate_conversion_rows_over_five_meters' => $coordinateConversionChecks->where('difference_meters', '>', 5)->count(),
            ],
            'coordinate_issues' => $coordinateIssues->all(),
            'species_variant_groups' => $speciesGroups->all(),
            'exact_duplicate_coordinate_groups' => $exactDuplicateGroups->all(),
            'nearby_duplicate_pairs' => $nearbyDuplicatePairs->all(),
            'coordinate_conversion_largest_discrepancies' => $coordinateConversionChecks->take(50)->all(),
            'manual_review_requirements' => [
                'Coordinate corrections require comparison with the source workbook and campus landmarks.',
                'Stored geographic coordinates should be reconciled with the WGS 84 / UTM Zone 51N conversion results before verification.',
                'Species normalization requires an approved forestry reference list.',
                'Campus-area assignment requires area centers or boundary polygons.',
                'Duplicate candidates must be checked for multiple trees recorded at one coordinate.',
            ],
        ];
    }

    /**
     * @param  Collection<int, TreeSurveyPoint>  $validCoordinates
     * @return Collection<int, array<string, mixed>>
     */
    private function nearbyDuplicatePairs(Collection $validCoordinates, float $duplicateRadius): Collection
    {
        if ($duplicateRadius <= 0) {
            return collect();
        }

        $pairs = collect();
        $coordinateBuckets = [];
        $cellSizeDegrees = $duplicateRadius / 100000;

        foreach ($validCoordinates as $surveyPoint) {
            $latitudeBucket = (int) floor((float) $surveyPoint->latitude / $cellSizeDegrees);
            $longitudeBucket = (int) floor((float) $surveyPoint->longitude / $cellSizeDegrees);

            for ($latitudeOffset = -1; $latitudeOffset <= 1; $latitudeOffset++) {
                for ($longitudeOffset = -1; $longitudeOffset <= 1; $longitudeOffset++) {
                    $bucketKey = ($latitudeBucket + $latitudeOffset).':'.($longitudeBucket + $longitudeOffset);

                    foreach ($coordinateBuckets[$bucketKey] ?? [] as $candidate) {
                        if ($surveyPoint->latitude === $candidate->latitude && $surveyPoint->longitude === $candidate->longitude) {
                            continue;
                        }

                        $distance = SurveyAuditRules::distanceMeters(
                            (float) $candidate->latitude,
                            (float) $candidate->longitude,
                            (float) $surveyPoint->latitude,
                            (float) $surveyPoint->longitude,
                        );

                        if ($distance <= $duplicateRadius) {
                            $pairs->push([
                                'left_id' => $candidate->getKey(),
                                'right_id' => $surveyPoint->getKey(),
                                'distance_meters' => round($distance, 2),
                            ]);
                        }
                    }
                }
            }

            $currentBucketKey = $latitudeBucket.':'.$longitudeBucket;
            $coordinateBuckets[$currentBucketKey][] = $surveyPoint;
        }

        return $pairs->sortBy('distance_meters')->values();
    }

    /** @param array<string, mixed> $report */
    private function renderTableReport(array $report, int $limit): void
    {
        $this->info('Tree survey audit (read-only)');
        $this->table(
            ['Metric', 'Value'],
            collect($report['summary'])->map(fn (mixed $value, string $metric): array => [$metric, $value])->values()->all(),
        );

        $this->newLine();
        $this->warn('Coordinate issues');
        $this->table(
            ['ID', 'Row', 'Species', 'Stored status', 'Audit issue'],
            collect($report['coordinate_issues'])->take($limit)->map(fn (array $issue): array => [
                $issue['id'],
                $issue['source_row'],
                $issue['species'],
                $issue['stored_status'],
                $issue['audit_issue'],
            ])->all(),
        );

        $this->newLine();
        $this->warn('Species labels with numeric or spelling variants');
        $this->table(
            ['Probable species', 'Records', 'Variants'],
            collect($report['species_variant_groups'])->take($limit)->map(fn (array $group): array => [
                $group['normalized_name'],
                $group['records'],
                implode(' | ', $group['variants']),
            ])->all(),
        );

        $this->newLine();
        $this->warn('Exact duplicate coordinate groups');
        $this->table(
            ['Latitude', 'Longitude', 'Records', 'IDs'],
            collect($report['exact_duplicate_coordinate_groups'])->take($limit)->map(fn (array $group): array => [
                $group['latitude'],
                $group['longitude'],
                $group['records'],
                implode(', ', $group['record_ids']),
            ])->all(),
        );

        $this->newLine();
        $this->warn('Nearby duplicate candidates');
        $this->table(
            ['Left ID', 'Right ID', 'Distance (m)'],
            collect($report['nearby_duplicate_pairs'])->take($limit)->map(fn (array $pair): array => [
                $pair['left_id'],
                $pair['right_id'],
                $pair['distance_meters'],
            ])->all(),
        );

        $this->newLine();
        $this->comment('No database records were changed. Use --format=json for the complete machine-readable report.');
    }

    private function toFloat(mixed $value): ?float
    {
        return $value !== null ? (float) $value : null;
    }
}
