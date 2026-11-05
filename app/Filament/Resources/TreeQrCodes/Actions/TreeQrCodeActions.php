<?php

namespace App\Filament\Resources\TreeQrCodes\Actions;

use App\Actions\DownloadTreeQrCode;
use App\Actions\GenerateTreeQrCode;
use App\Actions\UpdateTreeQrCodeStatus;
use App\Models\TreeQrCode;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Throwable;

class TreeQrCodeActions
{
    public static function regenerate(): Action
    {
        return Action::make('regenerate')
            ->label('Regenerate')
            ->icon(Heroicon::OutlinedArrowPath)
            ->requiresConfirmation()
            ->modalDescription('This creates a new QR image version. The encoded public profile URL remains the same.')
            ->action(function (TreeQrCode $record, Action $action): void {
                try {
                    app(GenerateTreeQrCode::class)->handle($record->tree, auth()->user());
                } catch (Throwable $exception) {
                    report($exception);
                    Notification::make()->danger()->title('QR code generation failed')->body($exception->getMessage())->send();
                    $action->halt(true);

                    return;
                }

                Notification::make()->success()->title('QR code regenerated')->send();
            });
    }

    public static function download(): Action
    {
        return Action::make('download')
            ->label('Download SVG')
            ->icon(Heroicon::OutlinedArrowDownTray)
            ->color('success')
            ->visible(fn (TreeQrCode $record): bool => filled($record->storage_path))
            ->action(fn (TreeQrCode $record) => app(DownloadTreeQrCode::class)->handle($record, auth()->user()));
    }

    public static function deactivate(): Action
    {
        return self::statusAction('deactivate', 'Deactivate', 'inactive', 'warning', Heroicon::OutlinedNoSymbol)
            ->visible(fn (TreeQrCode $record): bool => $record->status === 'active');
    }

    public static function activate(): Action
    {
        return self::statusAction('activate', 'Activate', 'active', 'success', Heroicon::OutlinedCheckCircle)
            ->visible(fn (TreeQrCode $record): bool => $record->status === 'inactive');
    }

    private static function statusAction(string $name, string $label, string $status, string $color, Heroicon $icon): Action
    {
        return Action::make($name)
            ->label($label)
            ->icon($icon)
            ->color($color)
            ->requiresConfirmation()
            ->action(function (TreeQrCode $record) use ($status, $label): void {
                app(UpdateTreeQrCodeStatus::class)->handle($record, $status, auth()->user());
                Notification::make()->success()->title("QR code {$label}d")->send();
            });
    }
}
