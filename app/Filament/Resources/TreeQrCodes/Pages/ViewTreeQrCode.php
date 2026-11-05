<?php

namespace App\Filament\Resources\TreeQrCodes\Pages;

use App\Filament\Resources\TreeQrCodes\TreeQrCodeResource;
use Filament\Resources\Pages\ViewRecord;

class ViewTreeQrCode extends ViewRecord
{
    protected static string $resource = TreeQrCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
