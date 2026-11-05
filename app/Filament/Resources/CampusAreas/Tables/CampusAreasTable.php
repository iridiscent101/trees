<?php

namespace App\Filament\Resources\CampusAreas\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CampusAreasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                TextColumn::make('code')->searchable()->sortable()->copyable()->weight('semibold'),
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('trees_count')->label('Trees')->counts('trees')->sortable()->alignCenter(),
                IconColumn::make('is_active')->label('Active')->boolean()->alignCenter(),
                TextColumn::make('updated_at')->label('Updated')->since()->dateTimeTooltip()->sortable()->toggleable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ;
    }
}
