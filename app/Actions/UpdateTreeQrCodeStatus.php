<?php

namespace App\Actions;

use App\Models\TreeQrCode;
use App\Models\User;
use InvalidArgumentException;

class UpdateTreeQrCodeStatus
{
    public function handle(TreeQrCode $qrCode, string $status, User $user): TreeQrCode
    {
        if (! in_array($status, ['active', 'inactive'], true)) {
            throw new InvalidArgumentException('The QR code status is invalid.');
        }

        $qrCode->update(['status' => $status]);

        activity('qr-codes')
            ->causedBy($user)
            ->performedOn($qrCode)
            ->withProperties(['tree_code' => $qrCode->tree->tree_code, 'status' => $status])
            ->log($status === 'active' ? 'Activated a tree QR code.' : 'Deactivated a tree QR code.');

        return $qrCode->refresh();
    }
}
