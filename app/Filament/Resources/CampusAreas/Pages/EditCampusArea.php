<?php

namespace App\Filament\Resources\CampusAreas\Pages;

use App\Filament\Resources\CampusAreas\CampusAreaResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditCampusArea extends EditRecord
{
    protected static string $resource = CampusAreaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
