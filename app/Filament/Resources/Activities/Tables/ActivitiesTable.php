<?php

namespace App\Filament\Resources\Activities\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ActivitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')->label('When')->dateTime()->since()->dateTimeTooltip()->sortable(),
                TextColumn::make('causer.name')->label('User')->placeholder('System')->searchable(),
                TextColumn::make('event')->badge()->placeholder('Action')->formatStateUsing(fn (?string $state): string => Str::headline($state ?? 'action')),
                TextColumn::make('description')->searchable()->wrap()->limit(60),
                TextColumn::make('subject_type')->label('Record type')->formatStateUsing(fn (?string $state): string => $state ? class_basename($state) : 'None')->toggleable(),
                TextColumn::make('subject_id')->label('Record ID')->placeholder('—')->toggleable(),
                TextColumn::make('log_name')->label('Log')->badge()->sortable(),
            ])
            ->filters([
                SelectFilter::make('log_name')->label('Log')->options([
                    'trees' => 'Trees',
                    'species' => 'Species',
                    'survey-records' => 'Survey Records',
                    'reports' => 'Reports',
                ]),
                SelectFilter::make('event')->options([
                    'created' => 'Created',
                    'updated' => 'Updated',
                    'deleted' => 'Deleted',
                    'restored' => 'Restored',
                ]),
            ])
            ->recordActions([ViewAction::make()]);
    }
}
