<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SurveyCoordinateReconciliationSummarySheet implements FromArray, ShouldAutoSize, WithStyles, WithTitle
{
    /** @param array<string, int|float|string> $summary */
    public function __construct(private readonly array $summary) {}

    /** @return array<int, array<int, int|float|string>> */
    public function array(): array
    {
        return [
            ['Metric', 'Value'],
            ...collect($this->summary)->map(fn (int|float|string $value, string $metric): array => [$metric, $value])->values()->all(),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->freezePane('A2');

        return [1 => ['font' => ['bold' => true]]];
    }

    public function title(): string
    {
        return 'Decision Summary';
    }
}
