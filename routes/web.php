<?php

use App\Http\Controllers\Admin\AnnouncementController as AdminAnnouncementController;
use App\Http\Controllers\Admin\NewsContentController as AdminNewsContentController;
use App\Http\Controllers\Admin\AdmissionsContentController as AdminAdmissionsContentController;
use App\Http\Controllers\Admin\ProgramsContentController as AdminProgramsContentController;
use App\Http\Controllers\Admin\AboutContentController as AdminAboutContentController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\GalleryItemController as AdminGalleryItemController;
use App\Http\Controllers\Admin\InquiryController as AdminInquiryController;
use App\Http\Controllers\Admin\SchoolEventController as AdminSchoolEventController;
use App\Http\Controllers\Admin\WebsiteContentController as AdminWebsiteContentController;
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
Route::get('/gallery', [GalleryController::class, 'index'])->name('gallery.index');
Route::post('/inquiries', [InquiryController::class, 'store'])->middleware('throttle:5,10')->name('inquiries.store');

Route::middleware('guest')->group(function (): void {
    Route::get('/admin/login', [AdminAuthController::class, 'create'])->name('admin.login');
    Route::post('/admin/login', [AdminAuthController::class, 'store'])->middleware('throttle:5,1')->name('admin.login.store');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function (): void {
    Route::get('/', AdminDashboardController::class)->name('dashboard');
    Route::post('/logout', [AdminAuthController::class, 'destroy'])->name('logout');

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

    Route::resource('announcements', AdminAnnouncementController::class)->except('show');
    Route::resource('events', AdminSchoolEventController::class)->except('show');
    Route::resource('gallery', AdminGalleryItemController::class)
        ->parameters(['gallery' => 'galleryItem'])
        ->except('show');

    Route::get('/inquiries', [AdminInquiryController::class, 'index'])->name('inquiries.index');
    Route::get('/inquiries/{inquiry}', [AdminInquiryController::class, 'show'])->name('inquiries.show');
    Route::patch('/inquiries/{inquiry}', [AdminInquiryController::class, 'update'])->name('inquiries.update');
});
