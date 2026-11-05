<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class DeploymentReadinessTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_deployment_check_rejects_non_production_configuration(): void
    {
        $this->artisan('app:deployment-check')
            ->expectsOutputToContain('Deployment readiness checks failed')
            ->assertFailed();
    }

    public function test_deployment_check_accepts_production_configuration(): void
    {
        $this->app->detectEnvironment(fn (): string => 'production');
        config()->set([
            'app.debug' => false,
            'app.url' => 'https://trees.nvsu.edu.ph',
            'mail.default' => 'smtp',
            'queue.default' => 'database',
            'cache.default' => 'file',
        ]);

        $this->artisan('app:deployment-check')
            ->expectsOutputToContain('All application-level deployment checks passed')
            ->assertSuccessful();
    }
}
