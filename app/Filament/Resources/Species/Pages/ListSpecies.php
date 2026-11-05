<?php

namespace App\Filament\Resources\Species\Pages;

use App\Enums\PermissionName;
use App\Exports\SpeciesExport;
use App\Filament\Resources\Species\SpeciesResource;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Maatwebsite\Excel\Facades\Excel;

class ListSpecies extends ListRecords
{
    protected static string $resource = SpeciesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('exportExcel')
                ->label('Export Excel')
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->color('gray')
                ->visible(fn (): bool => $this->canViewReports())
                ->action(function () {
                    abort_unless($this->canViewReports(), 403);

                    activity('reports')->causedBy(auth()->user())->withProperties(['report' => 'species', 'format' => 'xlsx'])->log('Exported the filtered species directory.');

                    return Excel::download(
                        new SpeciesExport($this->getTableQueryForExport()),
                        'species-directory-'.now()->format('Y-m-d-His').'.xlsx',
                    );
                }),
            Action::make('downloadPdf')
                ->label('PDF Report')
                ->icon(Heroicon::OutlinedDocumentArrowDown)
                ->color('gray')
                ->visible(fn (): bool => $this->canViewReports())
                ->action(function () {
                    abort_unless($this->canViewReports(), 403);

                    $species = $this->getTableQueryForExport()->withCount('trees')->get();

                    activity('reports')->causedBy(auth()->user())->withProperties(['report' => 'species', 'format' => 'pdf', 'record_count' => $species->count()])->log('Downloaded the filtered species report.');

                    return Pdf::loadView('reports.species', [
                        'species' => $species,
                        'generatedAt' => now(),
                    ])->setPaper('a4', 'landscape')->download('species-directory-'.now()->format('Y-m-d-His').'.pdf');
                }),
        ];
    }

    private function canViewReports(): bool
    {
        return auth()->user()?->can(PermissionName::ViewReports->value) ?? false;
    }
}
