<?php

namespace App\Actions;

use App\Models\TreeQrCode;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadTreeQrCode
{
    public function handle(TreeQrCode $qrCode, User $user): StreamedResponse
    {
        if (! $qrCode->storage_path || ! Storage::disk('local')->exists($qrCode->storage_path)) {
            throw new RuntimeException('Generate the QR code image before downloading it.');
        }

        $qrCode->forceFill(['last_printed_at' => now()])->save();
        $qrCode->increment('print_count');

        activity('qr-codes')
            ->causedBy($user)
            ->performedOn($qrCode)
            ->withProperties(['tree_code' => $qrCode->tree->tree_code])
            ->log('Downloaded a tree QR code for printing.');

        return Storage::disk('local')->download(
            $qrCode->storage_path,
            $qrCode->tree->tree_code.'-qr-code.svg',
            ['Content-Type' => 'image/svg+xml'],
        );
    }
}
