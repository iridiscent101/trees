<?php

namespace App\Filament\Resources\TreeSurveyPoints;

use App\Enums\SurveyReviewStatus;
use App\Filament\Resources\TreeSurveyPoints\Pages\EditTreeSurveyPoint;
use App\Filament\Resources\TreeSurveyPoints\Pages\ListTreeSurveyPoints;
use App\Filament\Resources\TreeSurveyPoints\Pages\ViewTreeSurveyPoint;
use App\Filament\Resources\TreeSurveyPoints\Schemas\TreeSurveyPointForm;
use App\Filament\Resources\TreeSurveyPoints\Schemas\TreeSurveyPointInfolist;
use App\Filament\Resources\TreeSurveyPoints\Tables\TreeSurveyPointsTable;
use App\Models\TreeSurveyPoint;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TreeSurveyPointResource extends Resource
{
    protected static ?string $model = TreeSurveyPoint::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $navigationLabel = 'Survey Records';

    protected static ?string $modelLabel = 'survey record';

    protected static ?string $recordTitleAttribute = 'raw_species_name';

    public static function form(Schema $schema): Schema
    {
        return TreeSurveyPointForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TreeSurveyPointInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TreeSurveyPointsTable::configure($table);
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::query()
            ->whereIn('review_status', [SurveyReviewStatus::Pending, SurveyReviewStatus::NeedsCorrection])
            ->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTreeSurveyPoints::route('/'),
            'view' => ViewTreeSurveyPoint::route('/{record}'),
            'edit' => EditTreeSurveyPoint::route('/{record}/edit'),
        ];
    }
}
