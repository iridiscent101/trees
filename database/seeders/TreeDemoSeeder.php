<?php

namespace Database\Seeders;

use App\Models\CampusArea;
use App\Models\Species;
use App\Models\Tree;
use App\Models\TreeMonitoring;
use App\Models\TreeQrCode;
use App\Models\TreeVerification;
use App\Models\User;
use Illuminate\Database\Seeder;

class TreeDemoSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@test.com')->first();
        $rows = [
            ['T-0001', 'Pterocarpus indicus', 'FOR', 'College of Forestry', 16.4809100, 121.1412200, 'healthy', true],
            ['T-0002', 'Acacia mangium', 'LIVESTOCK', 'NVSU Livestock Area', 16.4811300, 121.1422800, 'fair', true],
            ['T-0003', 'Swietenia macrophylla', 'ADMIN', 'Administration Building', 16.4804200, 121.1431800, 'healthy', true],
            ['T-0004', 'Annona muricata', 'ADMIN', 'Back of CAS Building', 16.4799400, 121.1426100, 'fair', false],
            ['T-0005', 'Pithecellobium dulce', 'ENTRANCE', 'NVSU Main Entrance', 16.4787800, 121.1415200, 'poor', false],
            ['T-0006', 'Mangifera indica', 'STUDENT', 'Student Dormitory Area', 16.4793100, 121.1439800, 'healthy', false],
            ['T-0007', 'Plumeria rubra', 'OVAL', 'NVSU Oval', 16.4784400, 121.1430800, 'fair', false],
            ['T-0008', 'Terminalia catappa', 'OPEN', 'Open Field', 16.4800300, 121.1409200, 'healthy', false],
        ];

        foreach ($rows as [$code,$scientific,$areaCode,$location,$lat,$lng,$condition,$featured]) {
            $tree = Tree::updateOrCreate(['tree_code' => $code], [
                'species_id' => Species::where('scientific_name', $scientific)->value('id'),
                'campus_area_id' => CampusArea::where('code', $areaCode)->value('id'),
                'descriptive_location' => $location, 'latitude' => $lat, 'longitude' => $lng,
                'current_condition' => $condition, 'status' => 'existing', 'verification_status' => 'verified',
                'recorded_by' => $admin?->id, 'verified_by' => $admin?->id, 'verified_at' => now(),
                'published_at' => now(), 'is_featured' => $featured,
            ]);

            TreeMonitoring::updateOrCreate(['tree_id' => $tree->id, 'observed_at' => '2026-05-15 09:00:00'], [
                'condition' => $condition, 'remarks' => 'Initial verified inventory observation.', 'public_notes' => 'Condition verified during the campus inventory.',
                'is_public' => true, 'verification_status' => 'verified', 'recorded_by' => $admin?->id, 'verified_by' => $admin?->id, 'verified_at' => now(),
            ]);
            TreeVerification::updateOrCreate(['tree_id' => $tree->id, 'tree_monitoring_id' => null], [
                'verifier_id' => $admin?->id, 'status' => 'verified', 'remarks' => 'Initial record approved for public display.', 'decided_at' => now(),
            ]);
            TreeQrCode::updateOrCreate(['tree_id' => $tree->id], [
                'public_url' => rtrim(config('app.url'), '/').'/trees/'.$code, 'status' => 'active', 'generated_by' => $admin?->id, 'generated_at' => now(),
            ]);
        }
    }
}
