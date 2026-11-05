<?php

namespace App\Filament\Resources\TreePhotos\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TreePhotosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                ImageColumn::make('path')->label('Photo')->disk('public')->square(),
                TextColumn::make('tree.tree_code')->label('Tree ID')->searchable()->sortable()->copyable(),
                TextColumn::make('type')->badge()->sortable(),
                TextColumn::make('alt_text')->label('Alternative text')->searchable()->wrap(),
                IconColumn::make('is_primary')->label('Primary')->boolean()->alignCenter(),
                TextColumn::make('uploader.name')->label('Uploaded by')->placeholder('System')->toggleable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ;
    }
}
