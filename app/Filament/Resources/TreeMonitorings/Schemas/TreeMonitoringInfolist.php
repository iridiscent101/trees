<?php

namespace App\Filament\Resources\TreeMonitorings\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TreeMonitoringInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Monitoring inspection')->columns(3)->schema([
                TextEntry::make('tree.tree_code')->label('Tree ID')->badge(),
                TextEntry::make('tree.species.common_name')->label('Tree'),
                TextEntry::make('observed_at')->label('Observed at')->dateTime(),
                TextEntry::make('condition')->badge(),
                TextEntry::make('trunk_diameter_cm')->label('Trunk diameter')->suffix(' cm')->placeholder('Not measured'),
                TextEntry::make('estimated_height_m')->label('Estimated height')->suffix(' m')->placeholder('Not measured'),
                TextEntry::make('remarks')->columnSpanFull(),
                TextEntry::make('public_notes')->label('Public notes')->placeholder('None')->columnSpanFull(),
            ]),
            Section::make('Review')->columns(3)->schema([
                TextEntry::make('verification_status')->label('Status')->badge(),
                TextEntry::make('recorder.name')->label('Recorded by'),
                TextEntry::make('verifier.name')->label('Verified by')->placeholder('Not verified'),
                TextEntry::make('verified_at')->label('Verified at')->dateTime()->placeholder('Not verified'),
                TextEntry::make('is_public')->label('Visibility')->formatStateUsing(fn (bool $state): string => $state ? 'Public' : 'Internal')->badge(),
            ]),
            Section::make('Photos')->schema([
                ImageEntry::make('photos.path')->label('Inspection photos')->disk('public')->height(180)->stacked(),
            ]),
        ]);
    }
}
