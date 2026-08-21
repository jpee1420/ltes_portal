<?php

use App\Models\GradeLevel;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

test('admin can view section roster and available unassigned students', function () {
    $admin = User::factory()->admin()->create();
    $sy = SchoolYear::factory()->active()->create();
    $gl = GradeLevel::factory()->create(['name' => 'Grade 1', 'order' => 1]);
    $section = Section::factory()->create([
        'school_year_id' => $sy->id,
        'grade_level_id' => $gl->id,
        'name' => 'Rizal',
    ]);

    $enrolled = StudentProfile::factory()->create(['section_id' => $section->id]);
    $unassigned = StudentProfile::factory()->create(['section_id' => null]);

    $response = $this->actingAs($admin)
        ->get(route('admin.sections.show', $section));

    $response->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('admin/sections/Show')
            ->has('section.student_profiles', 1)
            ->has('unassignedStudents', 1)
        );
});

test('admin can assign an unassigned student to a section', function () {
    $admin = User::factory()->admin()->create();
    $sy = SchoolYear::factory()->active()->create();
    $gl = GradeLevel::factory()->create(['name' => 'Grade 1', 'order' => 1]);
    $section = Section::factory()->create([
        'school_year_id' => $sy->id,
        'grade_level_id' => $gl->id,
        'name' => 'Rizal',
    ]);

    $student = StudentProfile::factory()->create(['section_id' => null]);

    $response = $this->actingAs($admin)
        ->post(route('admin.sections.assign-student', $section), [
            'student_profile_id' => $student->id,
        ]);

    $response->assertRedirect();

    $student->refresh();
    expect($student->section_id)->toBe($section->id);
});

test('admin can remove/unassign a student from a section', function () {
    $admin = User::factory()->admin()->create();
    $sy = SchoolYear::factory()->active()->create();
    $gl = GradeLevel::factory()->create(['name' => 'Grade 1', 'order' => 1]);
    $section = Section::factory()->create([
        'school_year_id' => $sy->id,
        'grade_level_id' => $gl->id,
        'name' => 'Rizal',
    ]);

    $student = StudentProfile::factory()->create(['section_id' => $section->id]);

    $response = $this->actingAs($admin)
        ->delete(route('admin.sections.unassign-student', [$section, $student]));

    $response->assertRedirect();

    $student->refresh();
    expect($student->section_id)->toBeNull();
});
