<?php

namespace App\Filament\Resources\TreeSurveyPoints\Schemas;

use App\Models\TreeSurveyPoint;
use App\Support\Survey\SurveyReviewReadiness;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TreeSurveyPointInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Survey record')->compact()->columns(3)->schema([
                TextEntry::make('source_row')->label('Survey ID')->formatStateUsing(fn (int $state): string => 'S-'.str_pad((string) $state, 4, '0', STR_PAD_LEFT))->badge()->copyable(),
                TextEntry::make('raw_species_name')->label('Recorded species')->placeholder('Unidentified tree')->weight('bold'),
                TextEntry::make('height_m')->label('Recorded height')->suffix(' m')->placeholder('Not measured'),
                TextEntry::make('source_file')->label('Source file'),
                TextEntry::make('coordinate_status')->label('Coordinate status')->badge(),
                TextEntry::make('review_status')->label('Review status')->badge(),
                TextEntry::make('approved_species_name')->label('Approved species')->placeholder('Needs resolution'),
                TextEntry::make('campusArea.name')->label('Campus area')->placeholder('Unassigned'),
                TextEntry::make('review_readiness')
                    ->label('Approval readiness')
                    ->state(fn (TreeSurveyPoint $record): string => app(SurveyReviewReadiness::class)->isReady($record) ? 'Ready' : 'Blocked')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'Ready' ? 'success' : 'warning')
                    ->helperText(fn (TreeSurveyPoint $record): string => implode(' ', app(SurveyReviewReadiness::class)->issues($record))),
            ]),
            Section::make('Coordinates')->compact()->columns(2)->schema([
                TextEntry::make('easting')->copyable()->placeholder('Missing'),
                TextEntry::make('northing')->copyable()->placeholder('Missing'),
                TextEntry::make('latitude')->copyable()->placeholder('Missing'),
                TextEntry::make('longitude')->copyable()->placeholder('Missing'),
                TextEntry::make('map_link')->label('Map preview')->state('Open location in OpenStreetMap')
                    ->url(fn ($record): ?string => $record->latitude && $record->longitude
                        ? "https://www.openstreetmap.org/?mlat={$record->latitude}&mlon={$record->longitude}#map=20/{$record->latitude}/{$record->longitude}"
                        : null)->openUrlInNewTab()->columnSpanFull(),
            ]),
            Section::make('Review outcome')->compact()->columns(3)->schema([
                TextEntry::make('registeredTree.tree_code')->label('Registered tree')->badge()->placeholder('Not registered')
                    ->url(fn ($record): ?string => $record->registeredTree
                        ? \App\Filament\Resources\Trees\TreeResource::getUrl('view', ['record' => $record->registeredTree])
                        : null),
                TextEntry::make('reviewer.name')->label('Reviewed by')->placeholder('Not reviewed'),
                TextEntry::make('reviewed_at')->label('Reviewed at')->dateTime()->placeholder('Not reviewed'),
                TextEntry::make('review_notes')->label('Review notes')->placeholder('No review notes')->columnSpanFull(),
            ]),
        ]);
    }
}
