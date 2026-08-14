<?php

namespace App\Support;

final class BrowserAcceptanceGate
{
    /** @var array<string, string> */
    private const REQUIRED_MANUAL_CHECKS = [
        'phone_320' => '320 px narrow phone layout',
        'phone_standard' => 'Standard phone layout',
        'tablet_portrait' => 'Tablet portrait layout',
        'tablet_landscape' => 'Tablet landscape layout',
        'laptop' => 'Laptop layout',
        'desktop' => 'Desktop layout',
        'ultrawide' => 'Large/ultrawide layout',
        'header_navigation' => 'Header/mobile navigation and every primary CTA',
        'resources_menu' => 'Resources menu and all resource links',
        'gallery_lightbox' => 'Gallery filtering/lightbox/mouse/touch/keyboard',
        'facebook_playback' => 'Real public Facebook video playback',
        'contact_inquiry' => 'Contact inquiry submission',
        'admissions_apply' => 'Admissions preliminary application',
        'admissions_track' => 'Admissions tracking access/logout',
        'turnstile_desktop_mobile' => 'Turnstile desktop/mobile and visible challenge path',
        'admin_login_2fa' => 'Admin sign-in, lockout, and two-factor flow',
        'admin_crud_safe_trash' => 'Admin CRUD, permissions, Safe Trash, restore, permanent-delete boundaries',
        'uploads_downloads' => 'Gallery/media/document upload and authorized download flows',
        'keyboard_focus_reduced_motion' => 'Keyboard focus, skip links, focus visibility, reduced-motion behavior',
        'no_crop_overflow' => 'No clipped controls, horizontal overflow, or unreachable buttons',
    ];

    public function __construct(private readonly FunctionalSurfaceReport $surface)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        $surfaceChecks = $this->surface->checks();
        $automatedFailures = $this->surface->requiredFailures($surfaceChecks);
        $record = $this->readRecord();

        $manual = [];
        $missing = [];

        foreach (self::REQUIRED_MANUAL_CHECKS as $key => $label) {
            $passed = ($record['checks'][$key] ?? false) === true;

            $manual[$key] = [
                'label' => $label,
                'passed' => $passed,
            ];

            if (! $passed) {
                $missing[] = $key;
            }
        }

        return [
            'application' => 'NACS-Phil',
            'phase' => 32,
            'automated_required_failures' => count($automatedFailures),
            'manual_record_present' => is_file(base_path('.nacs-browser-acceptance.json')),
            'manual_record_path' => '.nacs-browser-acceptance.json',
            'manual_checks' => $manual,
            'manual_missing' => $missing,
            'ready_for_cutover' => $automatedFailures === [] && $missing === [],
            'note' => 'Automated checks cannot truthfully replace physical browser, touch, keyboard, CAPTCHA, and device testing.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function requiredChecks(): array
    {
        return self::REQUIRED_MANUAL_CHECKS;
    }

    /**
     * @return array<string, mixed>
     */
    private function readRecord(): array
    {
        $path = base_path('.nacs-browser-acceptance.json');

        if (! is_file($path)) {
            return [];
        }

        $decoded = json_decode((string) file_get_contents($path), true);

        return is_array($decoded) ? $decoded : [];
    }
}
