<?php

use App\Http\Controllers\Student\AttendanceViewController;
use App\Http\Controllers\Student\DashboardController;
use App\Http\Controllers\Student\GradeViewController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:student'])->prefix('student')->name('student.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Learner Progress Report Card (Grades)
    Route::get('/grades', [GradeViewController::class, 'index'])->name('grades.index');

    // Attendance Log & Summary
    Route::get('/attendance', [AttendanceViewController::class, 'index'])->name('attendance.index');
});
