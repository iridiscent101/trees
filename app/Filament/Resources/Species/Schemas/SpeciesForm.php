<?php

namespace App\Filament\Resources\Species\Schemas;

use App\Enums\ConservationStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SpeciesForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Taxonomy')
                ->description('Core identification information reviewed by forestry personnel.')
                ->columns(2)
                ->schema([
                    TextInput::make('common_name')->label('Common name')->required()->maxLength(255),
                    TextInput::make('local_name')->label('Local name')->maxLength(255),
                    TextInput::make('scientific_name')->label('Scientific name')->required()->maxLength(255)->unique(ignoreRecord: true),
                    TextInput::make('family_name')->label('Family name')->required()->maxLength(255),
                    TextInput::make('classification')->maxLength(255),
                    Select::make('conservation_status')->label('Conservation status')->options(ConservationStatus::class)->default(ConservationStatus::NotEvaluated)->required()->native(false),
                ]),
            Section::make('Reference information')
                ->columns(2)
                ->schema([
                    TextInput::make('origin')->maxLength(255),
                    TextInput::make('typical_lifespan')->label('Typical lifespan')->placeholder('e.g. 80–100 years')->maxLength(255),
                    Textarea::make('description')->rows(5)->columnSpanFull(),
                    Textarea::make('educational_value')->label('Educational value')->rows(4)->columnSpanFull(),
                    Toggle::make('is_active')->label('Available for tree registration')->default(true)->required()->columnSpanFull(),
                ]),
        ]);
    }
}
