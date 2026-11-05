<?php

namespace App\Support\Survey;

use App\Models\TreeSurveyPoint;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class SurveySpeciesNormalization
{
    /**
     * @param  Collection<int, TreeSurveyPoint>  $surveyPoints
     * @return array{summary: array<string, int|string>, groups: Collection<int, array<string, mixed>>, labels: Collection<int, array<string, mixed>>}
     */
    public function build(Collection $surveyPoints): array
    {
        $labelCounts = $surveyPoints
            ->pluck('raw_species_name')
            ->filter(fn (mixed $label): bool => is_string($label) && filled(trim($label)))
            ->countBy(fn (string $label): string => Str::squish($label));

        $groups = $labelCounts
            ->groupBy(
                fn (int $records, string $label): string => SurveyAuditRules::normalizedSpeciesKey($label) ?? '',
                preserveKeys: true,
            )
            ->reject(fn (Collection $variants, string $key): bool => $key === '')
            ->map(fn (Collection $variants, string $key): array => $this->buildGroup($key, $variants))
            ->values();

        $groups = $groups
            ->map(fn (array $group): array => $this->addSpellingSuggestion($group, $groups))
            ->sortByDesc('records')
            ->values();

        $groupsByKey = $groups->keyBy('normalized_key');
        $labels = $labelCounts
            ->map(function (int $records, string $label) use ($groupsByKey): array {
                $normalizedKey = SurveyAuditRules::normalizedSpeciesKey($label);
                $group = $groupsByKey->get($normalizedKey);
                $hasNumericIdentifier = $this->stripNumericIdentifier($label) !== Str::squish($label);

                return [
                    'original_label' => $label,
                    'records' => $records,
                    'normalized_key' => $normalizedKey,
                    'proposed_label' => $group['proposed_label'] ?? null,
                    'has_numeric_identifier' => $hasNumericIdentifier,
                    'classification' => $group['classification'] ?? 'manual_review',
                    'suggested_existing_group' => $group['suggested_existing_group'] ?? null,
                    'similarity_percent' => $group['similarity_percent'] ?? null,
                    'safe_to_apply_automatically' => ($group['classification'] ?? null) === 'mechanical_cleanup'
                        && ! str_contains($label, '/'),
                ];
            })
            ->sortByDesc('records')
            ->values();

        return [
            'summary' => [
                'Generated at' => now()->toIso8601String(),
                'Survey records reviewed' => $surveyPoints->count(),
                'Exact recorded labels' => $labelCounts->count(),
                'Mechanical normalized groups' => $groups->count(),
                'Groups with numbered, spacing, or casing variants' => $groups->where('classification', 'mechanical_cleanup')->count(),
                'Possible spelling-variant groups' => $groups->where('classification', 'possible_spelling_variant')->count(),
                'Combined labels requiring record-by-record review' => $groups->where('classification', 'combined_species_label')->count(),
                'Records eligible for mechanical label cleanup' => $labels->where('safe_to_apply_automatically', true)->sum('records'),
                'Database records changed' => 'No — reviewer workbook only',
            ],
            'groups' => $groups,
            'labels' => $labels,
        ];
    }

    /**
     * @param  Collection<string, int>  $variants
     * @return array<string, mixed>
     */
    private function buildGroup(string $key, Collection $variants): array
    {
        $rankedVariants = $variants
            ->map(fn (int $records, string $label): array => [
                'label' => $label,
                'records' => $records,
                'base_label' => $this->stripNumericIdentifier($label),
            ])
            ->sort(function (array $left, array $right): int {
                $leftHasIdentifier = $left['label'] !== $left['base_label'];
                $rightHasIdentifier = $right['label'] !== $right['base_label'];

                return [$leftHasIdentifier, -$left['records'], mb_strlen($left['label']), $left['label']]
                    <=> [$rightHasIdentifier, -$right['records'], mb_strlen($right['label']), $right['label']];
            })
            ->values();

        $containsCombinedLabel = $rankedVariants->contains(fn (array $variant): bool => str_contains($variant['label'], '/'));
        $proposedLabel = $containsCombinedLabel
            ? $rankedVariants->first()['label']
            : $rankedVariants->first()['base_label'];
        $needsMechanicalCleanup = $rankedVariants->count() > 1
            || $rankedVariants->contains(fn (array $variant): bool => $variant['label'] !== $proposedLabel);

        return [
            'normalized_key' => $key,
            'proposed_label' => $proposedLabel,
            'records' => $rankedVariants->sum('records'),
            'exact_label_count' => $rankedVariants->count(),
            'variants' => $rankedVariants->map(fn (array $variant): string => $variant['label'].' ('.$variant['records'].')')->all(),
            'classification' => match (true) {
                $containsCombinedLabel => 'combined_species_label',
                $needsMechanicalCleanup => 'mechanical_cleanup',
                default => 'consistent_label',
            },
            'suggested_existing_group' => null,
            'similarity_percent' => null,
            'reviewer_decision' => '',
            'approved_label' => '',
            'reviewer_notes' => '',
        ];
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $groups
     * @return array<string, mixed>
     */
    private function addSpellingSuggestion(array $group, Collection $groups): array
    {
        if ($group['classification'] === 'combined_species_label') {
            return $group;
        }

        $currentKey = $group['normalized_key'];
        $maximumDistance = max(1, (int) floor(mb_strlen($currentKey) * 0.2));
        $suggestion = $groups
            ->filter(fn (array $candidate): bool => $candidate['normalized_key'] !== $currentKey
                && $candidate['classification'] !== 'combined_species_label'
                && ($candidate['records'] > $group['records']
                    || ($candidate['records'] === $group['records'] && $candidate['normalized_key'] < $currentKey)))
            ->map(function (array $candidate) use ($currentKey): array {
                $distance = levenshtein($currentKey, $candidate['normalized_key']);
                $longestLength = max(mb_strlen($currentKey), mb_strlen($candidate['normalized_key']));

                return [
                    'label' => $candidate['proposed_label'],
                    'records' => $candidate['records'],
                    'distance' => $distance,
                    'similarity_percent' => round((1 - $distance / $longestLength) * 100, 1),
                ];
            })
            ->filter(fn (array $candidate): bool => $candidate['distance'] <= $maximumDistance && $candidate['similarity_percent'] >= 80)
            ->sortBy(fn (array $candidate): array => [$candidate['distance'], -$candidate['records'], $candidate['label']])
            ->first();

        if ($suggestion === null) {
            return $group;
        }

        $group['classification'] = 'possible_spelling_variant';
        $group['suggested_existing_group'] = $suggestion['label'];
        $group['similarity_percent'] = $suggestion['similarity_percent'];

        return $group;
    }

    private function stripNumericIdentifier(string $label): string
    {
        return Str::of($label)
            ->squish()
            ->replaceMatches('/\s*\d+\s*$/u', '')
            ->trim()
            ->toString();
    }
}
