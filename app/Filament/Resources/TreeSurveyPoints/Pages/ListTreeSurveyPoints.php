<?php

namespace App\Filament\Resources\TreeSurveyPoints\Pages;

use App\Enums\PermissionName;
use App\Enums\SurveyReviewStatus;
use App\Exports\TreeSurveyPointsExport;
use App\Filament\Resources\TreeSurveyPoints\Actions\ImportTreeSurveyAction;
use App\Filament\Resources\TreeSurveyPoints\TreeSurveyPointResource;
use App\Models\TreeSurveyPoint;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Icons\Heroicon;
use Maatwebsite\Excel\Facades\Excel;

class ListTreeSurveyPoints extends ListRecords
{
    protected static string $resource = TreeSurveyPointResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All')->badge(fn (): int => TreeSurveyPoint::query()->count()),
            'pending' => $this->statusTab('Pending', SurveyReviewStatus::Pending, 'warning'),
            'approved' => $this->statusTab('Approved', SurveyReviewStatus::Approved, 'success'),
            'needs-correction' => $this->statusTab('Needs correction', SurveyReviewStatus::NeedsCorrection, 'info'),
            'rejected' => $this->statusTab('Rejected', SurveyReviewStatus::Rejected, 'danger'),
            'converted' => $this->statusTab('Converted', SurveyReviewStatus::Converted, 'success'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            ImportTreeSurveyAction::make(),
            Action::make('exportExcel')
                ->label('Export Excel')
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->color('gray')
                ->visible(fn (): bool => $this->canViewReports())
                ->action(function () {
                    abort_unless($this->canViewReports(), 403);

                    activity('reports')->causedBy(auth()->user())->withProperties(['report' => 'survey-records', 'format' => 'xlsx'])->log('Exported the filtered survey records.');

                    return Excel::download(
                        new TreeSurveyPointsExport($this->getTableQueryForExport()),
                        'survey-records-'.now()->format('Y-m-d-His').'.xlsx',
                    );
                }),
            Action::make('downloadPdf')
                ->label('PDF Report')
                ->icon(Heroicon::OutlinedDocumentArrowDown)
                ->color('gray')
                ->visible(fn (): bool => $this->canViewReports())
                ->action(function () {
                    abort_unless($this->canViewReports(), 403);

                    $surveyPoints = $this->getTableQueryForExport()
                        ->with(['registeredTree:id,tree_code', 'reviewer:id,name'])
                        ->get();

                    activity('reports')->causedBy(auth()->user())->withProperties(['report' => 'survey-records', 'format' => 'pdf', 'record_count' => $surveyPoints->count()])->log('Downloaded the filtered survey records report.');

                    return Pdf::loadView('reports.survey-records', [
                        'surveyPoints' => $surveyPoints,
                        'generatedAt' => now(),
                    ])->setPaper('a4', 'landscape')->download('survey-records-'.now()->format('Y-m-d-His').'.pdf');
                }),
        ];
    }

    private function canViewReports(): bool
    {
        return auth()->user()?->can(PermissionName::ViewReports->value) ?? false;
    }

    private function statusTab(string $label, SurveyReviewStatus $status, string $color): Tab
    {
        return Tab::make($label)
            ->badge(fn (): int => TreeSurveyPoint::query()->where('review_status', $status)->count())
            ->badgeColor($color)
            ->modifyQueryUsing(fn ($query) => $query->where('review_status', $status));
    }
}
