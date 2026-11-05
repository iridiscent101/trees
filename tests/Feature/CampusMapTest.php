<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class CampusMapTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_campus_map_is_available(): void
    {
        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('The database-backed campus map test requires the PDO SQLite extension.');
        }

        $this->seed(DatabaseSeeder::class);

        $response = $this->get('/campus-map')
            ->assertOk()
            ->assertSee('Campus Map')
            ->assertSee('Filter Trees on Map')
            ->assertSee('Pick coordinates')
            ->assertSee('Copy latitude &amp; longitude', false)
            ->assertSee('id="picked-latitude"', false)
            ->assertSee('id="picked-longitude"', false)
            ->assertSee('Map Summary');

        $this->assertMatchesRegularExpression(
            '/<aside class="filter-panel panel">.*id="coordinate-picker-toggle".*<\/aside>\s*<section class="campus-map"/s',
            $response->getContent(),
        );
    }
}
