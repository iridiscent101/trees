<?php

namespace App\Filament\Resources\TreeSurveyPoints\Actions;

use App\Actions\RegisterSurveyTree;
use App\Actions\ReviewSurveyPoint;
use App\Enums\PermissionName;
use App\Enums\SurveyReviewStatus;
use App\Enums\TreeCondition;
use App\Filament\Resources\Trees\TreeResource;
use App\Models\CampusArea;
use App\Models\Species;
use App\Models\TreeSurveyPoint;
use App\Support\Survey\SurveyReviewReadiness;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection;
use Throwable;

class SurveyPointActions
{
    public static function registerTree(): Action
    {
        return Action::make('registerTree')
            ->label('Complete and register')
            ->icon(Heroicon::OutlinedClipboardDocumentCheck)
            ->color('success')
            ->visible(fn (TreeSurveyPoint $record): bool => $record->registered_tree_id === null
                && $record->review_status === SurveyReviewStatus::Approved
                && (auth()->user()?->can(PermissionName::ManageVerifications->value) ?? false))
            ->modalHeading(fn (TreeSurveyPoint $record): string => 'Register survey row '.$record->source_row)
            ->modalDescription('Confirm the identification and location details. This will create and publish one official tree record.')
            ->modalSubmitActionLabel('Register verified tree')
            ->modalWidth('5xl')
            ->fillForm(fn (TreeSurveyPoint $record): array => [
                'recorded_species_name' => $record->raw_species_name ?: 'Unidentified tree',
                'coordinate_status' => ucfirst($record->coordinate_status),
                'survey_latitude' => $record->latitude,
                'survey_longitude' => $record->longitude,
                'estimated_height_m' => $record->height_m,
                'campus_area_id' => $record->campus_area_id,
                'species_id' => Species::query()->where('common_name', $record->approved_species_name)->value('id'),
                'descriptive_location' => 'Survey coordinate E '.$record->easting.' / N '.$record->northing,
                'current_condition' => TreeCondition::Healthy->value,
            ])
            ->schema([
                Section::make('Original survey reference')
                    ->description('Review the imported values before confirming the official record. Outlier coordinates should be checked on the map first.')
                    ->columns(4)
                    ->schema([
                        TextInput::make('recorded_species_name')->label('Recorded species')->disabled()->dehydrated(false),
                        TextInput::make('coordinate_status')->label('Coordinate quality')->disabled()->dehydrated(false),
                        TextInput::make('survey_latitude')->label('Latitude')->disabled()->dehydrated(false),
                        TextInput::make('survey_longitude')->label('Longitude')->disabled()->dehydrated(false),
                    ]),
                Section::make('Confirmed identity and location')
                    ->columns(2)
                    ->schema([
                        Select::make('species_id')
                            ->label('Confirmed species')
                            ->options(fn (): array => Species::query()->where('is_active', true)->orderBy('common_name')->get()
                                ->mapWithKeys(fn (Species $species): array => [$species->getKey() => "{$species->common_name} ({$species->scientific_name})"])->all())
                            ->searchable()->preload()->required(),
                        Select::make('campus_area_id')
                            ->label('Campus area')
                            ->options(fn (): array => CampusArea::query()->where('is_active', true)->orderBy('name')->pluck('name', 'id')->all())
                            ->searchable()->preload()->required(),
                        TextInput::make('descriptive_location')->label('Descriptive location')->required()->maxLength(255)->columnSpanFull(),
                        TextInput::make('nearby_landmark')->label('Nearby landmark')->maxLength(255)->columnSpanFull(),
                    ]),
                Section::make('Condition and measurements')
                    ->columns(3)
                    ->schema([
                        Select::make('current_condition')->label('Current condition')->options(TreeCondition::class)->required()->native(false),
                        TextInput::make('estimated_height_m')->label('Estimated height')->numeric()->minValue(0)->maxValue(9999)->suffix('m'),
                        TextInput::make('trunk_diameter_cm')->label('Trunk diameter')->numeric()->minValue(0)->maxValue(99999)->suffix('cm'),
                        TextInput::make('estimated_age_years')->label('Estimated age')->integer()->minValue(0)->maxValue(2000)->suffix('years'),
                        FileUpload::make('photo')->label('Primary tree photo')->image()->disk('public')->directory('trees')->visibility('public')->columnSpan(2),
                    ]),
                Section::make('Notes')
                    ->columns(2)
                    ->schema([
                        Textarea::make('public_notes')->label('Public notes')->rows(3),
                        Textarea::make('internal_remarks')->label('Internal remarks')->rows(3),
                        Textarea::make('review_notes')->label('Verification notes')->rows(3)->columnSpanFull(),
                    ]),
            ])
            ->action(function (array $data, TreeSurveyPoint $record, Action $action): void {
                try {
                    $tree = app(RegisterSurveyTree::class)->handle($record, $data, auth()->user());
                } catch (Throwable $exception) {
                    report($exception);
                    Notification::make()->danger()->title('Tree registration failed')->body($exception->getMessage())->send();
                    $action->halt(true);

                    return;
                }

                Notification::make()
                    ->success()
                    ->title('Tree registered successfully')
                    ->body("{$tree->tree_code} is now verified and visible on the public website.")
                    ->send();

                $action->redirect(TreeResource::getUrl('view', ['record' => $tree]));
            });
    }

    public static function approve(): Action
    {
        return Action::make('approve')
            ->label('Approve for registration')
            ->icon(Heroicon::OutlinedCheckCircle)
            ->color('success')
            ->visible(fn (TreeSurveyPoint $record): bool => $record->registered_tree_id === null
                && in_array($record->review_status, [SurveyReviewStatus::Pending, SurveyReviewStatus::NeedsCorrection], true)
                && (auth()->user()?->can(PermissionName::ManageVerifications->value) ?? false))
            ->fillForm(fn (TreeSurveyPoint $record): array => [
                'campus_area_id' => $record->campus_area_id,
                'approved_species_name' => $record->approved_species_name
                    ?? app(SurveyReviewReadiness::class)->suggestedSpeciesName($record->raw_species_name),
                'review_notes' => $record->review_notes,
            ])
            ->schema(self::approvalSchema())
            ->requiresConfirmation()
            ->modalDescription('Approval is blocked until the coordinates, campus area, and one cleaned species name are confirmed.')
            ->action(function (array $data, TreeSurveyPoint $record, Action $action): void {
                try {
                    app(ReviewSurveyPoint::class)->approve($record, $data, auth()->user());
                } catch (Throwable $exception) {
                    Notification::make()->danger()->title('Survey record is not ready')->body($exception->getMessage())->send();
                    $action->halt(true);

                    return;
                }

                Notification::make()->success()->title('Survey record approved')->body('It can now be converted into a verified tree record.')->send();
            });
    }

    public static function needsCorrection(): Action
    {
        return self::reviewAction(
            name: 'needsCorrection',
            label: 'Needs correction',
            status: SurveyReviewStatus::NeedsCorrection,
            color: 'warning',
            icon: Heroicon::OutlinedExclamationTriangle,
        );
    }

    public static function reject(): Action
    {
        return self::reviewAction(
            name: 'reject',
            label: 'Reject record',
            status: SurveyReviewStatus::Rejected,
            color: 'danger',
            icon: Heroicon::OutlinedXCircle,
        )->requiresConfirmation();
    }

    public static function assignCampusAreaBulk(): BulkAction
    {
        return BulkAction::make('assignCampusArea')
            ->label('Assign campus area')
            ->icon(Heroicon::OutlinedMapPin)
            ->authorizeIndividualRecords('update')
            ->schema([
                Select::make('campus_area_id')
                    ->label('Campus area')
                    ->options(fn (): array => self::campusAreaOptions())
                    ->searchable()->preload()->required(),
            ])
            ->action(function (array $data, Collection $records): void {
                $records->each(fn (TreeSurveyPoint $record) => app(ReviewSurveyPoint::class)
                    ->assignCampusArea($record, (int) $data['campus_area_id'], auth()->user()));
            })
            ->successNotificationTitle('Campus area assigned');
    }

    public static function approveReadyBulk(): BulkAction
    {
        return BulkAction::make('approveReady')
            ->label('Approve ready records')
            ->icon(Heroicon::OutlinedCheckCircle)
            ->color('success')
            ->authorizeIndividualRecords('update')
            ->requiresConfirmation()
            ->modalDescription('Only records with valid unique coordinates, a campus area, and one approved species name will be approved.')
            ->action(function (BulkAction $action, Collection $records): void {
                $records->each(function (TreeSurveyPoint $record) use ($action): void {
                    try {
                        app(ReviewSurveyPoint::class)->approve($record, [
                            'campus_area_id' => $record->campus_area_id,
                            'approved_species_name' => $record->approved_species_name,
                            'review_notes' => $record->review_notes,
                        ], auth()->user());
                    } catch (Throwable) {
                        $action->reportBulkProcessingFailure(
                            'not_ready',
                            message: fn (int $failureCount, int $totalCount): string => "{$failureCount} of {$totalCount} selected records were not ready for approval.",
                        );
                    }
                });
            })
            ->successNotificationTitle('Ready survey records approved');
    }

    public static function needsCorrectionBulk(): BulkAction
    {
        return self::reviewBulkAction(
            name: 'needsCorrection',
            label: 'Mark as needs correction',
            status: SurveyReviewStatus::NeedsCorrection,
            color: 'warning',
            icon: Heroicon::OutlinedExclamationTriangle,
        );
    }

    public static function rejectBulk(): BulkAction
    {
        return self::reviewBulkAction(
            name: 'reject',
            label: 'Reject records',
            status: SurveyReviewStatus::Rejected,
            color: 'danger',
            icon: Heroicon::OutlinedXCircle,
        )->requiresConfirmation();
    }

    private static function reviewAction(string $name, string $label, SurveyReviewStatus $status, string $color, Heroicon $icon): Action
    {
        return Action::make($name)
            ->label($label)
            ->icon($icon)
            ->color($color)
            ->visible(fn (TreeSurveyPoint $record): bool => $record->registered_tree_id === null
                && $record->review_status !== $status
                && (auth()->user()?->can(PermissionName::ManageVerifications->value) ?? false))
            ->schema([
                Textarea::make('review_notes')->label('Review notes')->required()->rows(4),
            ])
            ->action(function (array $data, TreeSurveyPoint $record) use ($status): void {
                $review = app(ReviewSurveyPoint::class);

                $status === SurveyReviewStatus::Rejected
                    ? $review->reject($record, $data['review_notes'], auth()->user())
                    : $review->needsCorrection($record, $data['review_notes'], auth()->user());

                Notification::make()->success()->title('Survey review updated')->send();
            });
    }

    /** @return array<int, mixed> */
    private static function approvalSchema(): array
    {
        return [
            Select::make('campus_area_id')
                ->label('Campus area')
                ->options(fn (): array => self::campusAreaOptions())
                ->searchable()->preload()->required(),
            TextInput::make('approved_species_name')
                ->label('Approved species name')
                ->helperText('Enter one cleaned inventory name. Combined labels must be resolved record by record.')
                ->required()->maxLength(255),
            Textarea::make('review_notes')->label('Reviewer notes')->rows(3),
        ];
    }

    private static function reviewBulkAction(string $name, string $label, SurveyReviewStatus $status, string $color, Heroicon $icon): BulkAction
    {
        return BulkAction::make($name)
            ->label($label)
            ->icon($icon)
            ->color($color)
            ->authorizeIndividualRecords('update')
            ->schema([
                Textarea::make('review_notes')
                    ->label($status === SurveyReviewStatus::Rejected ? 'Rejection reason' : 'Correction notes')
                    ->required()->rows(4),
            ])
            ->action(function (array $data, Collection $records) use ($status): void {
                $review = app(ReviewSurveyPoint::class);

                $records->each(fn (TreeSurveyPoint $record) => $status === SurveyReviewStatus::Rejected
                    ? $review->reject($record, $data['review_notes'], auth()->user())
                    : $review->needsCorrection($record, $data['review_notes'], auth()->user()));
            })
            ->successNotificationTitle('Survey reviews updated');
    }

    /** @return array<int|string, string> */
    private static function campusAreaOptions(): array
    {
        return CampusArea::query()->where('is_active', true)->orderBy('name')->pluck('name', 'id')->all();
    }
}
