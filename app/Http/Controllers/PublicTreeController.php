<?php

namespace App\Http\Controllers;

use App\Enums\ConservationStatus;
use App\Enums\TreeCondition;
use App\Enums\VerificationStatus;
use App\Models\CampusArea;
use App\Models\Tree;
use App\Models\TreeSurveyPoint;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PublicTreeController extends Controller
{
    public function index(Request $request): View
    {
        $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'area' => ['nullable', 'integer', 'exists:campus_areas,id'],
            'condition' => ['nullable', 'string'],
            'conservation' => ['nullable', 'string'],
            'record_type' => ['nullable', 'in:all,verified,survey'],
            'sort' => ['nullable', 'in:recent,name,code'],
            'per_page' => ['nullable', 'integer', 'in:8,12,24,48'],
        ]);

        $query = Tree::query()
            ->published()
            ->with(['species', 'campusArea', 'primaryPhoto']);

        if ($search = trim((string) $request->query('q'))) {
            $query->where(function (Builder $query) use ($search): void {
                $query
                    ->where('tree_code', 'like', "%{$search}%")
                    ->orWhere('descriptive_location', 'like', "%{$search}%")
                    ->orWhere('nearby_landmark', 'like', "%{$search}%")
                    ->orWhereHas('species', function (Builder $query) use ($search): void {
                        $query->where('common_name', 'like', "%{$search}%")
                            ->orWhere('local_name', 'like', "%{$search}%")
                            ->orWhere('scientific_name', 'like', "%{$search}%")
                            ->orWhere('family_name', 'like', "%{$search}%");
                    });
            });
        }

        $query
            ->when($request->filled('area'), fn (Builder $query) => $query->where('campus_area_id', $request->integer('area')))
            ->when($request->filled('condition'), fn (Builder $query) => $query->where('current_condition', $request->query('condition')))
            ->when($request->filled('conservation'), fn (Builder $query) => $query->whereHas('species', fn (Builder $query) => $query->where('conservation_status', $request->query('conservation'))));

        $recordType = $request->query('record_type', 'all');
        $records = collect();

        if ($recordType !== 'survey') {
            $records = $query->get()->map(function (Tree $tree): object {
                $photo = $tree->primaryPhoto;
                $location = $tree->descriptive_location ?: $tree->campusArea->name;
                $locationLabel = strcasecmp($location, $tree->campusArea->name) === 0
                    ? $location
                    : $location.' · '.$tree->campusArea->name;

                return (object) [
                    'record_type' => 'verified',
                    'code' => $tree->tree_code,
                    'name' => $tree->species->common_name,
                    'scientific_name' => $tree->species->scientific_name,
                    'family' => $tree->species->family_name,
                    'location' => $tree->descriptive_location,
                    'area' => $tree->campusArea->name,
                    'location_label' => $locationLabel,
                    'condition' => $tree->current_condition->value,
                    'height_m' => $tree->estimated_height_m !== null ? (float) $tree->estimated_height_m : null,
                    'photo_url' => $photo ? Storage::disk($photo->disk)->url($photo->path) : null,
                    'action_url' => route('trees.show', $tree->tree_code),
                    'sort_timestamp' => $tree->published_at?->getTimestamp() ?? $tree->created_at->getTimestamp(),
                    'is_verified' => true,
                ];
            });
        }

        $metadataFilterActive = $request->filled('area') || $request->filled('condition') || $request->filled('conservation');

        if ($recordType !== 'verified' && ! $metadataFilterActive) {
            $surveyQuery = TreeSurveyPoint::query()
                ->publiclyReviewable()
                ->whereNotNull('latitude')
                ->whereNotNull('longitude');

            if ($search = trim((string) $request->query('q'))) {
                $surveyQuery->where(function (Builder $query) use ($search): void {
                    $query->where('raw_species_name', 'like', "%{$search}%")
                        ->orWhere('source_row', $search)
                        ->orWhere('easting', 'like', "%{$search}%")
                        ->orWhere('northing', 'like', "%{$search}%");
                });
            }

            $surveyRecords = $surveyQuery->get()->map(fn (TreeSurveyPoint $point): object => (object) [
                'record_type' => 'survey',
                'code' => 'S-'.str_pad((string) $point->source_row, 4, '0', STR_PAD_LEFT),
                'name' => $point->raw_species_name ?: 'Unidentified tree',
                'scientific_name' => 'Identification pending',
                'family' => 'Not yet recorded',
                'location' => 'E '.$point->easting.' · N '.$point->northing,
                'area' => 'NVSU Bayombong – Unassigned',
                'location_label' => 'E '.$point->easting.' · N '.$point->northing.' · NVSU Bayombong – Unassigned',
                'condition' => 'unknown',
                'height_m' => $point->height_m !== null ? (float) $point->height_m : null,
                'photo_url' => null,
                'action_url' => route('campus-map', ['q' => $point->raw_species_name ?: null]),
                'sort_timestamp' => $point->created_at->getTimestamp(),
                'is_verified' => false,
            ]);

            $records = $records->concat($surveyRecords);
        }

        $records = (match ($request->query('sort', 'recent')) {
            'name' => $records->sortBy(fn (object $record) => mb_strtolower($record->name), SORT_NATURAL),
            'code' => $records->sortBy('code', SORT_NATURAL),
            default => $records->sortBy([
                ['is_verified', 'desc'],
                ['sort_timestamp', 'desc'],
            ]),
        })->values();

        $page = LengthAwarePaginator::resolveCurrentPage();
        $perPage = $request->integer('per_page', 8);
        $trees = new LengthAwarePaginator(
            $records->forPage($page, $perPage)->values(),
            $records->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()],
        );

        $activeFilterCount = collect([
            $request->filled('q'),
            $request->filled('area'),
            $request->query('record_type', 'all') !== 'all',
            $request->filled('condition'),
            $request->filled('conservation'),
            $request->query('sort', 'recent') !== 'recent',
        ])->filter()->count();

        return view('trees.index', [
            'trees' => $trees,
            'campusAreas' => CampusArea::query()->where('is_active', true)->orderBy('name')->get(),
            'conditions' => TreeCondition::cases(),
            'conservationStatuses' => ConservationStatus::cases(),
            'activeFilterCount' => $activeFilterCount,
        ]);
    }

    public function show(string $treeCode): View
    {
        $tree = Tree::query()
            ->published()
            ->with([
                'species', 'campusArea', 'primaryPhoto', 'photos', 'verifier',
                'monitorings' => fn ($query) => $query
                    ->where('verification_status', VerificationStatus::Verified)
                    ->where('is_public', true)
                    ->latest('observed_at'),
            ])
            ->where('tree_code', $treeCode)
            ->firstOrFail();

        return view('trees.show', compact('tree'));
    }
}
