<?php

namespace App\Filament\Resources\Species\Tables;

use App\Enums\ConservationStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SpeciesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('common_name')
            ->columns([
                TextColumn::make('common_name')->label('Common name')->searchable()->sortable()->weight('semibold'),
                TextColumn::make('scientific_name')->label('Scientific name')->searchable()->sortable()->extraAttributes(['class' => 'italic']),
                TextColumn::make('family_name')->label('Family')->searchable()->sortable(),
                TextColumn::make('conservation_status')->label('Conservation')->badge()->sortable(),
                TextColumn::make('trees_count')->label('Trees')->counts('trees')->sortable()->alignCenter(),
                IconColumn::make('is_active')->label('Active')->boolean()->alignCenter(),
                TextColumn::make('updated_at')->label('Last updated')->since()->dateTimeTooltip()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('conservation_status')->label('Conservation status')->options(ConservationStatus::class)->native(false),
                SelectFilter::make('is_active')->label('Availability')->options(['1' => 'Active', '0' => 'Inactive'])->native(false),
            ])
            ->recordActions([ViewAction::make(), EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
