<?php

namespace App\Filament\Resources\TreeSurveyPoints\Actions;

use App\Actions\ImportTreeSurveyWorkbook;
use App\Enums\PermissionName;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class ImportTreeSurveyAction extends Action
{
    public static function getDefaultName(): string
    {
        return 'importExcel';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Import Excel')
            ->icon(Heroicon::OutlinedArrowUpTray)
            ->visible(fn (): bool => self::canImportSurveyRecords())
            ->modalHeading('Import tree survey workbook')
            ->modalDescription('Upload an XLS or XLSX workbook. Imported rows are added to Survey Records for review before they can become verified trees.')
            ->modalSubmitActionLabel('Import workbook')
            ->schema([
                FileUpload::make('workbook')
                    ->label('Excel workbook')
                    ->acceptedFileTypes([
                        'application/vnd.ms-excel',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    ])
                    ->disk('local')
                    ->directory('imports/tree-surveys')
                    ->visibility('private')
                    ->storeFileNamesIn('original_file_name')
                    ->maxSize(10240)
                    ->required(),
            ])
            ->action(function (array $data): void {
                abort_unless(self::canImportSurveyRecords(), 403);

                $uploadedPath = (string) $data['workbook'];

                try {
                    $result = app(ImportTreeSurveyWorkbook::class)->handle(
                        Storage::disk('local')->path($uploadedPath),
                        (string) ($data['original_file_name'] ?? basename($uploadedPath)),
                    );

                    activity('survey-records')
                        ->causedBy(auth()->user())
                        ->withProperties(['source_file' => $data['original_file_name'] ?? basename($uploadedPath), ...$result])
                        ->log('Imported a tree survey workbook.');

                    Notification::make()
                        ->success()
                        ->title('Excel import completed')
                        ->body("Imported {$result['total']} survey rows. Review them under Survey Records.")
                        ->send();
                } catch (RuntimeException $exception) {
                    Notification::make()
                        ->danger()
                        ->title('Excel import failed')
                        ->body($exception->getMessage())
                        ->send();
                } catch (Throwable $exception) {
                    report($exception);

                    Notification::make()
                        ->danger()
                        ->title('Excel import failed')
                        ->body('The workbook could not be imported. Check the file and try again.')
                        ->send();
                } finally {
                    Storage::disk('local')->delete($uploadedPath);
                }
            });
    }

    private static function canImportSurveyRecords(): bool
    {
        return auth()->user()?->can(PermissionName::ManageVerifications->value) ?? false;
    }
}
