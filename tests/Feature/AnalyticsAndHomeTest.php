<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsAndHomeTest extends TestCase
{
    use RefreshDatabase;

    public function test_ga4_script_is_rendered_when_configured(): void
    {
        config([
            'analytics.ga4.enabled' => true,
            'analytics.ga4.measurement_id' => 'G-W23LY5HJBT',
        ]);

        $response = $this->get(route('public.home'));

        $response->assertOk();
        $response->assertSee('https://www.googletagmanager.com/gtag/js?id=G-W23LY5HJBT', false);
        $response->assertSee("gtag('config',", false);
        $response->assertSee('G-W23LY5HJBT', false);
    }

    public function test_home_has_inscreva_se_button(): void
    {
        $response = $this->get(route('public.home'));

        $response->assertOk();
        $response->assertSee('Inscreva-se');
        $response->assertSee(route('public.registrations.create'), false);
    }
}
