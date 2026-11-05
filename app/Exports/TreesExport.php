<?php

namespace App\Exports;

use App\Models\Tree;
use App\Support\Reports\SpreadsheetCell;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TreesExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(private readonly Builder $trees) {}

    public function query(): Builder
    {
        return (clone $this->trees)->with(['species:id,common_name,scientific_name', 'campusArea:id,name']);
    }

    /** @return array<int, string> */
    public function headings(): array
    {
        return ['Tree ID', 'Common Name', 'Scientific Name', 'Campus Area', 'Location', 'Landmark', 'Latitude', 'Longitude', 'Height (m)', 'Diameter (cm)', 'Condition', 'Status', 'Verification', 'Updated'];
    }

    /** @return array<int, bool|float|int|string|null> */
    public function map($row): array
    {
        /** @var Tree $row */
        return [
            SpreadsheetCell::text($row->tree_code),
            SpreadsheetCell::text($row->species?->common_name),
            SpreadsheetCell::text($row->species?->scientific_name),
            SpreadsheetCell::text($row->campusArea?->name),
            SpreadsheetCell::text($row->descriptive_location),
            SpreadsheetCell::text($row->nearby_landmark),
            $row->latitude,
            $row->longitude,
            $row->estimated_height_m,
            $row->trunk_diameter_cm,
            $row->current_condition->value,
            $row->status->value,
            $row->verification_status->value,
            $row->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
