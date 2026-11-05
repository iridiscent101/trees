<?php

namespace App\Filament\Resources\Trees\Pages;

use App\Filament\Resources\Trees\TreeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTree extends CreateRecord
{
    protected static string $resource = TreeResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['recorded_by'] = auth()->id();

        return $data;
    }
}
