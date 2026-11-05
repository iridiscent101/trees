<?php

namespace App\Console\Commands;

use App\Actions\ImportTreeSurveyWorkbook;
use Illuminate\Console\Command;
use RuntimeException;

class ImportTreeSurvey extends Command
{
    protected $signature = 'tree-survey:import {file : Path to an XLS or XLSX survey workbook} {--source= : Original source filename for audit metadata}';

    protected $description = 'Import provisional tree coordinates from an XLS or XLSX survey workbook';

    public function handle(ImportTreeSurveyWorkbook $importer): int
    {
        try {
            $result = $importer->handle(
                (string) $this->argument('file'),
                $this->option('source') ? (string) $this->option('source') : null,
            );
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info(sprintf(
            'Imported %d survey rows: %d campus coordinates, %d outliers, %d invalid.',
            $result['total'],
            $result['valid'],
            $result['outlier'],
            $result['invalid'],
        ));

        return self::SUCCESS;
    }
}
