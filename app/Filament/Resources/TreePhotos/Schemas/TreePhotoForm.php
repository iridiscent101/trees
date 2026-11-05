<?php

namespace App\Filament\Resources\TreePhotos\Schemas;

use App\Enums\PhotoType;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TreePhotoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Tree photograph')->columns(2)->schema([
                Select::make('tree_id')->label('Tree')->relationship('tree', 'tree_code')->searchable()->preload()->required(),
                Select::make('type')->options(PhotoType::class)->default(PhotoType::Tree)->required()->native(false),
                FileUpload::make('path')->label('Image')->image()
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])->maxSize(4096)
                    ->disk('public')->directory('trees')->visibility('public')->required()->columnSpanFull(),
                TextInput::make('alt_text')->label('Alternative text')->maxLength(255)->required(),
                TextInput::make('sort_order')->label('Display order')->integer()->minValue(0)->default(0),
                Textarea::make('caption')->rows(3)->columnSpanFull(),
                Toggle::make('is_primary')->label('Use as primary public photo')->default(false)->columnSpanFull(),
                Hidden::make('disk')->default('public'),
                Hidden::make('uploaded_by')->default(fn () => auth()->id()),
            ]),
        ]);
    }
}
