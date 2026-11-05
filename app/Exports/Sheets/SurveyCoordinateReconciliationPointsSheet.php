<?php

namespace App\Exports\Sheets;

use App\Support\Reports\SpreadsheetCell;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SurveyCoordinateReconciliationPointsSheet implements FromCollection, ShouldAutoSize, WithHeadings, WithStyles, WithTitle
{
    /** @param Collection<int, array<string, mixed>> $rows */
    public function __construct(private readonly Collection $rows) {}

    public function collection(): Collection
    {
        return $this->rows->map(fn (array $row): array => [
            SpreadsheetCell::text($row['source_file']),
            $row['source_row'],
            SpreadsheetCell::text($row['recorded_species']),
            $row['stored_easting'],
            $row['stored_northing'],
            $row['effective_easting'],
            $row['effective_northing'],
            $row['stored_latitude'],
            $row['stored_longitude'],
            $row['converted_latitude'],
            $row['converted_longitude'],
            $row['east_offset_meters'],
            $row['north_offset_meters'],
            $row['difference_meters'],
            $row['reconciliation_status'],
            SpreadsheetCell::text($row['recommended_action']),
        ]);
    }

    /** @return array<int, string> */
    public function headings(): array
    {
        return ['Source File', 'Source Row', 'Recorded Species', 'Stored Easting', 'Stored Northing', 'Effective Easting', 'Effective Northing', 'Stored Latitude', 'Stored Longitude', 'Converted Latitude', 'Converted Longitude', 'East Offset (m)', 'North Offset (m)', 'Difference (m)', 'Status', 'Recommended Action'];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->freezePane('A2');
        $sheet->setAutoFilter($sheet->calculateWorksheetDimension());

        return [1 => ['font' => ['bold' => true]]];
    }

    public function title(): string
    {
        return 'Point Review';
    }
}
