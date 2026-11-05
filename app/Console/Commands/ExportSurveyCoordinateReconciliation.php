<?php

namespace App\Console\Commands;

use App\Exports\SurveyCoordinateReconciliationExport;
use App\Models\TreeSurveyPoint;
use App\Support\Survey\SurveyCoordinateReconciliation;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

#[Signature('tree-survey:export-coordinate-reconciliation
    {--path= : Relative path on the local disk; defaults to a timestamped report in reports/}')]
#[Description('Export a read-only workbook comparing stored and WGS 84 / UTM Zone 51N survey coordinates')]
class ExportSurveyCoordinateReconciliation extends Command
{
    public function handle(SurveyCoordinateReconciliation $reconciliation): int
    {
        $surveyPoints = TreeSurveyPoint::query()
            ->where('source_file', 'trees.xls')
            ->oldest('source_row')
            ->get();
        $report = $reconciliation->build($surveyPoints);
        $path = $this->option('path') ?: 'reports/tree-survey-coordinate-reconciliation-'.now()->format('Ymd-His').'.xlsx';

        if (! str_ends_with(strtolower((string) $path), '.xlsx') || str_contains((string) $path, '..')) {
            $this->error('The --path value must be a safe relative .xlsx path.');

            return self::INVALID;
        }

        if (! Excel::store(new SurveyCoordinateReconciliationExport($report), (string) $path, 'local')) {
            $this->error('The reconciliation workbook could not be written.');

            return self::FAILURE;
        }

        $this->info('Coordinate reconciliation workbook created.');
        $this->line(Storage::disk('local')->path((string) $path));
        $this->table(
            ['Metric', 'Value'],
            collect($report['summary'])->map(fn (int|float|string $value, string $metric): array => [$metric, $value])->values()->all(),
        );
        $this->comment('No survey records were changed.');

        return self::SUCCESS;
    }
}
