<?php

namespace App\Console\Commands;

use App\Models\TreeSurveyPoint;
use App\Support\Survey\SurveySpeciesNormalization;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use JsonException;

#[Signature('tree-survey:audit-species
    {--format=table : Output format: table or json}
    {--limit=50 : Maximum groups shown in table output}')]
#[Description('Audit survey species labels and propose review-safe normalization groups without changing records')]
class AuditTreeSurveySpecies extends Command
{
    /** @throws JsonException */
    public function handle(SurveySpeciesNormalization $normalization): int
    {
        $format = mb_strtolower((string) $this->option('format'));
        $limit = (int) $this->option('limit');

        if (! in_array($format, ['table', 'json'], true)) {
            $this->error('The --format option must be table or json.');

            return self::INVALID;
        }

        if ($limit < 1 || $limit > 500) {
            $this->error('The --limit option must be between 1 and 500.');

            return self::INVALID;
        }

        $surveyPoints = TreeSurveyPoint::query()
            ->select(['id', 'source_file', 'source_row', 'raw_species_name'])
            ->where('source_file', 'trees.xls')
            ->oldest('source_row')
            ->get();
        $report = $normalization->build($surveyPoints);

        if ($format === 'json') {
            $this->line(json_encode([
                'summary' => $report['summary'],
                'groups' => $report['groups']->all(),
                'labels' => $report['labels']->all(),
            ], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

            return self::SUCCESS;
        }

        $this->info('Tree survey species audit (read-only)');
        $this->table(
            ['Metric', 'Value'],
            collect($report['summary'])->map(fn (int|string $value, string $metric): array => [$metric, $value])->values()->all(),
        );
        $this->table(
            ['Proposed label', 'Records', 'Exact labels', 'Classification', 'Possible match', 'Variants'],
            $report['groups']->take($limit)->map(fn (array $group): array => [
                $group['proposed_label'],
                $group['records'],
                $group['exact_label_count'],
                $group['classification'],
                $group['suggested_existing_group'] ?? '',
                implode(' | ', $group['variants']),
            ])->all(),
        );
        $this->comment('No survey records were changed. Use --format=json for the complete reviewer dataset.');

        return self::SUCCESS;
    }
}
