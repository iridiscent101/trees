<?php

namespace App\Filament\Resources\TreePhotos\Pages;

use App\Filament\Resources\TreePhotos\TreePhotoResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTreePhoto extends ViewRecord
{
    protected static string $resource = TreePhotoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
