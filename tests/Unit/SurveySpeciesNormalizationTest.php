<?php

namespace Tests\Unit;

use App\Models\TreeSurveyPoint;
use App\Support\Survey\SurveySpeciesNormalization;
use PHPUnit\Framework\TestCase;

class SurveySpeciesNormalizationTest extends TestCase
{
    public function test_it_groups_numbered_and_case_variants_without_changing_the_original_labels(): void
    {
        $report = (new SurveySpeciesNormalization)->build(collect([
            $this->surveyPoint('Mahogany'),
            $this->surveyPoint('Mahogany1'),
            $this->surveyPoint('Mahogany 2'),
            $this->surveyPoint('mahogany3'),
        ]));

        $group = $report['groups']->first();

        $this->assertSame('Mahogany', $group['proposed_label']);
        $this->assertSame(4, $group['records']);
        $this->assertSame('mechanical_cleanup', $group['classification']);
        $this->assertSame('Mahogany1', $report['labels']->firstWhere('original_label', 'Mahogany1')['original_label']);
    }

    public function test_it_flags_combined_labels_for_record_by_record_review(): void
    {
        $report = (new SurveySpeciesNormalization)->build(collect([
            $this->surveyPoint('Kakauate 1/ Malunggai 2'),
        ]));

        $this->assertSame('combined_species_label', $report['groups']->first()['classification']);
        $this->assertSame('Kakauate 1/ Malunggai 2', $report['groups']->first()['proposed_label']);
        $this->assertFalse($report['labels']->first()['safe_to_apply_automatically']);
    }

    public function test_it_suggests_close_spelling_variants_only_as_manual_review_candidates(): void
    {
        $surveyPoints = collect([
            ...array_fill(0, 3, $this->surveyPoint('Gmelina')),
            $this->surveyPoint('Gmellina'),
        ]);
        $report = (new SurveySpeciesNormalization)->build($surveyPoints);
        $misspelling = $report['groups']->firstWhere('normalized_key', 'gmellina');

        $this->assertSame('possible_spelling_variant', $misspelling['classification']);
        $this->assertSame('Gmelina', $misspelling['suggested_existing_group']);
        $this->assertFalse($report['labels']->firstWhere('original_label', 'Gmellina')['safe_to_apply_automatically']);
    }

    private function surveyPoint(string $label): TreeSurveyPoint
    {
        return new TreeSurveyPoint([
            'source_file' => 'trees.xls',
            'raw_species_name' => $label,
        ]);
    }
}
