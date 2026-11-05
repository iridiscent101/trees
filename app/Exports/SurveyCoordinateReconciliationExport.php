<?php

namespace App\Exports;

use App\Exports\Sheets\SurveyCoordinateReconciliationClustersSheet;
use App\Exports\Sheets\SurveyCoordinateReconciliationPointsSheet;
use App\Exports\Sheets\SurveyCoordinateReconciliationSummarySheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class SurveyCoordinateReconciliationExport implements WithMultipleSheets
{
    /** @param array{summary: array<string, int|float|string>, rows: mixed, clusters: mixed} $report */
    public function __construct(private readonly array $report) {}

    /** @return array<int, object> */
    public function sheets(): array
    {
        return [
            new SurveyCoordinateReconciliationSummarySheet($this->report['summary']),
            new SurveyCoordinateReconciliationPointsSheet($this->report['rows']),
            new SurveyCoordinateReconciliationClustersSheet($this->report['clusters']),
        ];
    }
}
