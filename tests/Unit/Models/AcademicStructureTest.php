<?php

use App\Models\GradeLevel;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\StudentProfile;
use App\Models\Subject;
use App\Models\User;
use Database\Seeders\GradeLevelSeeder;
use Database\Seeders\SubjectSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('school year active scope returns active school year', function () {
    SchoolYear::factory()->create(['label' => '2025-2026', 'is_active' => false]);
    $active = SchoolYear::factory()->active()->create(['label' => '2026-2027']);

    $result = SchoolYear::active()->first();
    expect($result->id)->toBe($active->id)
        ->and($result->label)->toBe('2026-2027');
});

test('grade level and subject seeders seed 7 grade levels and 49 deped subjects', function () {
    $this->seed(GradeLevelSeeder::class);
    $this->seed(SubjectSeeder::class);

    expect(GradeLevel::count())->toBe(7)
        ->and(Subject::count())->toBe(49);

    $kinder = GradeLevel::where('order', 0)->first();
    expect($kinder->subjects()->count())->toBe(5);

    $grade6 = GradeLevel::where('order', 6)->first();
    expect($grade6->subjects()->count())->toBe(9);
});

test('section relationships with school year, grade level, adviser, and students work', function () {
    $sy = SchoolYear::factory()->active()->create();
    $gl = GradeLevel::factory()->create(['name' => 'Grade 1', 'order' => 1]);
    $adviser = User::factory()->teacher()->create();

    $section = Section::factory()->create([
        'school_year_id' => $sy->id,
        'grade_level_id' => $gl->id,
        'name' => 'Sampaguita',
        'adviser_id' => $adviser->id,
    ]);

    $student = StudentProfile::factory()->create([
        'section_id' => $section->id,
    ]);

    expect($section->schoolYear->id)->toBe($sy->id)
        ->and($section->gradeLevel->id)->toBe($gl->id)
        ->and($section->adviser->id)->toBe($adviser->id)
        ->and($section->studentProfiles)->toHaveCount(1)
        ->and($section->studentProfiles->first()->id)->toBe($student->id);
});
