<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\CalendarController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\SchoolYearController;
use App\Http\Controllers\Admin\SectionController;
use App\Http\Controllers\Admin\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // User Management
    Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
    Route::get('/users/students/create', [UserManagementController::class, 'createStudent'])->name('users.students.create');
    Route::post('/users/students', [UserManagementController::class, 'storeStudent'])->name('users.students.store');
    Route::get('/users/students/import', [UserManagementController::class, 'importStudentsForm'])->name('users.students.import');
    Route::post('/users/students/import', [UserManagementController::class, 'importStudents'])->name('users.students.import.process');
    Route::get('/users/students/import/template', [UserManagementController::class, 'downloadImportTemplate'])->name('users.students.import.template');
    Route::get('/users/staff/create', [UserManagementController::class, 'createStaff'])->name('users.staff.create');
    Route::post('/users/staff', [UserManagementController::class, 'storeStaff'])->name('users.staff.store');
    Route::post('/users/{user}/reset-password', [UserManagementController::class, 'resetPassword'])->name('users.reset-password');
    Route::post('/users/{user}/photo', [UserManagementController::class, 'uploadPhoto'])->name('users.photo');

    // School Year Management
    Route::get('/school-years', [SchoolYearController::class, 'index'])->name('school-years.index');
    Route::get('/school-years/create', [SchoolYearController::class, 'create'])->name('school-years.create');
    Route::post('/school-years', [SchoolYearController::class, 'store'])->name('school-years.store');
    Route::post('/school-years/{schoolYear}/activate', [SchoolYearController::class, 'activate'])->name('school-years.activate');

    // Section Management
    Route::resource('sections', SectionController::class)->except(['destroy']);
    Route::post('/sections/{section}/assign-student', [SectionController::class, 'assignStudent'])->name('sections.assign-student');
    Route::delete('/sections/{section}/students/{student}', [SectionController::class, 'unassignStudent'])->name('sections.unassign-student');

    // Calendar Management
    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::post('/calendar', [CalendarController::class, 'store'])->name('calendar.store');
    Route::put('/calendar/{calendarEvent}', [CalendarController::class, 'update'])->name('calendar.update');
    Route::delete('/calendar/{calendarEvent}', [CalendarController::class, 'destroy'])->name('calendar.destroy');

    // Audit Log
    Route::get('/audit-log', [AuditLogController::class, 'index'])->name('audit-log.index');
});
