<?php

namespace App\Console\Commands;

use App\Models\TreeSurveyPoint;
use App\Support\Survey\SurveyAuditRules;
use App\Support\Survey\SurveyCoordinateRepairPlan;
use App\Support\Survey\UtmCoordinateConverter;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('tree-survey:repair-coordinates
    {--apply : Apply the deterministic repairs after validating all source values}')]
#[Description('Preview or apply deterministic coordinate typo repairs for the original tree survey')]
class RepairTreeSurveyCoordinates extends Command
{
    public function handle(UtmCoordinateConverter $converter): int
    {
        $corrections = SurveyCoordinateRepairPlan::deterministicCorrections();
        $surveyPoints = TreeSurveyPoint::query()
            ->where('source_file', SurveyCoordinateRepairPlan::SOURCE_FILE)
            ->whereIn('source_row', array_keys($corrections))
            ->get()
            ->keyBy('source_row');

        $repairs = [];
        $validationErrors = [];

        foreach ($corrections as $sourceRow => $correction) {
            $surveyPoint = $surveyPoints->get($sourceRow);

            if (! $surveyPoint) {
                $validationErrors[] = "Source row {$sourceRow} is missing from the imported survey.";

                continue;
            }

            if ((float) $surveyPoint->easting !== (float) $correction['expected_easting'] || (float) $surveyPoint->northing !== (float) $correction['expected_northing']) {
                $validationErrors[] = "Source row {$sourceRow} no longer matches the audited source values.";

                continue;
            }

            $geographicCoordinates = $converter->toWgs84($correction['corrected_easting'], $correction['corrected_northing']);
            $coordinateIssue = SurveyAuditRules::coordinateIssue(
                $geographicCoordinates['latitude'],
                $geographicCoordinates['longitude'],
                $correction['corrected_easting'],
                $correction['corrected_northing'],
            );

            if ($coordinateIssue !== null) {
                $validationErrors[] = "Source row {$sourceRow} remains invalid after repair: {$coordinateIssue}.";

                continue;
            }

            $repairs[$sourceRow] = [
                'survey_point' => $surveyPoint,
                'easting' => $correction['corrected_easting'],
                'northing' => $correction['corrected_northing'],
                'latitude' => round($geographicCoordinates['latitude'], 9),
                'longitude' => round($geographicCoordinates['longitude'], 9),
                'reason' => $correction['reason'],
            ];
        }

        $this->table(
            ['Source row', 'Old grid', 'Corrected grid', 'Latitude', 'Longitude', 'Reason'],
            collect($repairs)->map(fn (array $repair, int $sourceRow): array => [
                $sourceRow,
                $repair['survey_point']->easting.', '.$repair['survey_point']->northing,
                $repair['easting'].', '.$repair['northing'],
                $repair['latitude'],
                $repair['longitude'],
                $repair['reason'],
            ])->values()->all(),
        );

        foreach (SurveyCoordinateRepairPlan::unresolvedRows() as $sourceRow => $reason) {
            $this->warn("Source row {$sourceRow} remains unresolved: {$reason}");
        }

        if ($validationErrors !== []) {
            foreach ($validationErrors as $validationError) {
                $this->error($validationError);
            }

            $this->error('No records were changed because the repair plan did not pass validation.');

            return self::FAILURE;
        }

        if (! $this->option('apply')) {
            $this->comment('Dry run only. No database records were changed. Run with --apply after reviewing these corrections.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($repairs): void {
            foreach ($repairs as $repair) {
                $repair['survey_point']->update([
                    'easting' => $repair['easting'],
                    'northing' => $repair['northing'],
                    'latitude' => $repair['latitude'],
                    'longitude' => $repair['longitude'],
                    'coordinate_status' => 'valid',
                ]);
            }
        });

        $this->info(count($repairs).' coordinate repairs were applied.');

        return self::SUCCESS;
    }
}
