<?php

use App\Models\AttendanceRecord;
use App\Models\GradeLevel;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

test('student can view their attendance summary and log history', function () {
    $sy = SchoolYear::factory()->active()->create([
        'label' => '2026-2027',
        'start_date' => '2026-08-15',
        'end_date' => '2027-05-30',
    ]);
    $gl = GradeLevel::factory()->create(['name' => 'Grade 1', 'order' => 1]);
    $section = Section::factory()->create([
        'school_year_id' => $sy->id,
        'grade_level_id' => $gl->id,
        'name' => 'Mabini',
    ]);

    $studentUser = User::factory()->student()->create();
    $studentProfile = StudentProfile::factory()->create([
        'user_id' => $studentUser->id,
        'section_id' => $section->id,
    ]);

    $teacher = User::factory()->teacher()->create();

    AttendanceRecord::create([
        'student_id' => $studentProfile->id,
        'section_id' => $section->id,
        'date' => '2026-09-01',
        'status' => 'present',
        'encoded_by' => $teacher->id,
    ]);

    AttendanceRecord::create([
        'student_id' => $studentProfile->id,
        'section_id' => $section->id,
        'date' => '2026-09-02',
        'status' => 'absent',
        'remarks' => 'Flu',
        'encoded_by' => $teacher->id,
    ]);

    $response = $this->actingAs($studentUser)
        ->get(route('student.attendance.index'));

    $response->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('student/attendance/Index')
            ->has('summary')
            ->where('summary.present_count', 1)
            ->where('summary.absent_count', 1)
            ->where('summary.attendance_rate', 50)
            ->has('records', 2)
        );
});
