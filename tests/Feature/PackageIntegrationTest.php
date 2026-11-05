<?php

namespace Tests\Feature;

use Barryvdh\DomPDF\PDF;
use Barryvdh\DomPDF\Facade\Pdf as PdfFacade;
use Maatwebsite\Excel\Excel;
use Spatie\Activitylog\Support\ActivityLogger;
use Tests\TestCase;

class PackageIntegrationTest extends TestCase
{
    public function test_reporting_and_audit_packages_are_registered(): void
    {
        $this->assertInstanceOf(ActivityLogger::class, $this->app->make(ActivityLogger::class));
        $this->assertInstanceOf(Excel::class, $this->app->make(Excel::class));
        $this->assertInstanceOf(PDF::class, $this->app->make('dompdf.wrapper'));
    }

    public function test_inventory_pdf_views_render_valid_pdf_documents(): void
    {
        $reports = [
            ['reports.trees', ['trees' => collect()]],
            ['reports.species', ['species' => collect()]],
            ['reports.survey-records', ['surveyPoints' => collect()]],
        ];

        foreach ($reports as [$view, $data]) {
            $pdf = PdfFacade::loadView($view, [...$data, 'generatedAt' => now()]);

            $this->assertStringStartsWith('%PDF', $pdf->output());
        }
    }
}
