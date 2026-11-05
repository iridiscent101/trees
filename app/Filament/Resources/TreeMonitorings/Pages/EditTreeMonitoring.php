<?php

namespace App\Filament\Resources\TreeMonitorings\Pages;

use App\Filament\Resources\TreeMonitorings\TreeMonitoringResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditTreeMonitoring extends EditRecord
{
    protected static string $resource = TreeMonitoringResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
