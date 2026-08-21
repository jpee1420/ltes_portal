<?php

use App\Actions\Grades\EncodeGrade;
use App\Models\GradeLevel;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\StudentProfile;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

test('student can view their own report card and cannot see other students', function () {
    $sy = SchoolYear::factory()->active()->create(['label' => '2026-2027']);
    $gl = GradeLevel::factory()->create(['name' => 'Grade 3', 'order' => 3]);
    $section = Section::factory()->create([
        'school_year_id' => $sy->id,
        'grade_level_id' => $gl->id,
        'name' => 'Silang',
    ]);

    $math = Subject::factory()->create(['grade_level_id' => $gl->id, 'name' => 'Mathematics']);

    $studentUser = User::factory()->student()->create();
    $studentProfile = StudentProfile::factory()->create([
        'user_id' => $studentUser->id,
        'section_id' => $section->id,
    ]);

    $teacher = User::factory()->teacher()->create();

    app(EncodeGrade::class)->handle([
        'student_id' => $studentProfile->id,
        'subject_id' => $math->id,
        'school_year_id' => $sy->id,
        'quarter' => 'Q1',
        'score' => 91,
        'encoded_by' => $teacher->id,
    ]);

    $response = $this->actingAs($studentUser)
        ->get(route('student.grades.index'));

    $response->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('student/grades/Index')
            ->has('reportCard.subjects', 1)
            ->where('reportCard.subjects.0.Q1', 91)
            ->where('reportCard.student.name', $studentUser->name)
        );
});
