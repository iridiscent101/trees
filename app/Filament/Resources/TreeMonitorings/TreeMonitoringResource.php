<?php

namespace App\Filament\Resources\TreeMonitorings;

use App\Filament\Resources\TreeMonitorings\Pages\CreateTreeMonitoring;
use App\Filament\Resources\TreeMonitorings\Pages\EditTreeMonitoring;
use App\Filament\Resources\TreeMonitorings\Pages\ListTreeMonitorings;
use App\Filament\Resources\TreeMonitorings\Pages\ViewTreeMonitoring;
use App\Filament\Resources\TreeMonitorings\Schemas\TreeMonitoringForm;
use App\Filament\Resources\TreeMonitorings\Schemas\TreeMonitoringInfolist;
use App\Filament\Resources\TreeMonitorings\Tables\TreeMonitoringsTable;
use App\Models\TreeMonitoring;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TreeMonitoringResource extends Resource
{
    protected static ?string $model = TreeMonitoring::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static ?string $navigationLabel = 'Tree Monitoring';

    protected static ?string $modelLabel = 'monitoring inspection';

    protected static ?string $recordTitleAttribute = 'observed_at';

    public static function form(Schema $schema): Schema
    {
        return TreeMonitoringForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TreeMonitoringInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TreeMonitoringsTable::configure($table);
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
            'index' => ListTreeMonitorings::route('/'),
            'create' => CreateTreeMonitoring::route('/create'),
            'view' => ViewTreeMonitoring::route('/{record}'),
            'edit' => EditTreeMonitoring::route('/{record}/edit'),
        ];
    }
}
