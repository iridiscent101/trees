<?php

namespace App\Actions;

use App\Enums\PhotoType;
use App\Enums\VerificationStatus;
use App\Models\TreeMonitoring;
use App\Models\TreePhoto;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class RecordTreeMonitoring
{
    /** @param array<string, mixed> $data */
    public function handle(array $data, User $recorder): TreeMonitoring
    {
        $photoPaths = Arr::pull($data, 'photo_paths', []);
        $data['recorded_by'] = $recorder->getKey();
        $data['verification_status'] = VerificationStatus::Pending;
        $data['is_public'] = false;

        $monitoring = DB::transaction(function () use ($data, $photoPaths, $recorder): TreeMonitoring {
            $monitoring = TreeMonitoring::query()->create($data);

            foreach (Arr::wrap($photoPaths) as $index => $path) {
                TreePhoto::query()->create([
                    'tree_id' => $monitoring->tree_id, 'tree_monitoring_id' => $monitoring->getKey(),
                    'type' => PhotoType::Monitoring, 'disk' => 'public', 'path' => $path,
                    'is_primary' => false, 'sort_order' => $index, 'uploaded_by' => $recorder->getKey(),
                ]);
            }

            return $monitoring;
        });

        activity('monitoring')->causedBy($recorder)->performedOn($monitoring)
            ->withProperties(['tree_id' => $monitoring->tree_id])->log('Recorded a tree monitoring inspection.');

        return $monitoring->fresh(['tree', 'photos', 'recorder']);
    }
}
