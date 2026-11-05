<?php

namespace Tests\Feature;

use App\Actions\RecordTreeMonitoring;
use App\Actions\ReviewTreeMonitoring;
use App\Enums\RoleName;
use App\Enums\TreeCondition;
use App\Enums\VerificationStatus;
use App\Filament\Resources\TreeMonitorings\Pages\ListTreeMonitorings;
use App\Models\Tree;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TreeMonitoringWorkflowTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_monitoring_inspection_can_be_recorded_verified_and_published(): void
    {
        $recorder = User::factory()->create();
        $recorder->assignRole(RoleName::MaintenancePersonnel->value);
        $verifier = User::factory()->create();
        $verifier->assignRole(RoleName::CertifiedForester->value);
        $tree = Tree::factory()->verified()->create(['current_condition' => TreeCondition::Healthy]);

        $monitoring = app(RecordTreeMonitoring::class)->handle([
            'tree_id' => $tree->getKey(), 'observed_at' => now(), 'condition' => TreeCondition::Fair,
            'trunk_diameter_cm' => 44.5, 'estimated_height_m' => 12.3,
            'remarks' => 'Minor canopy damage observed.',
            'public_notes' => 'The tree remains stable and will be monitored.',
        ], $recorder);

        $this->assertSame(VerificationStatus::Pending, $monitoring->verification_status);
        $this->assertFalse($monitoring->is_public);

        $monitoring = app(ReviewTreeMonitoring::class)->handle($monitoring, VerificationStatus::Verified, $verifier, 'Field observation confirmed.', true);

        $this->assertSame(VerificationStatus::Verified, $monitoring->verification_status);
        $this->assertTrue($monitoring->is_public);
        $this->assertSame(TreeCondition::Fair, $tree->refresh()->current_condition);
        $this->assertCount(1, $monitoring->verifications);

        $this->get(route('trees.show', $tree->tree_code))
            ->assertOk()->assertSee('Verified monitoring history')
            ->assertSee('View on Campus Map')
            ->assertSee('The tree remains stable and will be monitored.');
    }

    public function test_unverified_monitoring_is_not_shown_publicly(): void
    {
        $tree = Tree::factory()->verified()->create();
        $recorder = User::factory()->create();
        $recorder->assignRole(RoleName::MaintenancePersonnel->value);
        app(RecordTreeMonitoring::class)->handle([
            'tree_id' => $tree->getKey(), 'observed_at' => now(), 'condition' => TreeCondition::Poor,
            'remarks' => 'Requires review.', 'public_notes' => 'This must remain hidden.',
        ], $recorder);

        $this->get(route('trees.show', $tree->tree_code))->assertOk()->assertDontSee('This must remain hidden.');
    }

    public function test_authorized_user_can_open_monitoring_management_page(): void
    {
        $user = User::factory()->create();
        $user->assignRole(RoleName::CertifiedForester->value);
        $this->actingAs($user);

        Livewire::test(ListTreeMonitorings::class)->assertOk();
    }
}
