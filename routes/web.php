<?php

use App\Http\Controllers\AcademicCalendarController;
use App\Http\Controllers\Admin\AcademicCalendarEntryController as AdminAcademicCalendarEntryController;
use App\Http\Controllers\Admin\AdmissionApplicationController as AdminAdmissionApplicationController;
use App\Http\Controllers\Admin\AdmissionChecklistController as AdminAdmissionChecklistController;
use App\Http\Controllers\Admin\AdmissionsContentController as AdminAdmissionsContentController;
use App\Http\Controllers\Admin\AnnouncementController as AdminAnnouncementController;
use App\Http\Controllers\Admin\AuditController as AdminAuditController;
use App\Http\Controllers\Admin\BrandingController as AdminBrandingController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\AboutContentController as AdminAboutContentController;
use App\Http\Controllers\Admin\ContactContentController as AdminContactContentController;
use App\Http\Controllers\Admin\ContentReviewController as AdminContentReviewController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\EventsContentController as AdminEventsContentController;
use App\Http\Controllers\Admin\FacultyProfileController as AdminFacultyProfileController;
use App\Http\Controllers\Admin\GalleryContentController as AdminGalleryContentController;
use App\Http\Controllers\Admin\GalleryItemController as AdminGalleryItemController;
use App\Http\Controllers\Admin\InquiryController as AdminInquiryController;
use App\Http\Controllers\Admin\LaunchReadinessController as AdminLaunchReadinessController;
use App\Http\Controllers\Admin\MediaAssetController as AdminMediaAssetController;
use App\Http\Controllers\Admin\NewsContentController as AdminNewsContentController;
use App\Http\Controllers\Admin\ProgramsContentController as AdminProgramsContentController;
use App\Http\Controllers\Admin\SchoolDocumentController as AdminSchoolDocumentController;
use App\Http\Controllers\Admin\SchoolEventController as AdminSchoolEventController;
use App\Http\Controllers\Admin\SchoolSettingController as AdminSchoolSettingController;
use App\Http\Controllers\Admin\SecurityController as AdminSecurityController;
use App\Http\Controllers\Admin\SeoSettingController as AdminSeoSettingController;
use App\Http\Controllers\Admin\StaffController as AdminStaffController;
use App\Http\Controllers\Admin\SystemHealthController as AdminSystemHealthController;
use App\Http\Controllers\Admin\TrashController as AdminTrashController;
use App\Http\Controllers\Admin\WebsiteContentController as AdminWebsiteContentController;
use App\Http\Controllers\AdmissionApplicationController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\FacultyController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InquiryController;
use App\Http\Controllers\PublicDocumentController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::view('/about', 'pages.about')->name('about');
Route::view('/programs', 'pages.programs')->name('programs');
Route::view('/admissions', 'pages.admissions')->name('admissions');
Route::view('/contact', 'pages.contact')->name('contact');
Route::view('/privacy', 'pages.privacy')->name('privacy');
Route::get('/sitemap.xml', [SitemapController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');

Route::get('/faculty', [FacultyController::class, 'index'])->name('faculty.index');
Route::get('/documents', [PublicDocumentController::class, 'index'])->name('documents.index');
Route::get('/documents/{document:slug}/download', [PublicDocumentController::class, 'download'])
    ->middleware('throttle:30,1')
    ->name('documents.download');
Route::get('/calendar', [AcademicCalendarController::class, 'index'])->name('calendar.index');

Route::get('/announcements', [AnnouncementController::class, 'index'])->name('announcements.index');
Route::get('/announcements/{announcement:slug}', [AnnouncementController::class, 'show'])->name('announcements.show');
Route::get('/events', [EventController::class, 'index'])->name('events.index');
Route::get('/events/{event:slug}', [EventController::class, 'show'])->name('events.show');
Route::get('/gallery', [GalleryController::class, 'index'])->name('gallery.index');
Route::post('/inquiries', [InquiryController::class, 'store'])->middleware('throttle:5,10')->name('inquiries.store');

Route::get('/admissions/apply', [AdmissionApplicationController::class, 'create'])->name('admissions.apply');
Route::post('/admissions/apply', [AdmissionApplicationController::class, 'store'])->middleware('throttle:3,60')->name('admissions.apply.store');
Route::get('/admissions/receipt/{application}', [AdmissionApplicationController::class, 'receipt'])->name('admissions.receipt');
Route::get('/admissions/track', [AdmissionApplicationController::class, 'track'])->name('admissions.track');
Route::post('/admissions/track', [AdmissionApplicationController::class, 'authenticate'])->middleware('throttle:5,10')->name('admissions.track.authenticate');
Route::post('/admissions/track/logout', [AdmissionApplicationController::class, 'logout'])->name('admissions.track.logout');

Route::middleware('admission.access')->group(function (): void {
    Route::get('/admissions/status/{application}', [AdmissionApplicationController::class, 'show'])->name('admissions.status');
    Route::post('/admissions/status/{application}/documents', [AdmissionApplicationController::class, 'uploadDocument'])->middleware('throttle:5,60')->name('admissions.documents.store');
    Route::delete('/admissions/status/{application}/documents/{document}', [AdmissionApplicationController::class, 'destroyDocument'])->name('admissions.documents.destroy');
});

Route::middleware('guest')->group(function (): void {
    Route::get('/admin/login', [AdminAuthController::class, 'create'])->name('admin.login');
    Route::post('/admin/login', [AdminAuthController::class, 'store'])->middleware('throttle:5,1')->name('admin.login.store');
    Route::get('/admin/two-factor', [AdminSecurityController::class, 'challenge'])->name('admin.two-factor.challenge');
    Route::post('/admin/two-factor', [AdminSecurityController::class, 'verifyChallenge'])
        ->middleware('throttle:10,1')
        ->name('admin.two-factor.verify');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function (): void {
    Route::get('/', AdminDashboardController::class)->name('dashboard');
    Route::post('/logout', [AdminAuthController::class, 'destroy'])->name('logout');

    Route::get('/security', [AdminSecurityController::class, 'index'])->name('security.index');
    Route::patch('/security/password', [AdminSecurityController::class, 'updatePassword'])->name('security.password');
    Route::post('/security/two-factor/setup', [AdminSecurityController::class, 'beginTwoFactor'])->name('security.two-factor.setup');
    Route::post('/security/two-factor/confirm', [AdminSecurityController::class, 'confirmTwoFactor'])->name('security.two-factor.confirm');
    Route::delete('/security/two-factor', [AdminSecurityController::class, 'disableTwoFactor'])->name('security.two-factor.disable');
    Route::post('/security/revoke-sessions', [AdminSecurityController::class, 'revokeOtherSessions'])->name('security.revoke-sessions');

    Route::resource('announcements', AdminAnnouncementController::class)->except('show');
    Route::resource('events', AdminSchoolEventController::class)->except('show');
    Route::resource('gallery', AdminGalleryItemController::class)
        ->parameters(['gallery' => 'galleryItem'])
        ->except('show');

    Route::get('/media', [AdminMediaAssetController::class, 'index'])->name('media.index');
    Route::get('/media/create', [AdminMediaAssetController::class, 'create'])->name('media.create');
    Route::post('/media', [AdminMediaAssetController::class, 'store'])->name('media.store');
    Route::delete('/media/{medium}', [AdminMediaAssetController::class, 'destroy'])->name('media.destroy');

    Route::middleware('staff_role:super_admin,principal')->group(function (): void {
        Route::get('/trash', [AdminTrashController::class, 'index'])->name('trash.index');
        Route::patch('/trash/{type}/{id}/restore', [AdminTrashController::class, 'restore'])->name('trash.restore');
        Route::delete('/trash/{type}/{id}', [AdminTrashController::class, 'destroy'])->name('trash.destroy');
        Route::get('/audit', [AdminAuditController::class, 'index'])->name('audit.index');

        Route::get('/reviews', [AdminContentReviewController::class, 'index'])->name('reviews.index');
        Route::patch('/reviews/{type}/{id}', [AdminContentReviewController::class, 'decide'])->name('reviews.decide');

        Route::resource('faculty', AdminFacultyProfileController::class)->except('show');
        Route::resource('documents', AdminSchoolDocumentController::class)->except('show');
        Route::get('/documents/{document}/download', [AdminSchoolDocumentController::class, 'download'])->name('documents.download');
        Route::resource('calendar', AdminAcademicCalendarEntryController::class)->except('show');

        Route::get('/admissions', [AdminAdmissionApplicationController::class, 'index'])->name('admissions.index');
        Route::get('/admissions/{application}', [AdminAdmissionApplicationController::class, 'show'])->name('admissions.show');
        Route::patch('/admissions/{application}', [AdminAdmissionApplicationController::class, 'update'])->name('admissions.update');
        Route::patch('/admissions/{application}/documents/{document}', [AdminAdmissionApplicationController::class, 'verifyDocument'])->name('admissions.documents.verify');
        Route::get('/admissions/{application}/documents/{document}/download', [AdminAdmissionApplicationController::class, 'downloadDocument'])->name('admissions.documents.download');
        Route::post('/admissions/{application}/rotate-access-code', [AdminAdmissionApplicationController::class, 'rotateAccessCode'])->middleware('throttle:3,10')->name('admissions.rotate-access-code');
        Route::patch('/admissions/{application}/checklist/{item}', [AdminAdmissionChecklistController::class, 'update'])->name('admissions.checklist.update');

        Route::get('/inquiries', [AdminInquiryController::class, 'index'])->name('inquiries.index');
        Route::get('/inquiries/{inquiry}', [AdminInquiryController::class, 'show'])->name('inquiries.show');
        Route::patch('/inquiries/{inquiry}', [AdminInquiryController::class, 'update'])->name('inquiries.update');

        Route::get('/website-content', [AdminWebsiteContentController::class, 'edit'])->name('website-content.edit');
        Route::patch('/website-content', [AdminWebsiteContentController::class, 'update'])->name('website-content.update');
        Route::get('/about-content', [AdminAboutContentController::class, 'edit'])->name('about-content.edit');
        Route::patch('/about-content', [AdminAboutContentController::class, 'update'])->name('about-content.update');
        Route::get('/programs-content', [AdminProgramsContentController::class, 'edit'])->name('programs-content.edit');
        Route::patch('/programs-content', [AdminProgramsContentController::class, 'update'])->name('programs-content.update');
        Route::get('/admissions-content', [AdminAdmissionsContentController::class, 'edit'])->name('admissions-content.edit');
        Route::patch('/admissions-content', [AdminAdmissionsContentController::class, 'update'])->name('admissions-content.update');
        Route::get('/news-content', [AdminNewsContentController::class, 'edit'])->name('news-content.edit');
        Route::patch('/news-content', [AdminNewsContentController::class, 'update'])->name('news-content.update');
        Route::get('/events-content', [AdminEventsContentController::class, 'edit'])->name('events-content.edit');
        Route::patch('/events-content', [AdminEventsContentController::class, 'update'])->name('events-content.update');
        Route::get('/gallery-content', [AdminGalleryContentController::class, 'edit'])->name('gallery-content.edit');
        Route::patch('/gallery-content', [AdminGalleryContentController::class, 'update'])->name('gallery-content.update');
        Route::get('/contact-content', [AdminContactContentController::class, 'edit'])->name('contact-content.edit');
        Route::patch('/contact-content', [AdminContactContentController::class, 'update'])->name('contact-content.update');

        Route::get('/seo', [AdminSeoSettingController::class, 'edit'])->name('seo.edit');
        Route::patch('/seo', [AdminSeoSettingController::class, 'update'])->name('seo.update');
        Route::get('/branding', [AdminBrandingController::class, 'edit'])->name('branding.edit');
        Route::post('/branding/logo', [AdminBrandingController::class, 'store'])->name('branding.store');
        Route::delete('/branding/logo', [AdminBrandingController::class, 'destroy'])->name('branding.destroy');        Route::get('/launch-readiness', AdminLaunchReadinessController::class)->name('launch-readiness');
        Route::get('/school-settings', [AdminSchoolSettingController::class, 'edit'])->name('settings.edit');
        Route::patch('/school-settings', [AdminSchoolSettingController::class, 'update'])->name('settings.update');
    });

    Route::middleware('staff_role:super_admin')->group(function (): void {
        Route::get('/staff', [AdminStaffController::class, 'index'])->name('staff.index');
        Route::get('/staff/create', [AdminStaffController::class, 'create'])->name('staff.create');
        Route::post('/staff', [AdminStaffController::class, 'store'])->name('staff.store');
        Route::get('/staff/{staff}/edit', [AdminStaffController::class, 'edit'])->name('staff.edit');
        Route::patch('/staff/{staff}', [AdminStaffController::class, 'update'])->name('staff.update');
        Route::post('/staff/{staff}/reset-two-factor', [AdminStaffController::class, 'resetTwoFactor'])->name('staff.reset-two-factor');
        Route::get('/system-health', AdminSystemHealthController::class)->name('system-health');
    });
});
