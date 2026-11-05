<?php

namespace App\Exports;

use App\Models\TreeSurveyPoint;
use App\Support\Reports\SpreadsheetCell;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TreeSurveyPointsExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(private readonly Builder $surveyPoints) {}

    public function query(): Builder
    {
        return (clone $this->surveyPoints)->with(['registeredTree:id,tree_code', 'reviewer:id,name']);
    }

    /** @return array<int, string> */
    public function headings(): array
    {
        return ['Survey ID', 'Source File', 'Source Row', 'Recorded Species', 'Easting', 'Northing', 'Height (m)', 'Latitude', 'Longitude', 'Coordinate Status', 'Review Status', 'Registered Tree', 'Reviewer', 'Reviewed At', 'Notes'];
    }

    /** @return array<int, bool|float|int|string|null> */
    public function map($row): array
    {
        /** @var TreeSurveyPoint $row */
        return [
            'S-'.str_pad((string) $row->source_row, 4, '0', STR_PAD_LEFT),
            SpreadsheetCell::text($row->source_file),
            $row->source_row,
            SpreadsheetCell::text($row->raw_species_name),
            $row->easting,
            $row->northing,
            $row->height_m,
            $row->latitude,
            $row->longitude,
            $row->coordinate_status,
            $row->review_status->value,
            SpreadsheetCell::text($row->registeredTree?->tree_code),
            SpreadsheetCell::text($row->reviewer?->name),
            $row->reviewed_at?->format('Y-m-d H:i:s'),
            SpreadsheetCell::text($row->review_notes),
        ];
    }
}
