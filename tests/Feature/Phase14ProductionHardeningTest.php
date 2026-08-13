<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase14ProductionHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_responses_receive_conservative_security_headers(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
    }

    public function test_admin_responses_are_marked_private_and_no_store(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertOk();
        $this->assertStringContainsString(
            'no-store',
            (string) $response->headers->get('Cache-Control')
        );
        $this->assertStringContainsString(
            'private',
            (string) $response->headers->get('Cache-Control')
        );
        $response->assertHeader('Pragma', 'no-cache');
    }

    public function test_security_middleware_is_registered_globally(): void
    {
        $bootstrap = file_get_contents(base_path('bootstrap/app.php'));

        $this->assertIsString($bootstrap);
        $this->assertStringContainsString('AddSecurityHeaders', $bootstrap);
        $this->assertStringContainsString('$middleware->append(AddSecurityHeaders::class);', $bootstrap);
    }

    public function test_phase_fourteen_release_documents_exist_and_do_not_contain_live_secrets(): void
    {
        foreach ([
            'docs/PRODUCTION-DEPLOYMENT-CHECKLIST.md',
            'docs/BACKUP-RESTORE-RUNBOOK.md',
            'docs/PRODUCTION-ENVIRONMENT-REFERENCE.md',
        ] as $relative) {
            $path = base_path($relative);
            $this->assertFileExists($path);
            $contents = file_get_contents($path);
            $this->assertIsString($contents);
            $this->assertStringNotContainsString('APP_KEY=base64:', $contents);
            $this->assertStringNotContainsString('DB_PASSWORD=', $contents);
        }
    }

    public function test_gitignore_keeps_private_runtime_data_out_of_source_control(): void
    {
        $gitignore = file_get_contents(base_path('.gitignore'));

        $this->assertIsString($gitignore);
        $this->assertStringContainsString('/.nacs-backups/', $gitignore);
        $this->assertStringContainsString('/storage/app/private/', $gitignore);
        $this->assertStringContainsString('/storage/app/admissions/', $gitignore);
        $this->assertStringContainsString('/database/*.sqlite', $gitignore);
        $this->assertStringContainsString('!.env.example', $gitignore);
    }
}
