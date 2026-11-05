<?php

namespace App\Filament\Resources\Trees\Pages;

use App\Enums\PermissionName;
use App\Exports\TreesExport;
use App\Filament\Resources\TreeSurveyPoints\Actions\ImportTreeSurveyAction;
use App\Filament\Resources\Trees\TreeResource;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Maatwebsite\Excel\Facades\Excel;

class ListTrees extends ListRecords
{
    protected static string $resource = TreeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            ImportTreeSurveyAction::make(),
            Action::make('exportExcel')
                ->label('Export Excel')
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->color('gray')
                ->visible(fn (): bool => $this->canViewReports())
                ->action(function () {
                    abort_unless($this->canViewReports(), 403);

                    activity('reports')->causedBy(auth()->user())->withProperties(['report' => 'trees', 'format' => 'xlsx'])->log('Exported the filtered tree inventory.');

                    return Excel::download(
                        new TreesExport($this->getTableQueryForExport()),
                        'tree-inventory-'.now()->format('Y-m-d-His').'.xlsx',
                    );
                }),
            Action::make('downloadPdf')
                ->label('PDF Report')
                ->icon(Heroicon::OutlinedDocumentArrowDown)
                ->color('gray')
                ->visible(fn (): bool => $this->canViewReports())
                ->action(function () {
                    abort_unless($this->canViewReports(), 403);

                    $trees = $this->getTableQueryForExport()
                        ->with(['species:id,common_name,scientific_name', 'campusArea:id,name'])
                        ->get();

                    activity('reports')->causedBy(auth()->user())->withProperties(['report' => 'trees', 'format' => 'pdf', 'record_count' => $trees->count()])->log('Downloaded the filtered tree inventory report.');

                    return Pdf::loadView('reports.trees', [
                        'trees' => $trees,
                        'generatedAt' => now(),
                    ])->setPaper('a4', 'landscape')->download('tree-inventory-'.now()->format('Y-m-d-His').'.pdf');
                }),
        ];
    }

    private function canViewReports(): bool
    {
        return auth()->user()?->can(PermissionName::ViewReports->value) ?? false;
    }

}
