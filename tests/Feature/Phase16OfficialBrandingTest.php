<?php

namespace Tests\Feature;

use App\Models\SchoolSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Phase16OfficialBrandingTest extends TestCase
{
    use RefreshDatabase;

    private function staff(string $role): User
    {
        return User::factory()->create([
            'is_admin' => true,
            'role' => $role,
            'is_active' => true,
        ]);
    }

    private function validPngUpload(): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'nacs-logo-');
        file_put_contents($path, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAQAAAAEACAIAAADTED8xAAACvklEQVR4nO3TMQEAIAzAMMC/2ElAxo4mCvr0zsyBqrcdAJsMQJoBSDMAaQYgzQCkGYA0A5BmANIMQJoBSDMAaQYgzQCkGYA0A5BmANIMQJoBSDMAaQYgzQCkGYA0A5BmANIMQJoBSDMAaQYgzQCkGYA0A5BmANIMQJoBSDMAaQYgzQCkGYA0A5BmANIMQJoBSDMAaQYgzQCkGYA0A5BmANIMQJoBSDMAaQYgzQCkGYA0A5BmANIMQJoBSDMAaQYgzQCkGYA0A5BmANIMQJoBSDMAaQYgzQCkGYA0A5BmANIMQJoBSDMAaQYgzQCkGYA0A5BmANIMQJoBSDMAaQYgzQCkGYA0A5BmANIMQJoBSDMAaQYgzQCkGYA0A5BmANIMQJoBSDMAaQYgzQCkGYA0A5BmANIMQJoBSDMAaQYgzQCkGYA0A5BmANIMQJoBSDMAaQYgzQCkGYA0A5BmANIMQJoBSDMAaQYgzQCkGYA0A5BmANIMQJoBSDMAaQYgzQCkGYA0A5BmANIMQJoBSDMAaQYgzQCkGYA0A5BmANIMQJoBSDMAaQYgzQCkGYA0A5BmANIMQJoBSDMAaQYgzQCkGYA0A5BmANIMQJoBSDMAaQYgzQCkGYA0A5BmANIMQJoBSDMAaQYgzQCkGYA0A5BmANIMQJoBSDMAaQYgzQCkGYA0A5BmANIMQJoBSDMAaQYgzQCkGYA0A5BmANIMQJoBSDMAaQYgzQCkGYA0A5BmANIMQJoBSDMAaQYgzQCkGYA0A5BmANIMQJoBSDMAaQYgzQCkGYA0A5BmANIMQJoBSDMAaQYgzQCkGYA0A5BmANIMQJoBSDMAaQYgzQCkGYA0A5BmANIMQJoBSDMAaQYgzQCkGYA0A5BmANIMQJoBSDMAaQYgzQCkGYA0A5BmANIMQJoBSDMAaQYgzQCkGYC0D3dZBNBfrtGOAAAAAElFTkSuQmCC'));

        return new UploadedFile(
            $path,
            'nacs-official-logo.png',
            'image/png',
            null,
            true
        );
    }

    private function svgUpload(): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'nacs-svg-');
        file_put_contents(
            $path,
            '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'
        );

        return new UploadedFile(
            $path,
            'unsafe-logo.svg',
            'image/svg+xml',
            null,
            true
        );
    }

    public function test_principal_can_open_branding_manager_and_teacher_cannot(): void
    {
        $principal = $this->staff('principal');
        $teacher = $this->staff('teacher');

        $this->actingAs($principal)
            ->get('/admin/branding')
            ->assertOk()
            ->assertSee('Branding Manager')
            ->assertSee('officially approved for public use');

        $this->actingAs($teacher)
            ->get('/admin/branding')
            ->assertForbidden();
    }

    public function test_approved_logo_upload_is_used_by_public_and_admin_shells(): void
    {
        Storage::fake('public');

        $principal = $this->staff('principal');

        $this->actingAs($principal)
            ->post('/admin/branding/logo', [
                'official_logo' => $this->validPngUpload(),
                'official_logo_alt' => 'NACS-Phil official school logo',
                'official_branding_approved' => '1',
            ])
            ->assertSessionHasNoErrors();

        $path = SchoolSetting::valueFor('official_logo_path');

        $this->assertNotNull($path);
        $this->assertStringStartsWith('branding/', $path);
        Storage::disk('public')->assertExists($path);
        $this->assertTrue(SchoolSetting::officialBrandingApproved());

        $this->get('/')
            ->assertOk()
            ->assertSee('/storage/branding/', false)
            ->assertSee('NACS-Phil official school logo');

        $this->actingAs($principal)
            ->get('/admin')
            ->assertOk()
            ->assertSee('/storage/branding/', false);

        $this->actingAs($principal)
            ->get('/admin/launch-readiness')
            ->assertOk()
            ->assertSee('Official approved school logo is configured.');
    }

    public function test_svg_logo_upload_is_rejected(): void
    {
        Storage::fake('public');

        $principal = $this->staff('principal');

        $this->actingAs($principal)
            ->post('/admin/branding/logo', [
                'official_logo' => $this->svgUpload(),
                'official_logo_alt' => 'Logo',
                'official_branding_approved' => '1',
            ])
            ->assertSessionHasErrors('official_logo');

        $this->assertNull(SchoolSetting::valueFor('official_logo_path'));
    }

    public function test_removing_uploaded_logo_returns_to_bundled_official_logo(): void
    {
        Storage::fake('public');

        $principal = $this->staff('principal');

        $this->actingAs($principal)
            ->post('/admin/branding/logo', [
                'official_logo' => $this->validPngUpload(),
                'official_logo_alt' => 'NACS-Phil official school logo',
                'official_branding_approved' => '1',
            ])
            ->assertSessionHasNoErrors();

        $this->actingAs($principal)
            ->delete('/admin/branding/logo')
            ->assertSessionHasNoErrors();

        $this->assertFalse(SchoolSetting::officialBrandingApproved());

        $this->get('/')
            ->assertOk()
            ->assertSee('nacs-official-logo.png', false);

        $this->actingAs($principal)
            ->get('/admin/launch-readiness')
            ->assertOk()
            ->assertSee('Upload and approve the official school logo before launch.');
    }

    public function test_desktop_header_exposes_school_resources_menu(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('nacs16-resources', false)
            ->assertSee('Faculty &amp; Staff', false)
            ->assertSee('Academic Calendar')
            ->assertSee('Documents');
    }

    public function test_production_footer_does_not_show_development_launch_warning(): void
    {
        $original = app()->environment();
        app()->detectEnvironment(fn () => 'production');

        try {
            $this->get('/')
                ->assertOk()
                ->assertDontSee('Official content must be reviewed before public launch.');
        } finally {
            app()->detectEnvironment(fn () => $original);
        }
    }
}
