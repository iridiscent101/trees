<?php

namespace Database\Seeders;

use App\Models\CampusArea;
use App\Models\Species;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TreeReferenceSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['FOR', 'College of Forestry'], ['ADMIN', 'Administration Area'], ['AGRI', 'Agriculture Area'],
            ['ENG', 'Engineering Area'], ['OVAL', 'NVSU Oval'], ['STUDENT', 'Student Center'],
            ['LIVESTOCK', 'Livestock Area'], ['ENTRANCE', 'Main Entrance'], ['OPEN', 'Open Field'],
        ] as [$code, $name]) {
            CampusArea::updateOrCreate(['code' => $code], ['name' => $name, 'slug' => Str::slug($name), 'is_active' => true]);
        }

        foreach ([
            ['Narra', 'Pterocarpus indicus', 'Fabaceae', 'least_concern'],
            ['Acacia', 'Acacia mangium', 'Fabaceae', 'least_concern'],
            ['Mahogany', 'Swietenia macrophylla', 'Meliaceae', 'vulnerable'],
            ['Guyabano', 'Annona muricata', 'Annonaceae', 'least_concern'],
            ['Camachile', 'Pithecellobium dulce', 'Fabaceae', 'least_concern'],
            ['Manggahan', 'Mangifera indica', 'Anacardiaceae', 'data_deficient'],
            ['Kalachuchi', 'Plumeria rubra', 'Apocynaceae', 'least_concern'],
            ['Talisay', 'Terminalia catappa', 'Combretaceae', 'least_concern'],
        ] as [$common, $scientific, $family, $conservation]) {
            Species::updateOrCreate(
                ['scientific_name' => $scientific],
                ['common_name' => $common, 'family_name' => $family, 'conservation_status' => $conservation, 'is_active' => true],
            );
        }
    }
}
