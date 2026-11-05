<?php

namespace App\Actions;

use App\Models\Tree;
use App\Models\TreeQrCode;
use App\Models\User;
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Output\QRMarkupSVG;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class GenerateTreeQrCode
{
    public function handle(Tree $tree, User $generator): TreeQrCode
    {
        if (! $tree->tree_code) {
            throw new RuntimeException('The tree must have a Tree ID before a QR code can be generated.');
        }

        $existingQrCode = $tree->qrCode;
        $version = $existingQrCode?->storage_path
            ? $existingQrCode->version + 1
            : max(1, $existingQrCode?->version ?? 1);
        $publicUrl = rtrim(config('app.url'), '/').route('trees.show', $tree->tree_code, false);
        $storagePath = "tree-qr-codes/{$tree->tree_code}-v{$version}.svg";
        $svg = (new QRCode(new QROptions([
            'outputInterface' => QRMarkupSVG::class,
            'outputBase64' => false,
            'eccLevel' => EccLevel::M,
            'addQuietzone' => true,
            'quietzoneSize' => 4,
        ])))->render($publicUrl);

        if (! Storage::disk('local')->put($storagePath, $svg)) {
            throw new RuntimeException('The QR code image could not be stored.');
        }

        try {
            $qrCode = DB::transaction(fn (): TreeQrCode => TreeQrCode::query()->updateOrCreate(
                ['tree_id' => $tree->getKey()],
                [
                    'public_url' => $publicUrl,
                    'storage_path' => $storagePath,
                    'version' => $version,
                    'status' => 'active',
                    'generated_by' => $generator->getKey(),
                    'generated_at' => now(),
                ],
            ));
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($storagePath);

            throw $exception;
        }

        if ($existingQrCode?->storage_path && $existingQrCode->storage_path !== $storagePath) {
            Storage::disk('local')->delete($existingQrCode->storage_path);
        }

        activity('qr-codes')
            ->causedBy($generator)
            ->performedOn($qrCode)
            ->withProperties(['tree_code' => $tree->tree_code, 'version' => $version])
            ->log($existingQrCode?->storage_path ? 'Regenerated a tree QR code.' : 'Generated a tree QR code.');

        return $qrCode->fresh(['tree', 'generator']);
    }
}
