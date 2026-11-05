<?php

namespace App\Filament\Resources\TreeMonitorings\Pages;

use App\Filament\Resources\TreeMonitorings\TreeMonitoringResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTreeMonitorings extends ListRecords
{
    protected static string $resource = TreeMonitoringResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
