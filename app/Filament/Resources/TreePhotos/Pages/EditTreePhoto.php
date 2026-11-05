<?php

namespace App\Filament\Resources\TreePhotos\Pages;

use App\Filament\Resources\TreePhotos\TreePhotoResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditTreePhoto extends EditRecord
{
    protected static string $resource = TreePhotoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
