<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')->searchable()->sortable()->weight('semibold'),
                TextColumn::make('email')->searchable()->sortable()->copyable(),
                TextColumn::make('roles.name')->label('Roles')->badge()->separator(', '),
                TextColumn::make('email_verified_at')->label('Verified')->dateTime()->placeholder('Not verified')->sortable(),
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
