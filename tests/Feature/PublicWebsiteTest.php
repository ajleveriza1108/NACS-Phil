<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicWebsiteTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_are_available(): void
    {
        foreach (['/', '/about', '/programs', '/admissions', '/announcements', '/events', '/gallery', '/contact', '/privacy'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_homepage_identifies_nacs_phil(): void
    {
        $this->get('/')->assertOk()->assertSee('NACS-Phil');
    }
}
