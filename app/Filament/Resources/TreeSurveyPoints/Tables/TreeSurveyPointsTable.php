<?php

namespace App\Filament\Resources\TreeSurveyPoints\Tables;

use App\Enums\SurveyReviewStatus;
use App\Filament\Resources\Trees\TreeResource;
use App\Filament\Resources\TreeSurveyPoints\Actions\SurveyPointActions;
use App\Models\TreeSurveyPoint;
use App\Support\Survey\SurveyReviewReadiness;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TreeSurveyPointsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('source_row')
            ->modifyQueryUsing(fn (Builder $query): Builder => $query
                ->with(['campusArea:id,name', 'registeredTree:id,tree_code'])
                ->addSelect([
                    'duplicate_coordinate_count' => TreeSurveyPoint::query()
                        ->from('tree_survey_points as duplicate_points')
                        ->selectRaw('count(*)')
                        ->whereColumn('duplicate_points.latitude', 'tree_survey_points.latitude')
                        ->whereColumn('duplicate_points.longitude', 'tree_survey_points.longitude')
                        ->whereColumn('duplicate_points.id', '<>', 'tree_survey_points.id'),
                ]))
            ->columns([
                TextColumn::make('source_row')
                    ->label('Survey ID')
                    ->formatStateUsing(fn (int $state): string => 'S-'.str_pad((string) $state, 4, '0', STR_PAD_LEFT))
                    ->searchable()->sortable()->copyable()->weight('semibold'),
                TextColumn::make('raw_species_name')->label('Recorded species')->placeholder('Unidentified tree')->searchable()->sortable(),
                TextColumn::make('approved_species_name')->label('Approved species')->placeholder('Needs resolution')->searchable()->toggleable(),
                TextColumn::make('campusArea.name')->label('Campus area')->placeholder('Unassigned')->sortable()->toggleable(),
                TextColumn::make('height_m')->label('Height')->suffix(' m')->placeholder('Not measured')->sortable(),
                TextColumn::make('coordinate_status')->label('Coordinates')->badge()->color(fn (string $state): string => match ($state) {
                    'valid' => 'success',
                    'outlier' => 'warning',
                    default => 'danger',
                })->sortable(),
                TextColumn::make('review_status')->label('Review')->badge()->color(fn (SurveyReviewStatus $state): string => match ($state) {
                    SurveyReviewStatus::Approved, SurveyReviewStatus::Converted => 'success',
                    SurveyReviewStatus::Pending => 'warning',
                    SurveyReviewStatus::NeedsCorrection => 'info',
                    SurveyReviewStatus::Rejected => 'danger',
                })->sortable(),
                TextColumn::make('review_readiness')
                    ->label('Readiness')
                    ->state(fn (TreeSurveyPoint $record): string => app(SurveyReviewReadiness::class)->isReady($record, false)
                        && (int) $record->duplicate_coordinate_count === 0 ? 'Ready' : 'Blocked')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'Ready' ? 'success' : 'warning')
                    ->tooltip(function (TreeSurveyPoint $record): ?string {
                        $issues = app(SurveyReviewReadiness::class)->issues($record, false);

                        if ((int) $record->duplicate_coordinate_count > 0) {
                            $issues[] = 'Possible duplicate coordinates detected.';
                        }

                        return $issues === [] ? null : implode(' ', $issues);
                    }),
                TextColumn::make('registeredTree.tree_code')->label('Registered tree')->badge()->placeholder('Not registered')
                    ->url(fn ($record): ?string => $record->registeredTree
                        ? TreeResource::getUrl('view', ['record' => $record->registeredTree])
                        : null),
                TextColumn::make('latitude')->label('Latitude')->copyable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('longitude')->label('Longitude')->copyable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('reviewed_at')->label('Reviewed')->since()->dateTimeTooltip()->placeholder('Not reviewed')->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('review_status')->label('Review status')->options(SurveyReviewStatus::class)->native(false),
                SelectFilter::make('coordinate_status')->label('Coordinate status')->options([
                    'valid' => 'Valid',
                    'outlier' => 'Outlier',
                    'invalid' => 'Invalid',
                ])->native(false),
                SelectFilter::make('campus_area_id')->label('Campus area')->relationship('campusArea', 'name')->searchable()->preload()->native(false),
            ])
            ->recordActions([
                SurveyPointActions::approve(),
                SurveyPointActions::registerTree(),
                ViewAction::make(),
                EditAction::make()->visible(fn ($record): bool => $record->registered_tree_id === null),
                ActionGroup::make([
                    SurveyPointActions::needsCorrection(),
                    SurveyPointActions::reject(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    SurveyPointActions::assignCampusAreaBulk(),
                    SurveyPointActions::approveReadyBulk(),
                    SurveyPointActions::needsCorrectionBulk(),
                    SurveyPointActions::rejectBulk(),
                ]),
            ]);
    }
}
