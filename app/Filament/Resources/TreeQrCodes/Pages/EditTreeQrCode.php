<?php

namespace App\Filament\Resources\TreeQrCodes\Pages;

use App\Filament\Resources\TreeQrCodes\TreeQrCodeResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditTreeQrCode extends EditRecord
{
    protected static string $resource = TreeQrCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
