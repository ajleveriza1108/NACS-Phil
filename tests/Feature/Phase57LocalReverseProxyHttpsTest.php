<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class Phase57LocalReverseProxyHttpsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware('web')->get('/_phase57/proxy-probe', function () {
            return response()->json([
                'secure' => request()->secure(),
                'scheme' => request()->getScheme(),
                'host' => request()->getHost(),
                'asset' => asset('assets/current/pages/home.css'),
            ]);
        });
    }

    public function test_loopback_reverse_proxy_can_forward_https_scheme_and_public_host(): void
    {
        $response = $this
            ->withServerVariables([
                'REMOTE_ADDR' => '127.0.0.1',
                'HTTP_HOST' => '127.0.0.1:8000',
                'SERVER_PORT' => '8000',
                'HTTPS' => 'off',
            ])
            ->withHeaders([
                'X-Forwarded-Proto' => 'https',
                'X-Forwarded-Host' => 'preview.example.test',
                'X-Forwarded-Port' => '443',
                'X-Forwarded-For' => '198.51.100.24',
            ])
            ->get('/_phase57/proxy-probe');

        $response
            ->assertOk()
            ->assertJson([
                'secure' => true,
                'scheme' => 'https',
                'host' => 'preview.example.test',
                'asset' => 'https://preview.example.test/assets/current/pages/home.css',
            ]);
    }

    public function test_non_loopback_client_cannot_spoof_forwarded_https_or_host(): void
    {
        $response = $this
            ->withServerVariables([
                'REMOTE_ADDR' => '203.0.113.50',
                'HTTP_HOST' => 'local.example.test',
                'SERVER_PORT' => '80',
                'HTTPS' => 'off',
            ])
            ->withHeaders([
                'X-Forwarded-Proto' => 'https',
                'X-Forwarded-Host' => 'spoofed.example.test',
                'X-Forwarded-Port' => '443',
            ])
            ->get('/_phase57/proxy-probe');

        $response
            ->assertOk()
            ->assertJson([
                'secure' => false,
                'scheme' => 'http',
            ]);

        $payload = $response->json();

        $this->assertNotSame('spoofed.example.test', $payload['host']);
        $this->assertStringStartsWith('http://', $payload['asset']);
        $this->assertStringNotContainsString('spoofed.example.test', $payload['asset']);
    }

    public function test_proxy_configuration_is_loopback_scoped_not_global_wildcard(): void
    {
        $bootstrap = (string) file_get_contents(base_path('bootstrap/app.php'));

        $this->assertStringContainsString("at: ['127.0.0.1', '::1']", $bootstrap);
        $this->assertStringContainsString('Request::HEADER_X_FORWARDED_PROTO', $bootstrap);
        $this->assertStringContainsString('Request::HEADER_X_FORWARDED_HOST', $bootstrap);
        $this->assertStringNotContainsString("trustProxies(at: '*')", $bootstrap);
    }
}
