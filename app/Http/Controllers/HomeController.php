<?php

namespace App\Http\Controllers;

use App\Models\CampusArea;
use App\Models\Species;
use App\Models\Tree;
use App\Models\TreeSurveyPoint;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $stats = $this->publicStats();

        try {
            $featuredTrees = Tree::query()
                ->published()
                ->with(['species', 'campusArea', 'primaryPhoto'])
                ->orderByDesc('is_featured')
                ->orderByDesc('published_at')
                ->limit(3)
                ->get()
                ->map(fn (Tree $tree): object => (object) [
                    'code' => $tree->tree_code,
                    'name' => $tree->species->common_name,
                    'scientific_name' => $tree->species->scientific_name,
                    'location' => $tree->descriptive_location ?: $tree->campusArea->name,
                    'condition' => $tree->current_condition->value,
                    'photo_url' => $tree->primaryPhoto
                        ? Storage::disk($tree->primaryPhoto->disk)->url($tree->primaryPhoto->path)
                        : null,
                    'profile_url' => route('trees.show', $tree->tree_code),
                ]);
        } catch (QueryException) {
            $featuredTrees = collect();
        }

        return view('home', [
            'stats' => $stats,
            'featuredTrees' => $featuredTrees,
        ]);
    }

    public function about(): View
    {
        return view('about', ['stats' => $this->publicStats()]);
    }

    /** @return array{mapped: int, species: int, areas: int, verified: int, survey: int} */
    private function publicStats(): array
    {
        try {
            $verifiedTrees = Tree::query()->published()->count();
            $surveyQuery = TreeSurveyPoint::query()
                ->publiclyReviewable()
                ->whereNotNull('latitude')
                ->whereNotNull('longitude');
            $surveyPoints = (clone $surveyQuery)->count();
            $verifiedSpecies = Species::query()
                ->whereHas('trees', fn ($query) => $query->published())
                ->pluck('common_name');
            $surveySpecies = (clone $surveyQuery)
                ->whereNotNull('raw_species_name')
                ->where('raw_species_name', '!=', '')
                ->distinct()
                ->pluck('raw_species_name');
            $recordedLabels = $verifiedSpecies
                ->merge($surveySpecies)
                ->map(fn (string $name): string => mb_strtolower(trim($name)))
                ->filter()
                ->unique()
                ->count();
            $campusAreas = CampusArea::query()
                ->where('is_active', true)
                ->whereHas('trees', fn ($query) => $query->published())
                ->count();

            return [
                'mapped' => $verifiedTrees + $surveyPoints,
                'species' => $recordedLabels,
                'areas' => $campusAreas,
                'verified' => $verifiedTrees,
                'survey' => $surveyPoints,
            ];
        } catch (QueryException) {
            return ['mapped' => 0, 'species' => 0, 'areas' => 0, 'verified' => 0, 'survey' => 0];
        }
    }
}
