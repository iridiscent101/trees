<?php

namespace App\Exports\Sheets;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SurveyCoordinateReconciliationClustersSheet implements FromCollection, ShouldAutoSize, WithHeadings, WithStyles, WithTitle
{
    /** @param Collection<int, array<string, mixed>> $clusters */
    public function __construct(private readonly Collection $clusters) {}

    public function collection(): Collection
    {
        return $this->clusters->map(fn (array $cluster): array => array_values($cluster));
    }

    /** @return array<int, string> */
    public function headings(): array
    {
        return ['East Offset Centre (m)', 'North Offset Centre (m)', 'Records', 'First Source Row', 'Last Source Row', 'Average Difference (m)'];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->freezePane('A2');
        $sheet->setAutoFilter($sheet->calculateWorksheetDimension());

        return [1 => ['font' => ['bold' => true]]];
    }

    public function title(): string
    {
        return 'Offset Clusters';
    }
}
