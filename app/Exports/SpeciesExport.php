<?php

namespace App\Exports;

use App\Models\Species;
use App\Support\Reports\SpreadsheetCell;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SpeciesExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(private readonly Builder $species) {}

    public function query(): Builder
    {
        return (clone $this->species)->withCount('trees');
    }

    /** @return array<int, string> */
    public function headings(): array
    {
        return ['Common Name', 'Scientific Name', 'Family', 'Local Name', 'Classification', 'Conservation Status', 'Origin', 'Tree Count', 'Active', 'Updated'];
    }

    /** @return array<int, bool|float|int|string|null> */
    public function map($row): array
    {
        /** @var Species $row */
        return [
            SpreadsheetCell::text($row->common_name),
            SpreadsheetCell::text($row->scientific_name),
            SpreadsheetCell::text($row->family_name),
            SpreadsheetCell::text($row->local_name),
            SpreadsheetCell::text($row->classification),
            $row->conservation_status->value,
            SpreadsheetCell::text($row->origin),
            $row->trees_count,
            $row->is_active,
            $row->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
