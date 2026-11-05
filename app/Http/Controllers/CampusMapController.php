<?php

namespace App\Http\Controllers;

use App\Enums\TreeCondition;
use App\Models\CampusArea;
use App\Models\Tree;
use App\Models\TreeSurveyPoint;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CampusMapController extends Controller
{
    public function index(): View
    {
        return view('campus-map', [
            'campusAreas' => CampusArea::query()->where('is_active', true)->orderBy('name')->get(),
            'conditions' => TreeCondition::cases(),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'area' => ['nullable', 'integer', 'exists:campus_areas,id'],
            'condition' => ['nullable', 'string'],
            'survey' => ['nullable', 'boolean'],
        ]);

        $query = Tree::query()
            ->published()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->with(['species', 'campusArea', 'primaryPhoto']);

        if ($search = trim((string) $request->query('q'))) {
            $query->where(function (Builder $query) use ($search): void {
                $query->where('tree_code', 'like', "%{$search}%")
                    ->orWhere('descriptive_location', 'like', "%{$search}%")
                    ->orWhereHas('species', fn (Builder $query) => $query
                        ->where('common_name', 'like', "%{$search}%")
                        ->orWhere('scientific_name', 'like', "%{$search}%"));
            });
        }

        $query
            ->when($request->filled('area'), fn (Builder $query) => $query->where('campus_area_id', $request->integer('area')))
            ->when($request->filled('condition'), fn (Builder $query) => $query->where('current_condition', $request->query('condition')));

        $trees = $query->orderBy('tree_code')->get();

        $surveyQuery = TreeSurveyPoint::query()
            ->publiclyReviewable()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude');

        if ($search = trim((string) $request->query('q'))) {
            $surveyQuery->where('raw_species_name', 'like', "%{$search}%");
        }

        if ($request->filled('area') || $request->filled('condition') || ! $request->boolean('survey', true)) {
            $surveyQuery->whereRaw('1 = 0');
        }

        $surveyPoints = $surveyQuery->orderBy('source_row')->get();

        return response()->json([
            'center' => ['latitude' => 16.480696, 'longitude' => 121.140771, 'zoom' => 18],
            'trees' => $trees->map(fn (Tree $tree): array => [
                'code' => $tree->tree_code,
                'name' => $tree->species->common_name,
                'scientific_name' => $tree->species->scientific_name,
                'family' => $tree->species->family_name,
                'area' => $tree->campusArea->name,
                'location' => $tree->descriptive_location,
                'condition' => $tree->current_condition->value,
                'latitude' => (float) $tree->latitude,
                'longitude' => (float) $tree->longitude,
                'profile_url' => route('trees.show', $tree->tree_code),
                'photo_url' => $tree->primaryPhoto ? Storage::disk($tree->primaryPhoto->disk)->url($tree->primaryPhoto->path) : null,
                'record_type' => 'verified',
            ])->values(),
            'survey_points' => $surveyPoints->map(fn (TreeSurveyPoint $point): array => [
                'code' => 'Survey row '.$point->source_row,
                'name' => $point->raw_species_name ?: 'Unidentified tree',
                'scientific_name' => 'Identification pending',
                'area' => 'NVSU Bayombong – Unassigned',
                'location' => 'E '.$point->easting.' · N '.$point->northing,
                'condition' => 'unknown',
                'height_m' => $point->height_m !== null ? (float) $point->height_m : null,
                'latitude' => (float) $point->latitude,
                'longitude' => (float) $point->longitude,
                'coordinate_status' => $point->coordinate_status,
                'profile_url' => null,
                'photo_url' => null,
                'record_type' => 'survey',
            ])->values(),
            'summary' => [
                'verified_records' => $trees->count(),
                'survey_points' => $surveyPoints->count(),
                'mapped_points' => $trees->count() + $surveyPoints->count(),
                'invalid_rows' => TreeSurveyPoint::query()->where('coordinate_status', 'invalid')->count(),
            ],
            'updated_at' => collect([$trees->max('updated_at'), $surveyPoints->max('updated_at')])->filter()->max()?->toIso8601String(),
        ]);
    }
}
