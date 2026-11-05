<?php

namespace App\Filament\Resources\TreeQrCodes;

use App\Filament\Resources\TreeQrCodes\Pages\CreateTreeQrCode;
use App\Filament\Resources\TreeQrCodes\Pages\ListTreeQrCodes;
use App\Filament\Resources\TreeQrCodes\Pages\ViewTreeQrCode;
use App\Filament\Resources\TreeQrCodes\Schemas\TreeQrCodeForm;
use App\Filament\Resources\TreeQrCodes\Schemas\TreeQrCodeInfolist;
use App\Filament\Resources\TreeQrCodes\Tables\TreeQrCodesTable;
use App\Models\TreeQrCode;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TreeQrCodeResource extends Resource
{
    protected static ?string $model = TreeQrCode::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQrCode;

    protected static ?string $navigationLabel = 'QR Codes';

    protected static ?string $modelLabel = 'tree QR code';

    protected static ?string $recordTitleAttribute = 'public_url';

    public static function form(Schema $schema): Schema
    {
        return TreeQrCodeForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TreeQrCodeInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TreeQrCodesTable::configure($table);
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
            'index' => ListTreeQrCodes::route('/'),
            'create' => CreateTreeQrCode::route('/create'),
            'view' => ViewTreeQrCode::route('/{record}'),
        ];
    }
}
