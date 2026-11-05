<?php

namespace App\Filament\Resources\CampusAreas\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CampusAreaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Campus area')->columns(2)->schema([
                TextInput::make('code')->required()->maxLength(20)->unique(ignoreRecord: true),
                TextInput::make('name')->required()->maxLength(255)->unique(ignoreRecord: true),
                TextInput::make('slug')->required()->maxLength(255)->unique(ignoreRecord: true),
                Toggle::make('is_active')->label('Available for tree registration')->default(true)->required(),
                Textarea::make('description')->rows(4)->columnSpanFull(),
                TextInput::make('center_latitude')->label('Center latitude')->numeric()->minValue(-90)->maxValue(90),
                TextInput::make('center_longitude')->label('Center longitude')->numeric()->minValue(-180)->maxValue(180),
            ]),
        ]);
    }
}
