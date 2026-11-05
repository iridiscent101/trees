<?php

namespace App\Support\Survey;

use App\Models\TreeSurveyPoint;
use DomainException;
use Illuminate\Support\Str;

class SurveyReviewReadiness
{
    /** @return list<string> */
    public function issues(TreeSurveyPoint $surveyPoint, bool $checkDuplicateCoordinates = true): array
    {
        $issues = [];

        if ($surveyPoint->registered_tree_id !== null) {
            $issues[] = 'This survey record has already been converted to a tree.';
        }

        if ($surveyPoint->coordinate_status !== 'valid') {
            $issues[] = 'Coordinates must be marked valid.';
        }

        if ($surveyPoint->latitude === null || $surveyPoint->longitude === null) {
            $issues[] = 'Latitude and longitude are required.';
        }

        if ($surveyPoint->campus_area_id === null) {
            $issues[] = 'A campus area must be assigned.';
        }

        if (blank($surveyPoint->approved_species_name)) {
            $issues[] = 'An approved species name is required.';
        } elseif ($this->isCombinedSpeciesLabel($surveyPoint->approved_species_name)) {
            $issues[] = 'The approved species name must identify one species only.';
        }

        if ($checkDuplicateCoordinates && $this->hasDuplicateCoordinates($surveyPoint)) {
            $issues[] = 'Another survey record uses the same coordinates.';
        }

        return $issues;
    }

    public function isReady(TreeSurveyPoint $surveyPoint, bool $checkDuplicateCoordinates = true): bool
    {
        return $this->issues($surveyPoint, $checkDuplicateCoordinates) === [];
    }

    public function assertReady(TreeSurveyPoint $surveyPoint): void
    {
        $issues = $this->issues($surveyPoint);

        if ($issues !== []) {
            throw new DomainException(implode(' ', $issues));
        }
    }

    public function suggestedSpeciesName(?string $recordedSpeciesName): ?string
    {
        if (blank($recordedSpeciesName) || $this->isCombinedSpeciesLabel($recordedSpeciesName)) {
            return null;
        }

        $suggestion = Str::of($recordedSpeciesName)
            ->squish()
            ->replaceMatches('/\s*\d+\s*$/u', '')
            ->trim()
            ->toString();

        return $suggestion !== '' ? $suggestion : null;
    }

    private function isCombinedSpeciesLabel(string $speciesName): bool
    {
        return preg_match('/[\/;]|\s+(?:and|or)\s+/iu', $speciesName) === 1;
    }

    private function hasDuplicateCoordinates(TreeSurveyPoint $surveyPoint): bool
    {
        if (! $surveyPoint->exists || $surveyPoint->latitude === null || $surveyPoint->longitude === null) {
            return false;
        }

        return TreeSurveyPoint::query()
            ->whereKeyNot($surveyPoint->getKey())
            ->where('latitude', $surveyPoint->latitude)
            ->where('longitude', $surveyPoint->longitude)
            ->exists();
    }
}
