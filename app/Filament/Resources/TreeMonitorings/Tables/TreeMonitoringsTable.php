<?php

namespace App\Filament\Resources\TreeMonitorings\Tables;

use App\Enums\VerificationStatus;
use App\Filament\Resources\TreeMonitorings\Actions\TreeMonitoringActions;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TreeMonitoringsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('observed_at', 'desc')
            ->modifyQueryUsing(fn ($query) => $query->with(['tree.species', 'recorder', 'verifier']))
            ->columns([
                TextColumn::make('tree.tree_code')->label('Tree ID')->searchable()->sortable()->copyable()->weight('semibold'),
                TextColumn::make('tree.species.common_name')->label('Tree')->searchable()->sortable(),
                TextColumn::make('observed_at')->label('Observed')->dateTime()->since()->dateTimeTooltip()->sortable(),
                TextColumn::make('condition')->badge()->sortable(),
                TextColumn::make('verification_status')->label('Review')->badge()->sortable(),
                IconColumn::make('is_public')->label('Public')->boolean()->alignCenter(),
                TextColumn::make('recorder.name')->label('Recorded by')->toggleable(),
            ])
            ->filters([
                SelectFilter::make('verification_status')->label('Review status')->options(VerificationStatus::class)->native(false),
                SelectFilter::make('tree_id')->label('Tree')->relationship('tree', 'tree_code')->searchable()->preload(),
            ])
            ->recordActions([
                TreeMonitoringActions::verify(),
                TreeMonitoringActions::needsCorrection(),
                TreeMonitoringActions::reject(),
                ViewAction::make(),
                EditAction::make(),
            ]);
    }
}
