<?php

namespace Tests\Feature;

use App\Models\AdmissionApplication;
use App\Models\Announcement;
use App\Models\FacebookMediaItem;
use App\Models\SchoolDocument;
use App\Models\SchoolEvent;
use App\Models\User;
use App\Support\FunctionalSurfaceReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class Phase27FullFunctionalInteractionAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_required_named_routes_views_and_interaction_assets_exist(): void
    {
        $report = new FunctionalSurfaceReport();
        $failures = $report->requiredFailures($report->checks());

        $this->assertSame([], $failures);
    }

    public function test_functional_check_command_and_json_report_are_available(): void
    {
        $exit = Artisan::call('nacs:functional-check', ['--strict' => true]);
        $this->assertSame(0, $exit);
        $this->assertStringContainsString('All required functional-surface checks passed', Artisan::output());

        $jsonExit = Artisan::call('nacs:functional-check', ['--json' => true, '--strict' => true]);
        $decoded = json_decode(Artisan::output(), true);

        $this->assertSame(0, $jsonExit);
        $this->assertIsArray($decoded);
        $this->assertSame('NACS-Phil', $decoded['application']);
        $this->assertSame(0, $decoded['required_failures']);
    }

    public function test_all_top_level_public_pages_and_entry_points_return_success(): void
    {
        foreach ([
            'home',
            'about',
            'programs',
            'admissions',
            'announcements.index',
            'events.index',
            'gallery.index',
            'contact',
            'privacy',
            'faculty.index',
            'calendar.index',
            'documents.index',
            'media.index',
            'admissions.apply',
            'admissions.track',
            'sitemap',
            'robots',
            'admin.login',
        ] as $routeName) {
            $this->get(route($routeName))->assertOk();
        }

        $this->get('/up')->assertOk();
    }

    public function test_dynamic_public_news_event_document_and_facebook_media_paths_work(): void
    {
        $announcement = Announcement::create([
            'title' => 'Functional Audit News',
            'excerpt' => 'Functional audit excerpt.',
            'body' => 'Functional audit body.',
            'type' => 'info',
            'sort_order' => 0,
            'published_at' => now(),
        ]);

        $event = SchoolEvent::create([
            'title' => 'Functional Audit Event',
            'description' => 'Functional audit event.',
            'venue' => 'School Campus',
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDay()->addHour(),
            'is_all_day' => false,
            'published_at' => now(),
        ]);

        $root = storage_path('app/private/documents');

        if (! is_dir($root)) {
            mkdir($root, 0750, true);
        }

        file_put_contents($root.'/phase27-public.pdf', '%PDF-1.4 phase27');

        $document = SchoolDocument::create([
            'title' => 'Functional Audit Handbook',
            'description' => 'Approved public audit file.',
            'category' => 'Handbook',
            'file_path' => 'phase27-public.pdf',
            'original_name' => 'phase27-handbook.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 20,
            'audience' => 'public',
            'published_at' => now(),
            'sort_order' => 0,
        ]);

        FacebookMediaItem::create([
            'title' => 'Functional Audit Video',
            'media_type' => 'video',
            'facebook_url' => 'https://www.facebook.com/nacsphil/videos/123456789/',
            'status' => 'published',
            'published_at' => now(),
            'public_confirmed_at' => now(),
        ]);

        $this->get(route('announcements.show', $announcement))->assertOk()->assertSee('Functional Audit News');
        $this->get(route('events.show', $event))->assertOk()->assertSee('Functional Audit Event');
        $this->get(route('documents.download', $document))->assertOk();
        $this->get(route('media.index'))->assertOk()->assertSee('Functional Audit Video')->assertSee('<iframe', false);
    }

    public function test_family_inquiry_application_receipt_tracking_and_logout_flow_works(): void
    {
        $this->post(route('inquiries.store'), [
            'guardian_name' => 'Functional Parent',
            'email' => 'functional-parent@example.test',
            'phone' => '',
            'student_name' => 'Functional Learner',
            'level_interested' => 'Elementary',
            'message' => 'Please send the official admissions requirements.',
            'privacy_consent' => '1',
            'website' => '',
        ])->assertSessionHasNoErrors()->assertSessionHas('success');

        $response = $this->post(route('admissions.apply.store'), [
            'guardian_name' => 'Functional Parent',
            'guardian_email' => 'functional-parent@example.test',
            'guardian_phone' => '',
            'student_name' => 'Functional Learner',
            'applying_for_level' => 'Grade 4',
            'school_year' => '2026-2027',
            'family_notes' => 'Functional audit application.',
            'privacy_consent' => '1',
            'application_consent' => '1',
            'website' => '',
        ]);

        $application = AdmissionApplication::latest('id')->firstOrFail();

        $response->assertRedirect(route('admissions.receipt', $application));
        $this->get(route('admissions.receipt', $application))->assertOk()->assertSee($application->reference_code);

        $plain = 'ABCD-EFGH-JKLM';
        $application->update([
            'access_code_hash' => Hash::make(AdmissionApplication::normalizeAccessCode($plain)),
        ]);

        $this->post(route('admissions.track.authenticate'), [
            'reference_code' => $application->reference_code,
            'access_code' => $plain,
        ])->assertRedirect(route('admissions.status', $application));

        $this->get(route('admissions.status', $application))->assertOk()->assertSee('Functional Learner');
        $this->post(route('admissions.track.logout'))->assertRedirect(route('admissions.track'));
    }

    public function test_super_admin_can_open_every_nonparameterized_school_manager_screen(): void
    {
        $super = User::factory()->create([
            'is_admin' => true,
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        $this->actingAs($super);

        foreach ([
            'admin.dashboard',
            'admin.security.index',
            'admin.announcements.index',
            'admin.announcements.create',
            'admin.events.index',
            'admin.events.create',
            'admin.gallery.index',
            'admin.gallery.create',
            'admin.media.index',
            'admin.media.create',
            'admin.facebook-media.index',
            'admin.facebook-media.create',
            'admin.trash.index',
            'admin.audit.index',
            'admin.reviews.index',
            'admin.faculty.index',
            'admin.faculty.create',
            'admin.documents.index',
            'admin.documents.create',
            'admin.calendar.index',
            'admin.calendar.create',
            'admin.admissions.index',
            'admin.inquiries.index',
            'admin.website-content.edit',
            'admin.about-content.edit',
            'admin.programs-content.edit',
            'admin.admissions-content.edit',
            'admin.news-content.edit',
            'admin.events-content.edit',
            'admin.gallery-content.edit',
            'admin.contact-content.edit',
            'admin.seo.edit',
            'admin.branding.edit',
            'admin.launch-readiness',
            'admin.settings.edit',
            'admin.staff.index',
            'admin.staff.create',
            'admin.system-health',
        ] as $routeName) {
            $this->get(route($routeName))->assertOk();
        }
    }

    public function test_every_literal_named_route_reference_in_blade_files_points_to_a_registered_route(): void
    {
        $root = resource_path('views');
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root));
        $missing = [];

        foreach ($iterator as $file) {
            if (! $file->isFile() || ! str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }

            $source = file_get_contents($file->getPathname());

            if (! is_string($source)) {
                continue;
            }

            preg_match_all("/route\\(\\s*['\\\"]([^'\\\"]+)['\\\"]/", $source, $matches);

            foreach (array_unique($matches[1] ?? []) as $routeName) {
                if (! Route::has($routeName)) {
                    $missing[] = $file->getPathname().' -> '.$routeName;
                }
            }
        }

        $this->assertSame([], $missing, implode(PHP_EOL, $missing));
    }

    public function test_every_literal_public_asset_reference_in_blade_files_exists(): void
    {
        $root = resource_path('views');
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root));
        $missing = [];

        foreach ($iterator as $file) {
            if (! $file->isFile() || ! str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }

            $source = file_get_contents($file->getPathname());

            if (! is_string($source)) {
                continue;
            }

            preg_match_all("/asset\\(\\s*['\\\"]([^'\\\"]+)['\\\"]\\s*\\)/", $source, $matches);

            foreach (array_unique($matches[1] ?? []) as $asset) {
                if (! is_file(public_path($asset))) {
                    $missing[] = $file->getPathname().' -> '.$asset;
                }
            }
        }

        $this->assertSame([], $missing, implode(PHP_EOL, $missing));
    }

    public function test_current_mobile_gallery_lightbox_and_facebook_preview_controls_have_handlers(): void
    {
        $header = file_get_contents(resource_path('views/partials/public-header.blade.php'));
        $menuJs = file_get_contents(public_path('assets/current/pages/public.js'));
        $galleryJs = file_get_contents(public_path('assets/current/pages/gallery.js'));
        $facebookForm = file_get_contents(resource_path('views/admin/facebook-media/form.blade.php'));
        $adminJs = file_get_contents(public_path('assets/current/admin.js'));

        $this->assertStringContainsString('data-nacs11-menu-button', $header);
        $this->assertStringContainsString('aria-expanded="false"', $header);
        $this->assertStringContainsString('data-nacs11-mobile-nav', $header);
        $this->assertStringContainsString('button.addEventListener("click"', $menuJs);
        $this->assertStringContainsString('event.key === "Escape"', $menuJs);
        $this->assertStringContainsString('menu.hidden = expanded', $menuJs);

        $gallery = $this->get(route('gallery.index'))->assertOk();
        $gallery->assertSee('data-g-lightbox', false);
        $gallery->assertSee('data-g-close', false);
        $gallery->assertSee('data-g-prev', false);
        $gallery->assertSee('data-g-next', false);
        $this->assertStringContainsString('ops.forEach', $galleryJs);
        $this->assertStringContainsString('ArrowLeft', $galleryJs);
        $this->assertStringContainsString('ArrowRight', $galleryJs);

        $this->assertStringContainsString('data-facebook-url-input', $facebookForm);
        $this->assertStringContainsString('data-facebook-preview-frame', $facebookForm);
        $this->assertStringContainsString("document.createElement('iframe')", $adminJs);
        $this->assertStringContainsString("input.addEventListener('paste'", $adminJs);
    }

    public function test_primary_public_forms_render_real_actions_and_csrf_tokens(): void
    {
        $contact = $this->get(route('contact'))->assertOk();
        $apply = $this->get(route('admissions.apply'))->assertOk();
        $track = $this->get(route('admissions.track'))->assertOk();
        $login = $this->get(route('admin.login'))->assertOk();

        $contact->assertSee(route('inquiries.store'), false)->assertSee('name="_token"', false);
        $apply->assertSee(route('admissions.apply.store'), false)->assertSee('name="_token"', false);
        $track->assertSee(route('admissions.track.authenticate'), false)->assertSee('name="_token"', false);
        $login->assertSee(route('admin.login.store'), false)->assertSee('name="_token"', false);
    }
}
