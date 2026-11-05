<?php

namespace App\Filament\Resources\TreeMonitorings\Actions;

use App\Actions\ReviewTreeMonitoring;
use App\Enums\PermissionName;
use App\Enums\VerificationStatus;
use App\Models\TreeMonitoring;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class TreeMonitoringActions
{
    public static function verify(): Action
    {
        return Action::make('verify')->label('Verify')->icon(Heroicon::OutlinedCheckCircle)->color('success')
            ->visible(fn (TreeMonitoring $record): bool => $record->verification_status !== VerificationStatus::Verified && self::canVerify())
            ->schema([
                Toggle::make('is_public')->label('Show on the public tree profile')->default(true),
                Textarea::make('remarks')->label('Verification remarks')->rows(3),
            ])->requiresConfirmation()
            ->action(function (array $data, TreeMonitoring $record): void {
                app(ReviewTreeMonitoring::class)->handle($record, VerificationStatus::Verified, auth()->user(), $data['remarks'] ?? null, (bool) ($data['is_public'] ?? false));
                Notification::make()->success()->title('Monitoring inspection verified')->send();
            });
    }

    public static function needsCorrection(): Action
    {
        return self::reviewAction('needsCorrection', 'Needs correction', VerificationStatus::NeedsCorrection, 'warning');
    }

    public static function reject(): Action
    {
        return self::reviewAction('reject', 'Reject', VerificationStatus::Rejected, 'danger');
    }

    private static function reviewAction(string $name, string $label, VerificationStatus $status, string $color): Action
    {
        return Action::make($name)->label($label)->color($color)
            ->visible(fn (TreeMonitoring $record): bool => $record->verification_status !== VerificationStatus::Verified && self::canVerify())
            ->schema([Textarea::make('remarks')->label('Review remarks')->required()->rows(4)])
            ->requiresConfirmation()
            ->action(function (array $data, TreeMonitoring $record) use ($status, $label): void {
                app(ReviewTreeMonitoring::class)->handle($record, $status, auth()->user(), $data['remarks']);
                Notification::make()->success()->title("Inspection marked {$label}")->send();
            });
    }

    private static function canVerify(): bool
    {
        return auth()->user()?->can(PermissionName::VerifyMonitoring->value) ?? false;
    }
}
