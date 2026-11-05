<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Support\Facades\DB;
use Throwable;

#[Signature('app:deployment-check')]
#[Description('Verify that the application is configured for a production deployment')]
class CheckDeploymentReadiness extends Command
{
    public function handle(Migrator $migrator): int
    {
        $checks = [
            'Production environment' => app()->isProduction(),
            'Debug mode disabled' => ! config('app.debug'),
            'Application key configured' => filled(config('app.key')),
            'HTTPS application URL' => str_starts_with((string) config('app.url'), 'https://'),
            'Public storage linked' => file_exists(public_path('storage')),
            'Production mailer configured' => config('mail.default') !== 'log',
            'Persistent queue configured' => config('queue.default') !== 'sync',
            'Persistent cache configured' => config('cache.default') !== 'array',
            'Database connection available' => $this->databaseIsAvailable(),
            'All database migrations applied' => $this->allMigrationsAreApplied($migrator),
        ];

        $this->table(['Deployment check', 'Result'], collect($checks)
            ->map(fn (bool $passed, string $label): array => [$label, $passed ? 'PASS' : 'FAIL'])
            ->values()
            ->all());

        if (in_array(false, $checks, true)) {
            $this->error('Deployment readiness checks failed. Correct every FAIL before publishing the system.');

            return self::FAILURE;
        }

        $this->info('All application-level deployment checks passed.');

        return self::SUCCESS;
    }

    private function databaseIsAvailable(): bool
    {
        try {
            DB::connection()->getPdo();

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    private function allMigrationsAreApplied(Migrator $migrator): bool
    {
        try {
            $files = $migrator->getMigrationFiles(database_path('migrations'));
            $ran = $migrator->getRepository()->getRan();

            return array_diff(array_keys($files), $ran) === [];
        } catch (Throwable) {
            return false;
        }
    }
}
