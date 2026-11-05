<?php

namespace App\Filament\Resources\TreeQrCodes\Pages;

use App\Actions\GenerateTreeQrCode;
use App\Filament\Resources\TreeQrCodes\TreeQrCodeResource;
use App\Models\Tree;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateTreeQrCode extends CreateRecord
{
    protected static string $resource = TreeQrCodeResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(GenerateTreeQrCode::class)->handle(
            Tree::query()->findOrFail($data['tree_id']),
            auth()->user(),
        );
    }
}
