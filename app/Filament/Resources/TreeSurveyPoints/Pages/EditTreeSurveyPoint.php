<?php

namespace App\Filament\Resources\TreeSurveyPoints\Pages;

use App\Filament\Resources\TreeSurveyPoints\Actions\SurveyPointActions;
use App\Filament\Resources\TreeSurveyPoints\TreeSurveyPointResource;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditTreeSurveyPoint extends EditRecord
{
    protected static string $resource = TreeSurveyPointResource::class;

    protected function getHeaderActions(): array
    {
        return [
            SurveyPointActions::approve(),
            SurveyPointActions::registerTree(),
            ViewAction::make(),
            ActionGroup::make([
                SurveyPointActions::needsCorrection(),
                SurveyPointActions::reject(),
            ]),
        ];
    }
}
