<?php

namespace App\Filament\Resources\TreeMonitorings\Pages;

use App\Filament\Resources\TreeMonitorings\Actions\TreeMonitoringActions;
use App\Filament\Resources\TreeMonitorings\TreeMonitoringResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTreeMonitoring extends ViewRecord
{
    protected static string $resource = TreeMonitoringResource::class;

    protected function getHeaderActions(): array
    {
        return [
            TreeMonitoringActions::verify(),
            TreeMonitoringActions::needsCorrection(),
            TreeMonitoringActions::reject(),
            EditAction::make(),
        ];
    }
}
