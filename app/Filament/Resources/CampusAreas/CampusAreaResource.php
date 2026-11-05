<?php

namespace App\Filament\Resources\CampusAreas;

use App\Filament\Resources\CampusAreas\Pages\CreateCampusArea;
use App\Filament\Resources\CampusAreas\Pages\EditCampusArea;
use App\Filament\Resources\CampusAreas\Pages\ListCampusAreas;
use App\Filament\Resources\CampusAreas\Pages\ViewCampusArea;
use App\Filament\Resources\CampusAreas\Schemas\CampusAreaForm;
use App\Filament\Resources\CampusAreas\Schemas\CampusAreaInfolist;
use App\Filament\Resources\CampusAreas\Tables\CampusAreasTable;
use App\Models\CampusArea;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CampusAreaResource extends Resource
{
    protected static ?string $model = CampusArea::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMap;

    protected static ?string $navigationLabel = 'Campus Areas';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return CampusAreaForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CampusAreaInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CampusAreasTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCampusAreas::route('/'),
            'create' => CreateCampusArea::route('/create'),
            'view' => ViewCampusArea::route('/{record}'),
            'edit' => EditCampusArea::route('/{record}/edit'),
        ];
    }
}
