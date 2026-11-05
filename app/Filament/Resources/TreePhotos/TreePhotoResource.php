<?php

namespace App\Filament\Resources\TreePhotos;

use App\Filament\Resources\TreePhotos\Pages\CreateTreePhoto;
use App\Filament\Resources\TreePhotos\Pages\EditTreePhoto;
use App\Filament\Resources\TreePhotos\Pages\ListTreePhotos;
use App\Filament\Resources\TreePhotos\Pages\ViewTreePhoto;
use App\Filament\Resources\TreePhotos\Schemas\TreePhotoForm;
use App\Filament\Resources\TreePhotos\Schemas\TreePhotoInfolist;
use App\Filament\Resources\TreePhotos\Tables\TreePhotosTable;
use App\Models\TreePhoto;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TreePhotoResource extends Resource
{
    protected static ?string $model = TreePhoto::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static ?string $navigationLabel = 'Tree Photos';

    protected static ?string $modelLabel = 'tree photo';

    protected static ?string $recordTitleAttribute = 'alt_text';

    public static function form(Schema $schema): Schema
    {
        return TreePhotoForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TreePhotoInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TreePhotosTable::configure($table);
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
            'index' => ListTreePhotos::route('/'),
            'create' => CreateTreePhoto::route('/create'),
            'view' => ViewTreePhoto::route('/{record}'),
            'edit' => EditTreePhoto::route('/{record}/edit'),
        ];
    }
}
