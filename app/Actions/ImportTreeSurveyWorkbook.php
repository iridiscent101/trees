<?php

namespace App\Actions;

use App\Models\TreeSurveyPoint;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use RuntimeException;
use Throwable;

class ImportTreeSurveyWorkbook
{
    /** @return array{total: int, valid: int, outlier: int, invalid: int} */
    public function handle(string $file, ?string $sourceFile = null): array
    {
        $path = realpath($file);

        if (! $path || ! is_file($path)) {
            throw new RuntimeException('The workbook could not be found.');
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if (! in_array($extension, ['xls', 'xlsx'], true)) {
            throw new RuntimeException('The workbook must be an XLS or XLSX file.');
        }

        try {
            $reader = IOFactory::createReader(IOFactory::identify($path));
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($path);
        } catch (Throwable $exception) {
            throw new RuntimeException('The workbook could not be read. Confirm it is a valid Excel file.', previous: $exception);
        }

        $records = [];
        $headers = [];
        $sourceName = pathinfo($sourceFile ?: $path, PATHINFO_BASENAME);

        try {
            foreach ($spreadsheet->getSheet(0)->toArray(null, true, true, false) as $index => $values) {
                $rowNumber = $index + 1;

                if ($rowNumber === 1) {
                    $headers = array_map(fn (mixed $value): string => trim((string) $value), $values);
                    $this->validateHeaders($headers);

                    continue;
                }

                $rowValues = array_slice(array_pad($values, count($headers), null), 0, count($headers));
                $source = array_combine($headers, $rowValues);
                $latitude = $this->number($source['Latitude'] ?? null);
                $longitude = $this->number($source['Longitude'] ?? null);
                $status = $this->coordinateStatus($latitude, $longitude);

                $records[] = [
                    'source_file' => $sourceName,
                    'source_row' => $rowNumber,
                    'raw_species_name' => trim((string) ($source['Species'] ?? '')) ?: null,
                    'easting' => $this->number($source['Easting'] ?? null),
                    'northing' => $this->number($source['Northing'] ?? null),
                    'height_m' => $this->number($source['Height'] ?? null),
                    'latitude' => $status === 'invalid' ? null : $latitude,
                    'longitude' => $status === 'invalid' ? null : $longitude,
                    'coordinate_status' => $status,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        } finally {
            $this->closeSpreadsheet($spreadsheet);
        }

        DB::transaction(fn () => collect($records)->chunk(250)->each(fn ($chunk) => TreeSurveyPoint::query()->upsert(
            $chunk->all(),
            ['source_file', 'source_row'],
            ['raw_species_name', 'easting', 'northing', 'height_m', 'latitude', 'longitude', 'coordinate_status', 'updated_at'],
        )));

        $counts = collect($records)->countBy('coordinate_status');

        return [
            'total' => count($records),
            'valid' => $counts->get('valid', 0),
            'outlier' => $counts->get('outlier', 0),
            'invalid' => $counts->get('invalid', 0),
        ];
    }

    /** @param array<int, string> $headers */
    private function validateHeaders(array $headers): void
    {
        $required = ['Species', 'Easting', 'Northing', 'Height', 'Latitude', 'Longitude'];

        if (array_diff($required, $headers)) {
            throw new RuntimeException('The workbook does not contain the expected survey columns.');
        }
    }

    private function number(mixed $value): ?float
    {
        return is_numeric($value) ? (float) $value : null;
    }

    private function coordinateStatus(?float $latitude, ?float $longitude): string
    {
        if ($latitude === null || $longitude === null || abs($latitude) > 90 || abs($longitude) > 180) {
            return 'invalid';
        }

        return $this->distanceFromCampus($latitude, $longitude) > 1 ? 'outlier' : 'valid';
    }

    private function distanceFromCampus(float $latitude, float $longitude): float
    {
        $earthRadius = 6371;
        $originLatitude = deg2rad(16.480696);
        $latitudeDelta = deg2rad($latitude - 16.480696);
        $longitudeDelta = deg2rad($longitude - 121.140771);
        $a = sin($latitudeDelta / 2) ** 2
            + cos($originLatitude) * cos(deg2rad($latitude)) * sin($longitudeDelta / 2) ** 2;

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    private function closeSpreadsheet(Spreadsheet $spreadsheet): void
    {
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
    }
}
