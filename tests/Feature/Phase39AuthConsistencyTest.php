<?php

namespace Tests\Feature;

use Tests\TestCase;

class Phase39AuthConsistencyTest extends TestCase
{
    public function test_auth_experiences_share_the_phase_thirty_nine_consistency_layer(): void
    {
        $adminLogin = file_get_contents(resource_path('views/admin/auth/login.blade.php'));
        $adminTwoFactor = file_get_contents(resource_path('views/admin/auth/two-factor.blade.php'));
        $portalLayout = file_get_contents(resource_path('views/portal/layout.blade.php'));
        $portalLogin = file_get_contents(resource_path('views/portal/auth/login.blade.php'));
        $portalPassword = file_get_contents(resource_path('views/portal/auth/password.blade.php'));

        $this->assertStringContainsString("asset('assets/phase39-auth/auth.css')", $adminLogin);
        $this->assertStringContainsString("asset('assets/phase39-auth/auth.css')", $adminTwoFactor);
        $this->assertStringContainsString("asset('assets/phase39-auth/auth.css')", $portalLayout);
        $this->assertStringContainsString('nacs-auth-card', $adminLogin);
        $this->assertStringContainsString('nacs-auth-card', $adminTwoFactor);
        $this->assertStringContainsString('nacs-auth-card', $portalLogin);
        $this->assertStringContainsString('nacs-auth-card', $portalPassword);
    }

    public function test_auth_pages_use_the_official_school_brand_not_the_old_development_mark(): void
    {
        $adminLogin = file_get_contents(resource_path('views/admin/auth/login.blade.php'));
        $adminTwoFactor = file_get_contents(resource_path('views/admin/auth/two-factor.blade.php'));

        $this->assertStringContainsString('SchoolSetting::logoUrl()', $adminLogin);
        $this->assertStringContainsString('SchoolSetting::logoUrl()', $adminTwoFactor);
        $this->assertStringNotContainsString('nacs-development-mark.svg', $adminLogin);
        $this->assertStringNotContainsString('nacs-development-mark.svg', $adminTwoFactor);
    }

    public function test_admin_login_preserves_the_existing_public_contract(): void
    {
        $adminLogin = file_get_contents(resource_path('views/admin/auth/login.blade.php'));

        $this->assertStringContainsString('NACS-Phil Administration', $adminLogin);
    }

    public function test_portal_password_form_preserves_the_registered_update_route_and_patch_method(): void
    {
        $portalPassword = file_get_contents(resource_path('views/portal/auth/password.blade.php'));

        $this->assertStringContainsString("route('portal.password.update')", $portalPassword);
        $this->assertStringContainsString("@method('PATCH')", $portalPassword);
        $this->assertStringNotContainsString("route('portal.password.store')", $portalPassword);
    }

    public function test_auth_consistency_stylesheet_contains_mobile_guards(): void
    {
        $authCss = file_get_contents(public_path('assets/phase39-auth/auth.css'));

        $this->assertStringContainsString('.nacs-auth-body', $authCss);
        $this->assertStringContainsString('@media (max-width: 720px)', $authCss);
        $this->assertStringContainsString('@media (max-width: 360px)', $authCss);
    }
}
