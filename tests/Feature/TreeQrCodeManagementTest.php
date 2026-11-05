<?php

namespace Tests\Feature;

use App\Actions\DownloadTreeQrCode;
use App\Actions\GenerateTreeQrCode;
use App\Actions\UpdateTreeQrCodeStatus;
use App\Enums\RoleName;
use App\Filament\Resources\TreeQrCodes\Pages\ListTreeQrCodes;
use App\Models\Tree;
use App\Models\User;
use App\Policies\TreeQrCodePolicy;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class TreeQrCodeManagementTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        Storage::fake('local');
    }

    public function test_authorized_user_can_generate_regenerate_and_download_tree_qr_code(): void
    {
        $administrator = User::factory()->create();
        $administrator->assignRole(RoleName::Administrator->value);
        $tree = Tree::factory()->verified()->create(['tree_code' => 'T-1001']);

        $qrCode = app(GenerateTreeQrCode::class)->handle($tree, $administrator);

        $this->assertSame('T-1001', $qrCode->tree->tree_code);
        $this->assertSame(1, $qrCode->version);
        $this->assertSame('active', $qrCode->status);
        Storage::disk('local')->assertExists($qrCode->storage_path);
        $this->assertStringContainsString('/trees/T-1001', $qrCode->public_url);

        $originalPath = $qrCode->storage_path;
        $qrCode = app(GenerateTreeQrCode::class)->handle($tree->refresh(), $administrator);

        $this->assertSame(2, $qrCode->version);
        Storage::disk('local')->assertMissing($originalPath);
        Storage::disk('local')->assertExists($qrCode->storage_path);

        app(UpdateTreeQrCodeStatus::class)->handle($qrCode, 'inactive', $administrator);
        $this->assertSame('inactive', $qrCode->refresh()->status);

        $response = app(DownloadTreeQrCode::class)->handle($qrCode, $administrator);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(1, $qrCode->refresh()->print_count);
        $this->assertNotNull($qrCode->last_printed_at);
    }

    public function test_qr_code_management_requires_permission(): void
    {
        $administrator = User::factory()->create();
        $administrator->assignRole(RoleName::Administrator->value);
        $regularUser = User::factory()->create();
        $regularUser->assignRole(RoleName::User->value);
        $tree = Tree::factory()->verified()->create();
        $qrCode = app(GenerateTreeQrCode::class)->handle($tree, $administrator);
        $policy = app(TreeQrCodePolicy::class);

        $this->assertTrue($policy->viewAny($administrator));
        $this->assertTrue($policy->update($administrator, $qrCode));
        $this->assertFalse($policy->viewAny($regularUser));
        $this->assertFalse($policy->update($regularUser, $qrCode));
    }

    public function test_authorized_user_can_open_qr_code_management_page(): void
    {
        $administrator = User::factory()->create();
        $administrator->assignRole(RoleName::Administrator->value);
        $this->actingAs($administrator);

        Livewire::test(ListTreeQrCodes::class)->assertOk();
    }

    public function test_bulk_command_generates_missing_qr_code_images(): void
    {
        $administrator = User::factory()->create();
        $administrator->assignRole(RoleName::Administrator->value);
        $tree = Tree::factory()->verified()->create(['tree_code' => 'T-2001']);

        $this->artisan('trees:generate-qr-codes')->assertSuccessful();

        $qrCode = $tree->refresh()->qrCode;
        $this->assertNotNull($qrCode);
        Storage::disk('local')->assertExists($qrCode->storage_path);
    }
}
