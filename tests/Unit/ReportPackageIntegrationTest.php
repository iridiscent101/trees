<?php

namespace Tests\Unit;

use App\Exports\TreeSurveyPointsExport;
use App\Exports\TreesExport;
use App\Models\CampusArea;
use App\Models\Species;
use App\Models\Tree;
use App\Models\TreeSurveyPoint;
use App\Support\Reports\SpreadsheetCell;
use Illuminate\Database\Eloquent\Builder;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class ReportPackageIntegrationTest extends MockeryTestCase
{
    public function test_spreadsheet_text_neutralizes_formula_injection(): void
    {
        $this->assertSame("'=SUM(A1:A2)", SpreadsheetCell::text('=SUM(A1:A2)'));
        $this->assertSame("'+639171234567", SpreadsheetCell::text('+639171234567'));
        $this->assertSame('Narra', SpreadsheetCell::text('Narra'));
        $this->assertSame('', SpreadsheetCell::text(null));
    }

    public function test_tree_export_maps_inventory_fields(): void
    {
        $tree = new Tree([
            'descriptive_location' => '=Unsafe location',
            'latitude' => 16.6,
            'longitude' => 121.6,
            'estimated_height_m' => 12.5,
            'trunk_diameter_cm' => 30,
            'current_condition' => 'healthy',
            'status' => 'existing',
            'verification_status' => 'verified',
        ]);
        $tree->forceFill(['tree_code' => 'T-0001']);
        $tree->setRelation('species', new Species([
            'common_name' => 'Narra',
            'scientific_name' => 'Pterocarpus indicus',
            'family_name' => 'Fabaceae',
        ]));
        $tree->setRelation('campusArea', new CampusArea(['name' => 'Main Campus']));

        $export = new TreesExport(Mockery::mock(Builder::class));
        $row = $export->map($tree);

        $this->assertSame('T-0001', $row[0]);
        $this->assertSame('Narra', $row[1]);
        $this->assertSame("'=Unsafe location", $row[4]);
        $this->assertSame('healthy', $row[10]);
        $this->assertCount(14, $export->headings());
    }

    public function test_survey_export_maps_review_fields(): void
    {
        $surveyPoint = new TreeSurveyPoint([
            'source_file' => 'trees.xlsx',
            'source_row' => 7,
            'raw_species_name' => '@unsafe',
            'coordinate_status' => 'valid',
            'review_status' => 'pending',
        ]);
        $surveyPoint->setRelation('registeredTree', null);
        $surveyPoint->setRelation('reviewer', null);

        $export = new TreeSurveyPointsExport(Mockery::mock(Builder::class));
        $row = $export->map($surveyPoint);

        $this->assertSame('S-0007', $row[0]);
        $this->assertSame("'@unsafe", $row[3]);
        $this->assertSame('pending', $row[10]);
        $this->assertCount(15, $export->headings());
    }

    public function test_inventory_models_define_dirty_activity_logging(): void
    {
        foreach ([new Tree, new Species, new TreeSurveyPoint] as $model) {
            $options = $model->getActivitylogOptions();

            $this->assertTrue($options->logFillable);
            $this->assertTrue($options->logOnlyDirty);
            $this->assertFalse($options->logEmptyChanges);
        }
    }
}
