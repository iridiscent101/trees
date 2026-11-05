<?php

namespace App\Filament\Resources\Species\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SpeciesInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Taxonomy')->columns(3)->schema([
                TextEntry::make('common_name')->label('Common name')->weight('bold'),
                TextEntry::make('scientific_name')->label('Scientific name')->extraAttributes(['class' => 'italic']),
                TextEntry::make('local_name')->label('Local name')->placeholder('Not recorded'),
                TextEntry::make('family_name')->label('Family'),
                TextEntry::make('classification')->placeholder('Not recorded'),
                TextEntry::make('conservation_status')->label('Conservation')->badge(),
            ]),
            Section::make('Reference information')->columns(2)->schema([
                TextEntry::make('origin')->placeholder('Not recorded'),
                TextEntry::make('typical_lifespan')->label('Typical lifespan')->placeholder('Not recorded'),
                TextEntry::make('description')->placeholder('No description provided')->columnSpanFull(),
                TextEntry::make('educational_value')->label('Educational value')->placeholder('Not provided')->columnSpanFull(),
                TextEntry::make('trees_count')->label('Registered trees')->state(fn ($record) => $record->trees()->count()),
                IconEntry::make('is_active')->label('Available for registration')->boolean(),
            ]),
        ]);
    }
}
