<?php

namespace Tests\Feature;

use App\Models\Tree;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class TreeDirectoryTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_tree_directory_is_available(): void
    {
        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('The database-backed directory test requires the PDO SQLite extension.');
        }

        $this->seed(DatabaseSeeder::class);
        Tree::factory()->verified()->create();

        $this->get('/trees')
            ->assertOk()
            ->assertSee('Tree Directory')
            ->assertSee('Narra')
            ->assertSee('T-0008')
            ->assertSee('aria-controls="directory-filters"', false)
            ->assertSee('mobile-filter-toggle', false)
            ->assertSee('desktop-filter-state', false)
            ->assertSee('desktop-filter-collapse', false)
            ->assertSee('desktop-filter-reveal', false)
            ->assertSee('#results-heading', false)
            ->assertSee('Go forward 10 pages')
            ->assertSee('Back 10 pages')
            ->assertSee('pagination-pages-slot', false)
            ->assertSee('class="tree-status"', false)
            ->assertDontSee('class="status"', false)
            ->assertDontSee('College of Forestry · College of Forestry');
    }
}
