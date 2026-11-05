<?php

namespace App\Filament\Resources\CampusAreas\Pages;

use App\Filament\Resources\CampusAreas\CampusAreaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCampusAreas extends ListRecords
{
    protected static string $resource = CampusAreaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
