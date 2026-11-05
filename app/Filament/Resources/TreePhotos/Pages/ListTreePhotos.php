<?php

namespace App\Filament\Resources\TreePhotos\Pages;

use App\Filament\Resources\TreePhotos\TreePhotoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTreePhotos extends ListRecords
{
    protected static string $resource = TreePhotoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
