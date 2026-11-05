<?php

namespace App\Filament\Resources\TreeMonitorings\Pages;

use App\Actions\RecordTreeMonitoring;
use App\Filament\Resources\TreeMonitorings\TreeMonitoringResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateTreeMonitoring extends CreateRecord
{
    protected static string $resource = TreeMonitoringResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(RecordTreeMonitoring::class)->handle($data, auth()->user());
    }
}
