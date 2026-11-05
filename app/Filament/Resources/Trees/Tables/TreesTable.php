<?php

namespace App\Filament\Resources\Trees\Tables;

use App\Enums\TreeCondition;
use App\Enums\TreeStatus;
use App\Enums\VerificationStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class TreesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('tree_code')
            ->columns([
                TextColumn::make('tree_code')->label('Tree ID')->searchable()->sortable()->copyable()->weight('semibold'),
                TextColumn::make('species.common_name')->label('Tree')->description(fn ($record): string => $record->species->scientific_name)->searchable(['common_name', 'scientific_name'])->sortable(),
                TextColumn::make('campusArea.name')->label('Campus area')->searchable()->sortable(),
                TextColumn::make('descriptive_location')->label('Location')->searchable()->limit(32)->tooltip(fn ($state): string => $state),
                TextColumn::make('current_condition')->label('Condition')->badge()->color(fn (TreeCondition $state): string => match ($state) {
                    TreeCondition::Healthy => 'success', TreeCondition::Fair => 'warning', TreeCondition::Poor, TreeCondition::Damaged => 'danger', TreeCondition::Diseased => 'purple', TreeCondition::Dead => 'gray', TreeCondition::Other => 'info',
                })->sortable(),
                TextColumn::make('verification_status')->label('Verification')->badge()->color(fn (VerificationStatus $state): string => match ($state) {
                    VerificationStatus::Verified => 'success', VerificationStatus::Pending => 'warning', VerificationStatus::NeedsCorrection => 'info', VerificationStatus::Rejected => 'danger',
                })->sortable(),
                TextColumn::make('status')->badge()->sortable(),
                IconColumn::make('is_featured')->label('Featured')->boolean()->alignCenter()->toggleable(),
                TextColumn::make('updated_at')->label('Updated')->since()->dateTimeTooltip()->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')->label('Archived')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('campus_area_id')->label('Campus area')->relationship('campusArea', 'name')->searchable()->preload(),
                SelectFilter::make('species_id')->label('Species')->relationship('species', 'common_name')->searchable()->preload(),
                SelectFilter::make('current_condition')->label('Condition')->options(TreeCondition::class)->native(false),
                SelectFilter::make('verification_status')->label('Verification')->options(VerificationStatus::class)->native(false),
                SelectFilter::make('status')->options(TreeStatus::class)->native(false),
                TrashedFilter::make()->label('Archived records'),
            ])
            ->recordActions([
                ViewAction::make(), EditAction::make(), DeleteAction::make()->label('Archive'), RestoreAction::make(), ForceDeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Archive selected'), RestoreBulkAction::make(), ForceDeleteBulkAction::make(),
                ]),
            ]);
    }
}
