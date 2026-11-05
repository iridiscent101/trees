<?php

namespace App\Filament\Resources\Trees\Pages;

use App\Filament\Resources\Trees\TreeResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTree extends ViewRecord
{
    protected static string $resource = TreeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
