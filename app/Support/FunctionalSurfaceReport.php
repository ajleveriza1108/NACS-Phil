<?php

namespace App\Support;

use Illuminate\Support\Facades\Route;

final class FunctionalSurfaceReport
{
    /** @var array<int, string> */
    private const REQUIRED_ROUTES = [
        'home',
        'about',
        'programs',
        'admissions',
        'contact',
        'privacy',
        'sitemap',
        'robots',
        'faculty.index',
        'documents.index',
        'documents.download',
        'calendar.index',
        'announcements.index',
        'announcements.show',
        'events.index',
        'events.show',
        'gallery.index',
        'media.index',
        'inquiries.store',
        'admissions.apply',
        'admissions.apply.store',
        'admissions.receipt',
        'admissions.track',
        'admissions.track.authenticate',
        'admissions.track.logout',
        'admissions.status',
        'admissions.documents.store',
        'admissions.documents.destroy',
        'admin.login',
        'admin.login.store',
        'admin.two-factor.challenge',
        'admin.two-factor.verify',
        'admin.dashboard',
        'admin.logout',
        'admin.security.index',
        'admin.security.password',
        'admin.security.two-factor.setup',
        'admin.security.two-factor.confirm',
        'admin.security.two-factor.disable',
        'admin.security.revoke-sessions',
        'admin.announcements.index',
        'admin.announcements.create',
        'admin.announcements.store',
        'admin.announcements.edit',
        'admin.announcements.update',
        'admin.announcements.destroy',
        'admin.events.index',
        'admin.events.create',
        'admin.events.store',
        'admin.events.edit',
        'admin.events.update',
        'admin.events.destroy',
        'admin.gallery.index',
        'admin.gallery.create',
        'admin.gallery.store',
        'admin.gallery.edit',
        'admin.gallery.update',
        'admin.gallery.destroy',
        'admin.media.index',
        'admin.media.create',
        'admin.media.store',
        'admin.media.destroy',
        'admin.facebook-media.index',
        'admin.facebook-media.create',
        'admin.facebook-media.store',
        'admin.facebook-media.edit',
        'admin.facebook-media.update',
        'admin.facebook-media.destroy',
        'admin.trash.index',
        'admin.trash.restore',
        'admin.trash.destroy',
        'admin.audit.index',
        'admin.reviews.index',
        'admin.reviews.decide',
        'admin.faculty.index',
        'admin.faculty.create',
        'admin.faculty.store',
        'admin.faculty.edit',
        'admin.faculty.update',
        'admin.faculty.destroy',
        'admin.documents.index',
        'admin.documents.create',
        'admin.documents.store',
        'admin.documents.edit',
        'admin.documents.update',
        'admin.documents.destroy',
        'admin.documents.download',
        'admin.calendar.index',
        'admin.calendar.create',
        'admin.calendar.store',
        'admin.calendar.edit',
        'admin.calendar.update',
        'admin.calendar.destroy',
        'admin.admissions.index',
        'admin.admissions.show',
        'admin.admissions.update',
        'admin.admissions.documents.verify',
        'admin.admissions.documents.download',
        'admin.admissions.rotate-access-code',
        'admin.admissions.checklist.update',
        'admin.inquiries.index',
        'admin.inquiries.show',
        'admin.inquiries.update',
        'admin.website-content.edit',
        'admin.website-content.update',
        'admin.about-content.edit',
        'admin.about-content.update',
        'admin.programs-content.edit',
        'admin.programs-content.update',
        'admin.admissions-content.edit',
        'admin.admissions-content.update',
        'admin.news-content.edit',
        'admin.news-content.update',
        'admin.events-content.edit',
        'admin.events-content.update',
        'admin.gallery-content.edit',
        'admin.gallery-content.update',
        'admin.contact-content.edit',
        'admin.contact-content.update',
        'admin.seo.edit',
        'admin.seo.update',
        'admin.branding.edit',
        'admin.branding.store',
        'admin.branding.destroy',
        'admin.launch-readiness',
        'admin.settings.edit',
        'admin.settings.update',
        'admin.staff.index',
        'admin.staff.create',
        'admin.staff.store',
        'admin.staff.edit',
        'admin.staff.update',
        'admin.staff.reset-two-factor',
        'admin.system-health',
    ];

    /** @var array<int, string> */
    private const REQUIRED_VIEWS = [
        'resources/views/partials/public-header.blade.php',
        'resources/views/partials/public-footer.blade.php',
        'resources/views/layouts/site-current.blade.php',
        'resources/views/admin/layouts/app.blade.php',
        'resources/views/admissions/apply.blade.php',
        'resources/views/admissions/track.blade.php',
        'resources/views/admissions/status.blade.php',
        'resources/views/media/index.blade.php',
        'resources/views/admin/facebook-media/form.blade.php',
        'resources/views/gallery/index.blade.php',
    ];

    /** @var array<int, string> */
    private const REQUIRED_PUBLIC_ASSETS = [
        'assets/current/pages/public.js',
        'assets/current/pages/gallery.js',
        'assets/current/pages/home.css',
        'assets/current/admin.js',
        'build/manifest.json',
    ];

    /**
     * @return array<int, array{key:string,label:string,passed:bool,required:bool,detail:string}>
     */
    public function checks(): array
    {
        $checks = [];

        foreach (self::REQUIRED_ROUTES as $routeName) {
            $checks[] = $this->check(
                'route_'.$routeName,
                'Route '.$routeName,
                Route::has($routeName),
                true,
                Route::has($routeName) ? 'Named route is registered.' : 'Named route is missing.'
            );
        }

        foreach (self::REQUIRED_VIEWS as $relative) {
            $checks[] = $this->check(
                'view_'.str_replace(['/', '.'], '_', $relative),
                'View '.$relative,
                is_file(base_path($relative)),
                true,
                base_path($relative)
            );
        }

        foreach (self::REQUIRED_PUBLIC_ASSETS as $relative) {
            $checks[] = $this->check(
                'asset_'.str_replace(['/', '.'], '_', $relative),
                'Asset '.$relative,
                is_file(public_path($relative)),
                true,
                public_path($relative)
            );
        }

        return $checks;
    }

    /**
     * @param array<int, array{key:string,label:string,passed:bool,required:bool,detail:string}> $checks
     * @return array<int, array{key:string,label:string,passed:bool,required:bool,detail:string}>
     */
    public function requiredFailures(array $checks): array
    {
        return array_values(array_filter(
            $checks,
            fn (array $check): bool => $check['required'] && ! $check['passed']
        ));
    }

    /**
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        $checks = $this->checks();

        return [
            'application' => 'NACS-Phil',
            'required_failures' => count($this->requiredFailures($checks)),
            'checks' => $checks,
        ];
    }

    /**
     * @return array{key:string,label:string,passed:bool,required:bool,detail:string}
     */
    private function check(
        string $key,
        string $label,
        bool $passed,
        bool $required,
        string $detail
    ): array {
        return compact('key', 'label', 'passed', 'required', 'detail');
    }
}
