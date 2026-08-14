<?php

namespace Tests\Feature;

use App\Models\Inquiry;
use App\Support\ProductionReadiness;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class Phase29AdaptiveTurnstilePreparationTest extends TestCase
{
    use RefreshDatabase;

    private const VERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.turnstile.enabled' => true,
            'services.turnstile.site_key' => '1x00000000000000000000AA',
            'services.turnstile.secret_key' => '1x0000000000000000000000000000000AA',
            'services.turnstile.expected_hostname' => 'localhost',
            'services.turnstile.verify_url' => self::VERIFY_URL,
        ]);
    }

    public function test_protected_forms_render_managed_interaction_only_turnstile_actions(): void
    {
        foreach ([
            [route('contact'), 'inquiry'],
            [route('admissions.apply'), 'admissions_apply'],
            [route('admissions.track'), 'admissions_track'],
            [route('admin.login'), 'admin_login'],
        ] as [$url, $action]) {
            $this->get($url)
                ->assertOk()
                ->assertSee('https://challenges.cloudflare.com/turnstile/v0/api.js', false)
                ->assertSee('data-appearance="interaction-only"', false)
                ->assertSee('data-action="'.$action.'"', false);
        }
    }

    public function test_turnstile_can_be_disabled_for_local_development_without_changing_existing_form_flow(): void
    {
        config(['services.turnstile.enabled' => false]);

        $this->post(route('inquiries.store'), [
            'guardian_name' => 'Local Development Parent',
            'email' => 'local@example.test',
            'phone' => '',
            'student_name' => '',
            'level_interested' => 'General Inquiry',
            'message' => 'Local development should not need CAPTCHA.',
            'privacy_consent' => '1',
            'website' => '',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('inquiries', [
            'guardian_name' => 'Local Development Parent',
        ]);
    }

    public function test_missing_turnstile_token_is_rejected_before_inquiry_is_created(): void
    {
        $this->post(route('inquiries.store'), [
            'guardian_name' => 'Protected Parent',
            'email' => 'protected@example.test',
            'phone' => '',
            'student_name' => '',
            'level_interested' => 'General Inquiry',
            'message' => 'This request has no Turnstile token.',
            'privacy_consent' => '1',
            'website' => '',
        ])->assertSessionHasErrors('turnstile');

        $this->assertDatabaseCount('inquiries', 0);
    }

    public function test_valid_server_side_turnstile_verification_allows_inquiry_submission(): void
    {
        Http::fake([
            self::VERIFY_URL => Http::response([
                'success' => true,
                'hostname' => 'localhost',
                'action' => 'inquiry',
                'error-codes' => [],
            ], 200),
        ]);

        $this->post(route('inquiries.store'), [
            'guardian_name' => 'Verified Parent',
            'email' => 'verified@example.test',
            'phone' => '',
            'student_name' => '',
            'level_interested' => 'General Inquiry',
            'message' => 'This request passed server-side verification.',
            'privacy_consent' => '1',
            'website' => '',
            'cf-turnstile-response' => 'XXXX.DUMMY.TOKEN.XXXX',
        ])->assertSessionHasNoErrors()->assertSessionHas('success');

        $this->assertDatabaseHas('inquiries', [
            'guardian_name' => 'Verified Parent',
        ]);

        Http::assertSent(function ($request): bool {
            return $request->url() === self::VERIFY_URL
                && $request['secret'] === '1x0000000000000000000000000000000AA'
                && $request['response'] === 'XXXX.DUMMY.TOKEN.XXXX';
        });
    }

    public function test_wrong_action_or_hostname_is_rejected(): void
    {
        foreach ([
            ['action' => 'admin_login', 'hostname' => 'localhost'],
            ['action' => 'inquiry', 'hostname' => 'wrong.example.test'],
        ] as $payload) {
            Http::fake([
                self::VERIFY_URL => Http::response([
                    'success' => true,
                    'hostname' => $payload['hostname'],
                    'action' => $payload['action'],
                    'error-codes' => [],
                ], 200),
            ]);

            $this->post(route('inquiries.store'), [
                'guardian_name' => 'Rejected Parent',
                'email' => 'rejected@example.test',
                'phone' => '',
                'student_name' => '',
                'level_interested' => 'General Inquiry',
                'message' => 'This request should be rejected.',
                'privacy_consent' => '1',
                'website' => '',
                'cf-turnstile-response' => 'XXXX.DUMMY.TOKEN.XXXX',
            ])->assertSessionHasErrors('turnstile');
        }

        $this->assertDatabaseCount('inquiries', 0);
    }

    public function test_siteverify_network_failure_fails_closed_with_user_friendly_error(): void
    {
        Http::fake([
            self::VERIFY_URL => Http::response('Unavailable', 503),
        ]);

        $this->post(route('inquiries.store'), [
            'guardian_name' => 'Network Parent',
            'email' => 'network@example.test',
            'phone' => '',
            'student_name' => '',
            'level_interested' => 'General Inquiry',
            'message' => 'Verification service unavailable.',
            'privacy_consent' => '1',
            'website' => '',
            'cf-turnstile-response' => 'XXXX.DUMMY.TOKEN.XXXX',
        ])->assertSessionHasErrors('turnstile');

        $this->assertDatabaseCount('inquiries', 0);
    }

    public function test_production_readiness_requires_live_turnstile_configuration(): void
    {
        $keys = collect((new ProductionReadiness())->checks())->pluck('key')->all();

        $this->assertContains('turnstile_enabled', $keys);
        $this->assertContains('turnstile_site_key', $keys);
        $this->assertContains('turnstile_secret_key', $keys);
        $this->assertContains('turnstile_hostname', $keys);
    }

    public function test_tracked_environment_templates_keep_local_disabled_and_production_enabled(): void
    {
        $local = file_get_contents(base_path('.env.example'));
        $production = file_get_contents(base_path('PRODUCTION_ENV_TEMPLATE.txt'));

        $this->assertStringContainsString('TURNSTILE_ENABLED=false', $local);
        $this->assertStringContainsString('TURNSTILE_SITE_KEY=', $local);
        $this->assertStringContainsString('TURNSTILE_SECRET_KEY=', $local);

        $this->assertStringContainsString('TURNSTILE_ENABLED=true', $production);
        $this->assertStringContainsString('TURNSTILE_SITE_KEY=YOUR_CLOUDFLARE_TURNSTILE_SITE_KEY', $production);
        $this->assertStringContainsString('TURNSTILE_SECRET_KEY=YOUR_CLOUDFLARE_TURNSTILE_SECRET_KEY', $production);
        $this->assertStringContainsString('TURNSTILE_EXPECTED_HOSTNAME=YOUR-FINAL-DOMAIN', $production);
    }

    public function test_privacy_page_discloses_adaptive_anti_bot_service(): void
    {
        $this->get(route('privacy'))
            ->assertOk()
            ->assertSee('Cloudflare Turnstile')
            ->assertSee('anti-bot');
    }
}
