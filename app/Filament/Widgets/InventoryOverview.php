<?php

namespace App\Filament\Widgets;

use App\Enums\SurveyReviewStatus;
use App\Enums\TreeCondition;
use App\Enums\VerificationStatus;
use App\Models\Species;
use App\Models\Tree;
use App\Models\TreeSurveyPoint;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class InventoryOverview extends StatsOverviewWidget
{
    protected ?string $heading = 'Inventory and review overview';

    protected ?string $description = 'Current inventory health and the work remaining in the survey review queue.';

    protected function getStats(): array
    {
        $needsAttention = Tree::whereIn('current_condition', [
            TreeCondition::Poor,
            TreeCondition::Damaged,
            TreeCondition::Diseased,
            TreeCondition::Dead,
        ])->count();
        $reviewQueue = TreeSurveyPoint::query()
            ->whereNull('registered_tree_id')
            ->whereIn('review_status', [
                SurveyReviewStatus::Pending,
                SurveyReviewStatus::NeedsCorrection,
            ]);
        $awaitingReview = (clone $reviewQueue)->count();
        $coordinateIssues = (clone $reviewQueue)->whereIn('coordinate_status', ['invalid', 'outlier'])->count();
        $unassignedAreas = (clone $reviewQueue)->whereNull('campus_area_id')->count();
        $unresolvedSpecies = (clone $reviewQueue)
            ->where(fn ($query) => $query
                ->whereNull('approved_species_name')
                ->orWhere('approved_species_name', ''))
            ->count();

        return [
            Stat::make('Registered trees', Tree::count())
                ->description('All active and archived records')
                ->descriptionIcon('heroicon-m-rectangle-stack')
                ->color('success'),
            Stat::make('Verified records', Tree::where('verification_status', VerificationStatus::Verified)->count())
                ->description('Approved for authorized use')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),
            Stat::make('Species', Species::where('is_active', true)->count())
                ->description('Active species references')
                ->descriptionIcon('heroicon-m-tag')
                ->color('info'),
            Stat::make('Needs attention', $needsAttention)
                ->description('Poor, damaged, diseased, or dead')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($needsAttention > 0 ? 'warning' : 'success'),
            Stat::make('Awaiting survey review', $awaitingReview)
                ->description('Pending or returned for correction')
                ->descriptionIcon('heroicon-m-clipboard-document-check')
                ->color($awaitingReview > 0 ? 'warning' : 'success'),
            Stat::make('Coordinate issues', $coordinateIssues)
                ->description('Invalid or outside the campus radius')
                ->descriptionIcon('heroicon-m-map-pin')
                ->color($coordinateIssues > 0 ? 'danger' : 'success'),
            Stat::make('Unassigned campus areas', $unassignedAreas)
                ->description('Survey records blocked from approval')
                ->descriptionIcon('heroicon-m-map')
                ->color($unassignedAreas > 0 ? 'warning' : 'success'),
            Stat::make('Unresolved species', $unresolvedSpecies)
                ->description('Approved inventory name still required')
                ->descriptionIcon('heroicon-m-tag')
                ->color($unresolvedSpecies > 0 ? 'warning' : 'success'),
        ];
    }
}
