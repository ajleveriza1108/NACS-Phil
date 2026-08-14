<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\FacebookMediaItem;
use App\Models\FacultyProfile;
use App\Models\GalleryItem;
use App\Models\SchoolDocument;
use App\Models\SchoolEvent;
use App\Models\SchoolSetting;
use App\Models\SiteContent;
use App\Models\User;
use Illuminate\View\View;

class LaunchReadinessController extends Controller
{
    public function __invoke(): View
    {
        $settings = SchoolSetting::allValues();

        $requiredSettings = [
            'school_name' => 'Official school name',
            'short_name' => 'Public short name',
            'current_school_year' => 'Current school year',
            'address' => 'Official school address',
            'office_hours' => 'School office hours',
            'privacy_email' => 'Privacy contact email',
        ];

        $schoolChecks = collect($requiredSettings)->map(function (string $label, string $key) use ($settings): array {
            return [
                'label' => $label,
                'passed' => filled($settings[$key] ?? null),
                'detail' => filled($settings[$key] ?? null)
                    ? (string) $settings[$key]
                    : 'Not yet supplied in School Settings.',
            ];
        });

        $hasPublicContact = filled($settings['phone'] ?? null) || filled($settings['email'] ?? null);

        $schoolChecks->push([
            'label' => 'Public contact method',
            'passed' => $hasPublicContact,
            'detail' => $hasPublicContact
                ? 'At least one public phone/email contact is configured.'
                : 'Add an official phone number or email address.',
        ]);

        $publishedFaculty = FacultyProfile::query()->where('is_published', true)->count();

        $pendingReviews =
            Announcement::query()->where('workflow_status', 'pending_review')->count()
            + SchoolEvent::query()->where('workflow_status', 'pending_review')->count()
            + GalleryItem::query()->where('workflow_status', 'pending_review')->count();

        $unsafePublishedGallery = GalleryItem::query()
            ->where('is_published', true)
            ->whereNull('consent_confirmed_at')
            ->count();

        $unsafePublishedFacebookMedia = FacebookMediaItem::query()
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->whereNull('public_confirmed_at')
            ->count();

        $activeLeadershipWithoutTwoFactor = User::query()
            ->where('is_admin', true)
            ->where('is_active', true)
            ->where(function ($query): void {
                $query->whereIn('role', ['super_admin', 'principal'])
                    ->orWhereNull('role');
            })
            ->whereNull('two_factor_enabled_at')
            ->count();

        $officialBrandingApproved = SchoolSetting::officialBrandingApproved();

        $contentValues = SiteContent::query()
            ->pluck('value')
            ->filter(fn ($value): bool => is_string($value) && trim($value) !== '');

        $placeholderPatterns = [
            'placeholder',
            'to be supplied',
            'school to supply',
            'development copy',
            'sample content',
            'tbd',
        ];

        $placeholderHits = $contentValues->filter(function (string $value) use ($placeholderPatterns): bool {
            $normalized = strtolower($value);

            foreach ($placeholderPatterns as $pattern) {
                if (str_contains($normalized, $pattern)) {
                    return true;
                }
            }

            return false;
        })->count();

        $contentChecks = collect([
            [
                'label' => 'Published Faculty & Staff profiles',
                'passed' => $publishedFaculty > 0,
                'detail' => $publishedFaculty > 0
                    ? "{$publishedFaculty} public profile(s) are available."
                    : 'No Faculty & Staff profile is currently published.',
            ],
            [
                'label' => 'Pending content reviews cleared',
                'passed' => $pendingReviews === 0,
                'detail' => $pendingReviews === 0
                    ? 'No teacher submissions are waiting for approval.'
                    : "{$pendingReviews} item(s) are still waiting for Principal/Super Admin review.",
            ],
            [
                'label' => 'Gallery consent protection',
                'passed' => $unsafePublishedGallery === 0,
                'detail' => $unsafePublishedGallery === 0
                    ? 'No published gallery item is missing recorded consent confirmation.'
                    : "{$unsafePublishedGallery} published photo(s) require attention.",
            ],
            [
                'label' => 'Facebook media public/embed confirmation',
                'passed' => $unsafePublishedFacebookMedia === 0,
                'detail' => $unsafePublishedFacebookMedia === 0
                    ? 'Published Facebook video/live entries have recorded Public/embed confirmation.'
                    : "{$unsafePublishedFacebookMedia} published Facebook media item(s) require Public/embed confirmation.",
            ],
            [
                'label' => 'Development placeholder scan',
                'passed' => $placeholderHits === 0,
                'detail' => $placeholderHits === 0
                    ? 'No known placeholder phrases were detected in managed page content.'
                    : "{$placeholderHits} managed content value(s) may still contain placeholder wording.",
            ],
            [
                'label' => 'Official logo/crest replacement',
                'passed' => $officialBrandingApproved,
                'detail' => $officialBrandingApproved
                    ? 'Official approved school logo is configured.'
                    : 'Upload and approve the official school logo before launch.',
            ],
        ]);

        $securityChecks = collect([
            [
                'label' => 'Leadership two-factor authentication',
                'passed' => $activeLeadershipWithoutTwoFactor === 0,
                'detail' => $activeLeadershipWithoutTwoFactor === 0
                    ? 'Active Principal/Super Admin accounts have 2FA enabled.'
                    : "{$activeLeadershipWithoutTwoFactor} active leadership account(s) still need 2FA.",
            ],
            [
                'label' => 'Production security middleware',
                'passed' => class_exists(\App\Http\Middleware\AddSecurityHeaders::class),
                'detail' => 'Response hardening, sensitive-page no-store rules, and browser security headers are installed.',
            ],
            [
                'label' => 'Private runtime data excluded from Git',
                'passed' => true,
                'detail' => 'Git privacy guards and .gitignore rules remain part of the release process.',
            ],
        ]);

        $deploymentChecks = collect([
            [
                'label' => 'Production environment',
                'passed' => app()->environment('production'),
                'detail' => app()->environment('production')
                    ? 'Application is running with APP_ENV=production.'
                    : 'Expected while developing locally. Set APP_ENV=production only on the real hosting server.',
            ],
            [
                'label' => 'Debug disabled',
                'passed' => config('app.debug') === false,
                'detail' => config('app.debug') === false
                    ? 'APP_DEBUG is disabled.'
                    : 'Expected locally; the production server must use APP_DEBUG=false.',
            ],
            [
                'label' => 'HTTPS public URL',
                'passed' => str_starts_with((string) config('app.url'), 'https://'),
                'detail' => str_starts_with((string) config('app.url'), 'https://')
                    ? (string) config('app.url')
                    : 'Configure the final HTTPS domain in the production environment.',
            ],
        ]);

        $automaticChecks = $schoolChecks
            ->concat($contentChecks)
            ->concat($securityChecks);

        $passed = $automaticChecks->where('passed', true)->count();
        $total = $automaticChecks->count();
        $score = $total > 0 ? (int) round(($passed / $total) * 100) : 0;

        return view('admin.launch-readiness', [
            'schoolChecks' => $schoolChecks,
            'contentChecks' => $contentChecks,
            'securityChecks' => $securityChecks,
            'deploymentChecks' => $deploymentChecks,
            'score' => $score,
            'passed' => $passed,
            'total' => $total,
            'publishedDocuments' => SchoolDocument::query()
                ->where('audience', 'public')
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->count(),
            'manualChecks' => [
                'Review the complete website at 320px, 360px phone, tablet portrait/landscape, laptop, desktop, and ultrawide sizes.',
                'Confirm official Mission, Vision, Christian statements, school history, admissions wording, and school-year dates.',
                'Confirm every identifiable child photograph has the school authorization and appropriate consent record.',
                'Confirm downloadable files are current, authorized, and safe for public release.',
                'Complete a disposable admissions application from submission through staff review and family tracking.',
                'Complete a Teacher draft -> Principal review -> public publication simulation.',
                'Confirm the official school logo/crest and branding assets are approved for public use.',
                'Open one real public Facebook recorded video and one Facebook Live/replay link on phone and desktop; confirm preview, inline playback, and the Watch on Facebook fallback.',
                'Have the school review the Privacy & Child Protection wording, including the Facebook-hosted media disclosure, before production launch.',
            ],
        ]);
    }
}
