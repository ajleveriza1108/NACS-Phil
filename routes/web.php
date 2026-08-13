<?php

use App\Http\Controllers\Admin\AdmissionApplicationController as AdminAdmissionApplicationController;
use App\Http\Controllers\Admin\AnnouncementController as AdminAnnouncementController;
use App\Http\Controllers\Admin\NewsContentController as AdminNewsContentController;
use App\Http\Controllers\Admin\EventsContentController as AdminEventsContentController;
use App\Http\Controllers\Admin\GalleryContentController as AdminGalleryContentController;
use App\Http\Controllers\Admin\ContactContentController as AdminContactContentController;
use App\Http\Controllers\Admin\AdmissionsContentController as AdminAdmissionsContentController;
use App\Http\Controllers\Admin\ProgramsContentController as AdminProgramsContentController;
use App\Http\Controllers\Admin\AboutContentController as AdminAboutContentController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\StaffController as AdminStaffController;
use App\Http\Controllers\Admin\TrashController as AdminTrashController;
use App\Http\Controllers\Admin\AuditController as AdminAuditController;
use App\Http\Controllers\Admin\GalleryItemController as AdminGalleryItemController;
use App\Http\Controllers\Admin\InquiryController as AdminInquiryController;
use App\Http\Controllers\Admin\SchoolEventController as AdminSchoolEventController;
use App\Http\Controllers\Admin\WebsiteContentController as AdminWebsiteContentController;
use App\Http\Controllers\AdmissionApplicationController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InquiryController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::view('/about', 'pages.about')->name('about');
Route::view('/programs', 'pages.programs')->name('programs');
Route::view('/admissions', 'pages.admissions')->name('admissions');
Route::view('/contact', 'pages.contact')->name('contact');
Route::view('/privacy', 'pages.privacy')->name('privacy');
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
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function (): void {
    Route::get('/', AdminDashboardController::class)->name('dashboard');
    Route::post('/logout', [AdminAuthController::class, 'destroy'])->name('logout');

    Route::middleware('staff_role:super_admin,principal')->group(function (): void {
        Route::get('/trash', [AdminTrashController::class, 'index'])->name('trash.index');
        Route::patch('/trash/{type}/{id}/restore', [AdminTrashController::class, 'restore'])->name('trash.restore');
        Route::delete('/trash/{type}/{id}', [AdminTrashController::class, 'destroy'])->name('trash.destroy');
        Route::get('/audit', [AdminAuditController::class, 'index'])->name('audit.index');

        Route::middleware('staff_role:super_admin,principal')->group(function (): void {
            Route::get('/admissions', [AdminAdmissionApplicationController::class, 'index'])->name('admissions.index');
            Route::get('/admissions/{application}', [AdminAdmissionApplicationController::class, 'show'])->name('admissions.show');
            Route::patch('/admissions/{application}', [AdminAdmissionApplicationController::class, 'update'])->name('admissions.update');
            Route::patch('/admissions/{application}/documents/{document}', [AdminAdmissionApplicationController::class, 'verifyDocument'])->name('admissions.documents.verify');
            Route::get('/admissions/{application}/documents/{document}/download', [AdminAdmissionApplicationController::class, 'downloadDocument'])->name('admissions.documents.download');
            Route::post('/admissions/{application}/rotate-access-code', [AdminAdmissionApplicationController::class, 'rotateAccessCode'])->middleware('throttle:3,10')->name('admissions.rotate-access-code');
        });
    });

    Route::middleware('staff_role:super_admin')->group(function (): void {
        Route::get('/staff', [AdminStaffController::class, 'index'])->name('staff.index');
        Route::get('/staff/create', [AdminStaffController::class, 'create'])->name('staff.create');
        Route::post('/staff', [AdminStaffController::class, 'store'])->name('staff.store');
        Route::get('/staff/{staff}/edit', [AdminStaffController::class, 'edit'])->name('staff.edit');
        Route::patch('/staff/{staff}', [AdminStaffController::class, 'update'])->name('staff.update');
    });

    Route::get('/website-content', [AdminWebsiteContentController::class, 'edit'])->name('website-content.edit')->middleware('staff_role:super_admin,principal');
    Route::patch('/website-content', [AdminWebsiteContentController::class, 'update'])->name('website-content.update')->middleware('staff_role:super_admin,principal');

    Route::get('/about-content', [AdminAboutContentController::class, 'edit'])->name('about-content.edit')->middleware('staff_role:super_admin,principal');
    Route::patch('/about-content', [AdminAboutContentController::class, 'update'])->name('about-content.update')->middleware('staff_role:super_admin,principal');

    Route::get('/programs-content', [AdminProgramsContentController::class, 'edit'])->name('programs-content.edit')->middleware('staff_role:super_admin,principal');
    Route::patch('/programs-content', [AdminProgramsContentController::class, 'update'])->name('programs-content.update')->middleware('staff_role:super_admin,principal');

    Route::get('/admissions-content', [AdminAdmissionsContentController::class, 'edit'])->name('admissions-content.edit')->middleware('staff_role:super_admin,principal');
    Route::patch('/admissions-content', [AdminAdmissionsContentController::class, 'update'])->name('admissions-content.update')->middleware('staff_role:super_admin,principal');

    Route::get('/news-content', [AdminNewsContentController::class, 'edit'])->name('news-content.edit')->middleware('staff_role:super_admin,principal');
    Route::patch('/news-content', [AdminNewsContentController::class, 'update'])->name('news-content.update')->middleware('staff_role:super_admin,principal');

    Route::get('/events-content', [AdminEventsContentController::class, 'edit'])->name('events-content.edit')->middleware('staff_role:super_admin,principal');
    Route::patch('/events-content', [AdminEventsContentController::class, 'update'])->name('events-content.update')->middleware('staff_role:super_admin,principal');

    Route::get('/gallery-content', [AdminGalleryContentController::class, 'edit'])->name('gallery-content.edit')->middleware('staff_role:super_admin,principal');
    Route::patch('/gallery-content', [AdminGalleryContentController::class, 'update'])->name('gallery-content.update')->middleware('staff_role:super_admin,principal');

    Route::get('/contact-content', [AdminContactContentController::class, 'edit'])->name('contact-content.edit')->middleware('staff_role:super_admin,principal');
    Route::patch('/contact-content', [AdminContactContentController::class, 'update'])->name('contact-content.update')->middleware('staff_role:super_admin,principal');

    Route::resource('announcements', AdminAnnouncementController::class)->except('show');
    Route::resource('events', AdminSchoolEventController::class)->except('show');
    Route::resource('gallery', AdminGalleryItemController::class)
        ->parameters(['gallery' => 'galleryItem'])
        ->except('show');

    Route::get('/inquiries', [AdminInquiryController::class, 'index'])->name('inquiries.index')->middleware('staff_role:super_admin,principal');
    Route::get('/inquiries/{inquiry}', [AdminInquiryController::class, 'show'])->name('inquiries.show')->middleware('staff_role:super_admin,principal');
    Route::patch('/inquiries/{inquiry}', [AdminInquiryController::class, 'update'])->name('inquiries.update')->middleware('staff_role:super_admin,principal');
});
