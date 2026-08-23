<?php

use App\Models\GradeLevel;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

test('section model calculates enrolled count and capacity correctly', function () {
    $schoolYear = SchoolYear::factory()->active()->create();
    $gradeLevel = GradeLevel::factory()->create();

    $section = Section::factory()->create([
        'school_year_id' => $schoolYear->id,
        'grade_level_id' => $gradeLevel->id,
        'name' => 'Capacity Test Section A',
        'max_students' => 2,
    ]);

    expect($section->isAtCapacity())->toBeFalse()
        ->and($section->enrolledCount())->toBe(0);

    // Enroll 1 student
    $student1 = User::factory()->student()->create();
    StudentProfile::factory()->create([
        'user_id' => $student1->id,
        'section_id' => $section->id,
    ]);

    expect($section->isAtCapacity())->toBeFalse()
        ->and($section->enrolledCount())->toBe(1);

    // Enroll 2nd student (at capacity)
    $student2 = User::factory()->student()->create();
    StudentProfile::factory()->create([
        'user_id' => $student2->id,
        'section_id' => $section->id,
    ]);

    expect($section->isAtCapacity())->toBeTrue()
        ->and($section->enrolledCount())->toBe(2);

    // Unlimited section (max_students null)
    $unlimitedSection = Section::factory()->create([
        'school_year_id' => $schoolYear->id,
        'grade_level_id' => $gradeLevel->id,
        'name' => 'Capacity Test Section B',
        'max_students' => null,
    ]);

    expect($unlimitedSection->isAtCapacity())->toBeFalse();
});

test('admin can create a section with optional max students capacity', function () {
    $admin = User::factory()->admin()->create();
    $schoolYear = SchoolYear::factory()->active()->create();
    $gradeLevel = GradeLevel::factory()->create();

    $response = $this->actingAs($admin)
        ->post(route('admin.sections.store'), [
            'school_year_id' => $schoolYear->id,
            'grade_level_id' => $gradeLevel->id,
            'name' => 'Mabini',
            'max_students' => 35,
        ]);

    $response->assertRedirect(route('admin.sections.index'));

    $section = Section::where('name', 'Mabini')->first();
    expect($section)->not->toBeNull()
        ->and($section->max_students)->toBe(35);
});

test('admin section index displays capacity warnings for full sections', function () {
    $admin = User::factory()->admin()->create();
    $schoolYear = SchoolYear::factory()->active()->create();
    $gradeLevel = GradeLevel::factory()->create();

    $section = Section::factory()->create([
        'school_year_id' => $schoolYear->id,
        'grade_level_id' => $gradeLevel->id,
        'name' => 'Full Section',
        'max_students' => 1,
    ]);

    $student = User::factory()->student()->create();
    StudentProfile::factory()->create([
        'user_id' => $student->id,
        'section_id' => $section->id,
    ]);

    $response = $this->actingAs($admin)
        ->get(route('admin.sections.index'));

    $response->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('admin/sections/Index')
            ->has('sections.data.0', fn (AssertableInertia $sec) => $sec
                ->where('id', $section->id)
                ->where('max_students', 1)
                ->where('enrolled_count', 1)
                ->where('is_at_capacity', true)
                ->etc()
            )
        );
});
