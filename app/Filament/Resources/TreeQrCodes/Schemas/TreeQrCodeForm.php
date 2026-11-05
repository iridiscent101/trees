<?php

namespace App\Filament\Resources\TreeQrCodes\Schemas;

use App\Models\Tree;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TreeQrCodeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Tree QR code')
                ->description('Select a registered tree. The QR code will open its public profile when scanned.')
                ->schema([
                    Select::make('tree_id')
                        ->label('Tree')
                        ->relationship(
                            'tree',
                            'tree_code',
                            modifyQueryUsing: fn ($query) => $query
                                ->whereNotNull('tree_code')
                                ->whereDoesntHave('qrCode')
                                ->orderBy('tree_code'),
                        )
                        ->getOptionLabelFromRecordUsing(fn (Tree $tree): string => "{$tree->tree_code} — {$tree->species->common_name}")
                        ->searchable(['tree_code'])
                        ->preload()
                        ->required(),
                ]),
        ]);
    }
}
