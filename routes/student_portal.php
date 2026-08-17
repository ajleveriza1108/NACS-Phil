<?php

use App\Http\Controllers\AcademicRecordController;
use App\Http\Controllers\StudentProfilePhotoController;
use App\Http\Controllers\Admin\StudentAssignmentController;
use App\Http\Controllers\Admin\StudentAttendanceController;
use App\Http\Controllers\Admin\StudentController as AdminStudentController;
use App\Http\Controllers\Admin\StudentDocumentController;
use App\Http\Controllers\Admin\StudentFinanceController;
use App\Http\Controllers\Admin\StudentGradeController;
use App\Http\Controllers\Admin\StudentGuardianController;
use App\Http\Controllers\Portal\AuthController as PortalAuthController;
use App\Http\Controllers\Portal\DashboardController as PortalDashboardController;
use App\Http\Controllers\Portal\PasswordController as PortalPasswordController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/portal/login', [PortalAuthController::class, 'create'])->name('portal.login');
    Route::post('/portal/login', [PortalAuthController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('portal.login.store');
});

Route::prefix('portal')->name('portal.')->middleware('portal')->group(function (): void {
    Route::get('/', [PortalDashboardController::class, 'index'])->name('dashboard');
    Route::get('/students/{student}', [PortalDashboardController::class, 'show'])->name('students.show');
    Route::get('/students/{student}/report-card', [AcademicRecordController::class, 'reportCard'])->middleware('throttle:30,1')->name('students.report-card');
    Route::get('/students/{student}/report-card.pdf', [AcademicRecordController::class, 'reportCardPdf'])->middleware('throttle:10,1')->name('students.report-card.pdf');
    Route::get('/students/{student}/academic-history', [AcademicRecordController::class, 'transcript'])->middleware('throttle:30,1')->name('students.transcript');
    Route::get('/students/{student}/academic-history.pdf', [AcademicRecordController::class, 'transcriptPdf'])->middleware('throttle:10,1')->name('students.transcript.pdf');
    Route::get('/students/{student}/photo', [StudentProfilePhotoController::class, 'show'])->middleware('throttle:60,1')->name('students.photo');
    Route::get('/password', [PortalPasswordController::class, 'edit'])->name('password.edit');
    Route::patch('/password', [PortalPasswordController::class, 'update'])->middleware('throttle:5,10')->name('password.update');
    Route::post('/logout', [PortalAuthController::class, 'destroy'])->name('logout');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin', 'staff_permission:students.manage'])->group(function (): void {
    Route::get('/students', [AdminStudentController::class, 'index'])->name('students.index');
    Route::get('/students/create', [AdminStudentController::class, 'create'])->name('students.create');
    Route::post('/students/link-existing', [StudentAssignmentController::class, 'requestExisting'])->middleware('throttle:10,10')->name('students.assignments.request-existing');
    Route::post('/students', [AdminStudentController::class, 'store'])->middleware('throttle:10,10')->name('students.store');
    Route::get('/students/{student}', [AdminStudentController::class, 'show'])->name('students.show');
    Route::get('/students/{student}/edit', [AdminStudentController::class, 'edit'])->name('students.edit');
    Route::patch('/students/{student}', [AdminStudentController::class, 'update'])->name('students.update');
    Route::get('/students/{student}/report-card', [AcademicRecordController::class, 'reportCard'])->middleware('throttle:30,1')->name('students.report-card');
    Route::get('/students/{student}/report-card.pdf', [AcademicRecordController::class, 'reportCardPdf'])->middleware('throttle:10,1')->name('students.report-card.pdf');
    Route::get('/students/{student}/transcript', [AcademicRecordController::class, 'transcript'])->middleware('throttle:30,1')->name('students.transcript');
    Route::get('/students/{student}/transcript.pdf', [AcademicRecordController::class, 'transcriptPdf'])->middleware('throttle:10,1')->name('students.transcript.pdf');
    Route::get('/students/{student}/photo', [StudentProfilePhotoController::class, 'show'])->middleware('throttle:60,1')->name('students.photo');
    Route::post('/students/{student}/photo', [StudentProfilePhotoController::class, 'store'])->middleware('throttle:10,10')->name('students.photo.store');
    Route::delete('/students/{student}/photo', [StudentProfilePhotoController::class, 'destroy'])->middleware('throttle:10,10')->name('students.photo.destroy');

    Route::post('/students/{student}/resend-registration', [AdminStudentController::class, 'resendPortalRegistration'])->middleware('throttle:3,60')->name('students.resend-registration');
    Route::post('/students/{student}/grades', [StudentGradeController::class, 'store'])->middleware('throttle:nacs-sensitive-write')->name('students.grades.store');
    Route::delete('/students/{student}/grades/{grade}', [StudentGradeController::class, 'destroy'])->middleware('throttle:nacs-sensitive-write')->name('students.grades.destroy');
    Route::post('/students/{student}/attendance', [StudentAttendanceController::class, 'store'])->middleware('throttle:nacs-sensitive-write')->name('students.attendance.store');
    Route::post('/students/{student}/finance', [StudentFinanceController::class, 'store'])->middleware('throttle:nacs-sensitive-write')->name('students.finance.store');
    Route::post('/students/{student}/assignments', [StudentAssignmentController::class, 'store'])->middleware('throttle:nacs-sensitive-write')->name('students.assignments.store');
    Route::patch('/students/{student}/assignments/{assignment}/approve', [StudentAssignmentController::class, 'approve'])->middleware('throttle:nacs-sensitive-write')->name('students.assignments.approve');
    Route::patch('/students/{student}/assignments/{assignment}/reject', [StudentAssignmentController::class, 'reject'])->middleware('throttle:nacs-sensitive-write')->name('students.assignments.reject');
    Route::delete('/students/{student}/assignments/{assignment}', [StudentAssignmentController::class, 'destroy'])->middleware('throttle:nacs-sensitive-write')->name('students.assignments.destroy');
    Route::post('/students/{student}/guardians', [StudentGuardianController::class, 'store'])->middleware('throttle:nacs-sensitive-write')->name('students.guardians.store');
    Route::post('/students/{student}/documents', [StudentDocumentController::class, 'store'])->middleware('throttle:nacs-sensitive-write')->name('students.documents.store');
});
