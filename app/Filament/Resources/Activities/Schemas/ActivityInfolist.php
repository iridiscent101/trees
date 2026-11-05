<?php

namespace App\Filament\Resources\Activities\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Spatie\Activitylog\Models\Activity;

class ActivityInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Activity')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('created_at')->label('Date and time')->dateTime(),
                        TextEntry::make('causer.name')->label('User')->placeholder('System'),
                        TextEntry::make('log_name')->label('Log')->badge(),
                        TextEntry::make('event')->badge()->placeholder('Action'),
                        TextEntry::make('subject_type')->label('Record type')->formatStateUsing(fn (?string $state): string => $state ? class_basename($state) : 'None'),
                        TextEntry::make('subject_id')->label('Record ID')->placeholder('—'),
                        TextEntry::make('description')->columnSpanFull(),
                    ]),
                Section::make('Changed attributes')
                    ->schema([
                        TextEntry::make('attribute_changes')
                            ->label('Changes')
                            ->state(fn (Activity $record): string => json_encode($record->attribute_changes?->toArray() ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: '{}')
                            ->fontFamily('mono')
                            ->columnSpanFull(),
                    ]),
                Section::make('Additional properties')
                    ->schema([
                        TextEntry::make('properties')
                            ->label('Properties')
                            ->state(fn (Activity $record): string => json_encode($record->properties?->toArray() ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: '{}')
                            ->fontFamily('mono')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
