<?php

use App\Models\Grade;
use App\Models\GradeLevel;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\StudentProfile;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

test('teacher can view class roster for their assigned section', function () {
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
        ->get(route('teacher.roster.index'));

    $response->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('teacher/roster/Index')
            ->has('section.student_profiles', 1)
            ->where('section.student_profiles.0.id', $student->id)
        );
});

test('teacher sees empty state when no section is assigned', function () {
    $teacher = User::factory()->teacher()->create();

    $response = $this->actingAs($teacher)
        ->get(route('teacher.roster.index'));

    $response->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('teacher/roster/Index')
            ->where('section', null)
        );
});

test('teacher can view grade matrix and bulk encode quarterly grades', function () {
    $teacher = User::factory()->teacher()->create();
    $sy = SchoolYear::factory()->active()->create();
    $gl = GradeLevel::factory()->create(['name' => 'Grade 2', 'order' => 2]);
    $subject = Subject::factory()->create(['grade_level_id' => $gl->id, 'name' => 'Mathematics']);

    $section = Section::factory()->create([
        'school_year_id' => $sy->id,
        'grade_level_id' => $gl->id,
        'name' => 'Mabini',
        'adviser_id' => $teacher->id,
    ]);

    $student1 = StudentProfile::factory()->create(['section_id' => $section->id]);
    $student2 = StudentProfile::factory()->create(['section_id' => $section->id]);

    $response = $this->actingAs($teacher)
        ->get(route('teacher.grades.index', ['subject_id' => $subject->id, 'quarter' => 'Q1']));

    $response->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('teacher/grades/Matrix')
            ->has('students', 2)
            ->has('subjects', 1)
        );

    // Encode grades for both students
    $postResponse = $this->actingAs($teacher)
        ->post(route('teacher.grades.store'), [
            'grades' => [
                [
                    'student_id' => $student1->id,
                    'subject_id' => $subject->id,
                    'school_year_id' => $sy->id,
                    'quarter' => 'Q1',
                    'score' => 88,
                ],
                [
                    'student_id' => $student2->id,
                    'subject_id' => $subject->id,
                    'school_year_id' => $sy->id,
                    'quarter' => 'Q1',
                    'score' => 74, // Below 75
                ],
            ],
        ]);

    $postResponse->assertRedirect();

    $grade1 = Grade::where('student_id', $student1->id)->where('quarter', 'Q1')->first();
    $grade2 = Grade::where('student_id', $student2->id)->where('quarter', 'Q1')->first();

    expect($grade1->score)->toBe(88)
        ->and($grade1->isPassing())->toBeTrue()
        ->and($grade2->score)->toBe(74)
        ->and($grade2->isPassing())->toBeFalse();
});
