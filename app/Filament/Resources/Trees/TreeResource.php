<?php

namespace App\Filament\Resources\Trees;

use App\Filament\Resources\Trees\Pages\CreateTree;
use App\Filament\Resources\Trees\Pages\EditTree;
use App\Filament\Resources\Trees\Pages\ListTrees;
use App\Filament\Resources\Trees\Pages\ViewTree;
use App\Filament\Resources\Trees\Schemas\TreeForm;
use App\Filament\Resources\Trees\Schemas\TreeInfolist;
use App\Filament\Resources\Trees\Tables\TreesTable;
use App\Models\Tree;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TreeResource extends Resource
{
    protected static ?string $model = Tree::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static ?string $recordTitleAttribute = 'tree_code';

    protected static ?string $navigationLabel = 'Tree Inventory';

    public static function form(Schema $schema): Schema
    {
        return TreeForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TreeInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TreesTable::configure($table);
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
            'index' => ListTrees::route('/'),
            'create' => CreateTree::route('/create'),
            'view' => ViewTree::route('/{record}'),
            'edit' => EditTree::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
