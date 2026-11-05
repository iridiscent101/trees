<?php

namespace Tests\Feature;

use Tests\TestCase;

class AboutPageTest extends TestCase
{
    public function test_about_page_is_available(): void
    {
        $this->get('/about')
            ->assertOk()
            ->assertSee('About the Project')
            ->assertSee('Why This System Matters')
            ->assertSee('How the QR System Works')
            ->assertSee('menu-active bg-primary font-semibold text-white!', false);
    }
}
