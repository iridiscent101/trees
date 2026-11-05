<?php

namespace Tests\Feature;

use App\Models\TreeSurveyPoint;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class ImportTreeSurveyTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_it_imports_a_legacy_xls_workbook(): void
    {
        $path = $this->createSurveyWorkbook('xls');

        try {
            $this->artisan('tree-survey:import', [
                'file' => $path,
                '--source' => 'legacy-trees.xls',
            ])
                ->expectsOutputToContain('Imported 1 survey rows')
                ->assertSuccessful();

            $surveyPoint = TreeSurveyPoint::query()
                ->where('source_file', 'legacy-trees.xls')
                ->where('source_row', 2)
                ->firstOrFail();

            $this->assertSame('Narra', $surveyPoint->raw_species_name);
            $this->assertSame('valid', $surveyPoint->coordinate_status);
            $this->assertSame('16.480700000', $surveyPoint->latitude);
            $this->assertSame('121.140800000', $surveyPoint->longitude);
        } finally {
            unlink($path);
        }
    }

    public function test_it_continues_to_import_an_xlsx_workbook(): void
    {
        $path = $this->createSurveyWorkbook('xlsx');

        try {
            $this->artisan('tree-survey:import', ['file' => $path])
                ->expectsOutputToContain('Imported 1 survey rows')
                ->assertSuccessful();

            $this->assertSame(1, TreeSurveyPoint::query()->count());
        } finally {
            unlink($path);
        }
    }

    public function test_it_rejects_unsupported_file_types(): void
    {
        $path = storage_path('framework/testing/tree-survey-'.Str::uuid().'.csv');
        file_put_contents($path, 'Species,Easting,Northing,Height,Latitude,Longitude');

        try {
            $this->artisan('tree-survey:import', ['file' => $path])
                ->expectsOutput('The workbook must be an XLS or XLSX file.')
                ->assertFailed();
        } finally {
            unlink($path);
        }
    }

    private function createSurveyWorkbook(string $extension): string
    {
        $path = storage_path('framework/testing/tree-survey-'.Str::uuid().'.'.$extension);
        $spreadsheet = new Spreadsheet;
        $spreadsheet->getActiveSheet()->fromArray([
            ['Species', 'Easting', 'Northing', 'Height', 'Latitude', 'Longitude'],
            ['Narra', 514000.125, 1822000.5, 8.25, 16.4807, 121.1408],
        ]);

        $writer = $extension === 'xls' ? new Xls($spreadsheet) : new Xlsx($spreadsheet);
        $writer->save($path);
        $spreadsheet->disconnectWorksheets();

        return $path;
    }
}
