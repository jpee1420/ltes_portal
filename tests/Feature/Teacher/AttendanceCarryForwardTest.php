<?php

use App\Models\AttendanceRecord;
use App\Models\GradeLevel;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('teacher can fetch carry-forward attendance from last recorded date', function () {
    $teacher = User::factory()->teacher()->create();
    $schoolYear = SchoolYear::factory()->active()->create();
    $gradeLevel = GradeLevel::factory()->create();

    $section = Section::factory()->create([
        'adviser_id' => $teacher->id,
        'school_year_id' => $schoolYear->id,
        'grade_level_id' => $gradeLevel->id,
        'name' => 'Grade 6 - Luna',
    ]);

    $student1 = User::factory()->student()->create();
    $profile1 = StudentProfile::factory()->create([
        'user_id' => $student1->id,
        'section_id' => $section->id,
    ]);

    $student2 = User::factory()->student()->create();
    $profile2 = StudentProfile::factory()->create([
        'user_id' => $student2->id,
        'section_id' => $section->id,
    ]);

    // Create past attendance record on 2026-08-20
    AttendanceRecord::create([
        'student_id' => $profile1->id,
        'section_id' => $section->id,
        'date' => '2026-08-20',
        'status' => 'present',
        'encoded_by' => $teacher->id,
    ]);

    AttendanceRecord::create([
        'student_id' => $profile2->id,
        'section_id' => $section->id,
        'date' => '2026-08-20',
        'status' => 'absent',
        'remarks' => 'Fever',
        'encoded_by' => $teacher->id,
    ]);

    $response = $this->actingAs($teacher)
        ->getJson(route('teacher.attendance.carry-forward', ['before_date' => '2026-08-21']));

    $response->assertOk()
        ->assertJson([
            'last_date' => '2026-08-20',
            'records' => [
                $profile1->id => [
                    'status' => 'present',
                    'remarks' => null,
                ],
                $profile2->id => [
                    'status' => 'absent',
                    'remarks' => 'Fever',
                ],
            ],
        ]);
});

test('carry-forward returns null when no previous attendance records exist', function () {
    $teacher = User::factory()->teacher()->create();
    $schoolYear = SchoolYear::factory()->active()->create();
    $gradeLevel = GradeLevel::factory()->create();

    $section = Section::factory()->create([
        'adviser_id' => $teacher->id,
        'school_year_id' => $schoolYear->id,
        'grade_level_id' => $gradeLevel->id,
    ]);

    $response = $this->actingAs($teacher)
        ->getJson(route('teacher.attendance.carry-forward'));

    $response->assertOk()
        ->assertJson([
            'last_date' => null,
            'records' => [],
        ]);
});

test('non-teacher cannot access carry-forward endpoint', function () {
    $student = User::factory()->student()->create();

    $this->actingAs($student)
        ->getJson('/teacher/attendance/carry-forward')
        ->assertForbidden();
});
