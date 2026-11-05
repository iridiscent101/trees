<?php

namespace App\Filament\Resources\TreeQrCodes\Pages;

use App\Filament\Resources\TreeQrCodes\TreeQrCodeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTreeQrCodes extends ListRecords
{
    protected static string $resource = TreeQrCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
