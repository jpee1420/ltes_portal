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

test('teacher can view daily attendance interface for their section', function () {
    $teacher = User::factory()->teacher()->create();
    $sy = SchoolYear::factory()->active()->create();
    $gl = GradeLevel::factory()->create(['name' => 'Grade 1', 'order' => 1]);
    $section = Section::factory()->create([
        'school_year_id' => $sy->id,
        'grade_level_id' => $gl->id,
        'name' => 'Rizal',
        'adviser_id' => $teacher->id,
    ]);

    $student = StudentProfile::factory()->create(['section_id' => $section->id]);

    $response = $this->actingAs($teacher)
        ->get(route('teacher.attendance.index', ['date' => '2026-09-01']));

    $response->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('teacher/attendance/Daily')
            ->has('students', 1)
            ->where('date', '2026-09-01')
        );
});

test('teacher can save and update daily attendance records', function () {
    $teacher = User::factory()->teacher()->create();
    $sy = SchoolYear::factory()->active()->create();
    $gl = GradeLevel::factory()->create(['name' => 'Grade 1', 'order' => 1]);
    $section = Section::factory()->create([
        'school_year_id' => $sy->id,
        'grade_level_id' => $gl->id,
        'name' => 'Rizal',
        'adviser_id' => $teacher->id,
    ]);

    $student1 = StudentProfile::factory()->create(['section_id' => $section->id]);
    $student2 = StudentProfile::factory()->create(['section_id' => $section->id]);

    $response = $this->actingAs($teacher)
        ->post(route('teacher.attendance.store'), [
            'date' => '2026-09-01',
            'records' => [
                [
                    'student_id' => $student1->id,
                    'status' => 'present',
                ],
                [
                    'student_id' => $student2->id,
                    'status' => 'absent',
                    'remarks' => 'Fever',
                ],
            ],
        ]);

    $response->assertRedirect();

    $rec1 = AttendanceRecord::where('student_id', $student1->id)->whereDate('date', '2026-09-01')->first();
    $rec2 = AttendanceRecord::where('student_id', $student2->id)->whereDate('date', '2026-09-01')->first();

    expect($rec1)->not->toBeNull()
        ->and($rec1->status)->toBe('present')
        ->and($rec2)->not->toBeNull()
        ->and($rec2->status)->toBe('absent')
        ->and($rec2->remarks)->toBe('Fever');
});
