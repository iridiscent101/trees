<?php

namespace Tests\Feature;

use App\Enums\RoleName;
use App\Filament\Resources\CampusAreas\Pages\ListCampusAreas;
use App\Filament\Resources\TreePhotos\Pages\ListTreePhotos;
use App\Filament\Resources\TreeSurveyPoints\Pages\ListTreeSurveyPoints;
use App\Filament\Resources\Trees\Pages\CreateTree;
use App\Filament\Resources\Trees\Pages\ListTrees;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Widgets\InventoryOverview;
use App\Models\Tree;
use App\Models\TreePhoto;
use App\Models\TreeSurveyPoint;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Livewire\Livewire;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use Tests\TestCase;

class AdministrationResourcesTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_administrator_can_open_reference_management_pages(): void
    {
        $administrator = User::factory()->create();
        $administrator->assignRole(RoleName::Administrator->value);
        $this->actingAs($administrator);

        Livewire::test(ListCampusAreas::class)->assertOk();
        Livewire::test(ListUsers::class)->assertOk();
        Livewire::test(ListTreePhotos::class)->assertOk();
    }

    public function test_administrator_can_open_the_responsive_tree_creation_form(): void
    {
        $administrator = User::factory()->create();
        $administrator->assignRole(RoleName::Administrator->value);
        $this->actingAs($administrator);

        Livewire::test(CreateTree::class)
            ->assertOk()
            ->assertSee('Tree identity')
            ->assertSee('Measurements and condition')
            ->assertSee('Verification and publication');
    }

    public function test_only_one_photo_is_primary_for_a_tree(): void
    {
        $tree = Tree::factory()->create();
        $first = TreePhoto::query()->create([
            'tree_id' => $tree->getKey(), 'type' => 'tree', 'disk' => 'public',
            'path' => 'trees/first.jpg', 'alt_text' => 'First tree photograph', 'is_primary' => true,
        ]);
        $second = TreePhoto::query()->create([
            'tree_id' => $tree->getKey(), 'type' => 'tree', 'disk' => 'public',
            'path' => 'trees/second.jpg', 'alt_text' => 'Second tree photograph', 'is_primary' => true,
        ]);

        $this->assertFalse($first->refresh()->is_primary);
        $this->assertTrue($second->refresh()->is_primary);
    }

    public function test_inventory_dashboard_exposes_the_survey_review_queue(): void
    {
        Livewire::test(InventoryOverview::class)
            ->assertSee('Awaiting survey review')
            ->assertSee('Coordinate issues')
            ->assertSee('Unassigned campus areas')
            ->assertSee('Unresolved species');
    }

    public function test_administrator_can_import_a_legacy_workbook_from_tree_inventory(): void
    {
        $administrator = User::factory()->create();
        $administrator->assignRole(RoleName::Administrator->value);
        $this->actingAs($administrator);

        $path = $this->createLegacySurveyWorkbook();

        try {
            Livewire::test(ListTrees::class)
                ->assertActionVisible('importExcel')
                ->callAction('importExcel', data: [
                    'workbook' => UploadedFile::fake()->createWithContent('trees.xls', file_get_contents($path)),
                ])
                ->assertHasNoFormErrors()
                ->assertNotified('Excel import completed');

            $this->assertSame(1, TreeSurveyPoint::query()->count());
        } finally {
            unlink($path);
        }
    }

    public function test_administrator_can_import_a_legacy_workbook_from_survey_records(): void
    {
        $administrator = User::factory()->create();
        $administrator->assignRole(RoleName::Administrator->value);
        $this->actingAs($administrator);

        $path = $this->createLegacySurveyWorkbook();

        try {
            Livewire::test(ListTreeSurveyPoints::class)
                ->assertActionVisible('importExcel')
                ->callAction('importExcel', data: [
                    'workbook' => UploadedFile::fake()->createWithContent('trees.xls', file_get_contents($path)),
                ])
                ->assertHasNoFormErrors()
                ->assertNotified('Excel import completed');

            $this->assertSame(1, TreeSurveyPoint::query()->count());
        } finally {
            unlink($path);
        }
    }

    public function test_user_without_verification_permission_cannot_see_the_import_action(): void
    {
        $dataEncoder = User::factory()->create();
        $dataEncoder->assignRole(RoleName::DataEncoder->value);
        $this->actingAs($dataEncoder);

        Livewire::test(ListTrees::class)
            ->assertActionHidden('importExcel');

        Livewire::test(ListTreeSurveyPoints::class)
            ->assertActionHidden('importExcel');
    }

    private function createLegacySurveyWorkbook(): string
    {
        $path = storage_path('framework/testing/tree-survey-'.Str::uuid().'.xls');
        $spreadsheet = new Spreadsheet;
        $spreadsheet->getActiveSheet()->fromArray([
            ['Species', 'Easting', 'Northing', 'Height', 'Latitude', 'Longitude'],
            ['Narra', 514000.125, 1822000.5, 8.25, 16.4807, 121.1408],
        ]);

        (new Xls($spreadsheet))->save($path);
        $spreadsheet->disconnectWorksheets();

        return $path;
    }
}
