<?php

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
    Route::get('/password', [PortalPasswordController::class, 'edit'])->name('password.edit');
    Route::patch('/password', [PortalPasswordController::class, 'update'])->name('password.update');
    Route::post('/logout', [PortalAuthController::class, 'destroy'])->name('logout');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function (): void {
    Route::get('/students', [AdminStudentController::class, 'index'])->name('students.index');
    Route::get('/students/create', [AdminStudentController::class, 'create'])->name('students.create');
    Route::post('/students', [AdminStudentController::class, 'store'])->name('students.store');
    Route::get('/students/{student}', [AdminStudentController::class, 'show'])->name('students.show');
    Route::get('/students/{student}/edit', [AdminStudentController::class, 'edit'])->name('students.edit');
    Route::patch('/students/{student}', [AdminStudentController::class, 'update'])->name('students.update');

    Route::post('/students/{student}/grades', [StudentGradeController::class, 'store'])->name('students.grades.store');
    Route::delete('/students/{student}/grades/{grade}', [StudentGradeController::class, 'destroy'])->name('students.grades.destroy');
    Route::post('/students/{student}/attendance', [StudentAttendanceController::class, 'store'])->name('students.attendance.store');
    Route::post('/students/{student}/finance', [StudentFinanceController::class, 'store'])->name('students.finance.store');
    Route::post('/students/{student}/assignments', [StudentAssignmentController::class, 'store'])->name('students.assignments.store');
    Route::delete('/students/{student}/assignments/{assignment}', [StudentAssignmentController::class, 'destroy'])->name('students.assignments.destroy');
    Route::post('/students/{student}/guardians', [StudentGuardianController::class, 'store'])->name('students.guardians.store');
    Route::post('/students/{student}/documents', [StudentDocumentController::class, 'store'])->name('students.documents.store');
});
