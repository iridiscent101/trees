<?php

namespace App\Filament\Resources\CampusAreas\Pages;

use App\Filament\Resources\CampusAreas\CampusAreaResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCampusArea extends ViewRecord
{
    protected static string $resource = CampusAreaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
