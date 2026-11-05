<?php

namespace App\Filament\Resources\TreeSurveyPoints\Pages;

use App\Filament\Resources\TreeSurveyPoints\Actions\SurveyPointActions;
use App\Filament\Resources\TreeSurveyPoints\TreeSurveyPointResource;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTreeSurveyPoint extends ViewRecord
{
    protected static string $resource = TreeSurveyPointResource::class;

    protected function getHeaderActions(): array
    {
        return [
            SurveyPointActions::approve(),
            SurveyPointActions::registerTree(),
            EditAction::make()->visible(fn (): bool => $this->record->registered_tree_id === null),
            ActionGroup::make([
                SurveyPointActions::needsCorrection(),
                SurveyPointActions::reject(),
            ]),
        ];
    }
}
