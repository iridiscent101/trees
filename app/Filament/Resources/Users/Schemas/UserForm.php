<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\PermissionName;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('User account')->columns(2)->schema([
                TextInput::make('name')->required()->maxLength(255),
                TextInput::make('email')->email()->required()->maxLength(255)->unique(ignoreRecord: true),
                TextInput::make('password')->password()->revealable()->minLength(8)
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->helperText('Leave blank while editing to keep the current password.'),
                Select::make('roles')->relationship('roles', 'name')->multiple()->preload()->searchable()
                    ->visible(fn (): bool => auth()->user()?->can(PermissionName::ManageRoles->value) ?? false),
            ]),
        ]);
    }
}
