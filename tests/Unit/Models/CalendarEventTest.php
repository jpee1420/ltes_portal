<?php

use App\Enums\EventCategory;
use App\Models\CalendarEvent;
use App\Models\GradeLevel;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('event category returns correct labels and colors', function () {
    expect(EventCategory::Exam->label())->toBe('Examination');
    expect(EventCategory::Exam->color())->toBe('red');
    expect(EventCategory::Exam->isSchoolWide())->toBeTrue();

    expect(EventCategory::Holiday->label())->toBe('Holiday / No Classes');
    expect(EventCategory::Holiday->color())->toBe('amber');

    expect(EventCategory::SectionEvent->label())->toBe('Section Event');
    expect(EventCategory::SectionEvent->color())->toBe('emerald');
    expect(EventCategory::SectionEvent->isSchoolWide())->toBeFalse();
});

test('admin sees all school-wide and section events for active school year', function () {
    $admin = User::factory()->admin()->create();
    $schoolYear = SchoolYear::factory()->active()->create();
    $gradeLevel = GradeLevel::factory()->create();
    $section = Section::factory()->create([
        'school_year_id' => $schoolYear->id,
        'grade_level_id' => $gradeLevel->id,
        'name' => 'Section Admin Test',
    ]);

    $schoolWideEvent = CalendarEvent::factory()->create([
        'school_year_id' => $schoolYear->id,
        'section_id' => null,
        'created_by' => $admin->id,
    ]);

    $sectionEvent = CalendarEvent::factory()->create([
        'school_year_id' => $schoolYear->id,
        'section_id' => $section->id,
        'created_by' => $admin->id,
    ]);

    $events = CalendarEvent::query()->forUser($admin)->get();

    expect($events)->toHaveCount(2)
        ->pluck('id')->toContain($schoolWideEvent->id, $sectionEvent->id);
});

test('teacher sees school-wide events and events for their advised section only', function () {
    $teacher = User::factory()->teacher()->create();
    $otherTeacher = User::factory()->teacher()->create();
    $schoolYear = SchoolYear::factory()->active()->create();
    $gradeLevel = GradeLevel::factory()->create();

    $mySection = Section::factory()->create([
        'school_year_id' => $schoolYear->id,
        'grade_level_id' => $gradeLevel->id,
        'adviser_id' => $teacher->id,
        'name' => 'My Section Unique',
    ]);

    $otherSection = Section::factory()->create([
        'school_year_id' => $schoolYear->id,
        'grade_level_id' => $gradeLevel->id,
        'adviser_id' => $otherTeacher->id,
        'name' => 'Other Section Unique',
    ]);

    $schoolWideEvent = CalendarEvent::factory()->create([
        'school_year_id' => $schoolYear->id,
        'section_id' => null,
    ]);

    $mySectionEvent = CalendarEvent::factory()->create([
        'school_year_id' => $schoolYear->id,
        'section_id' => $mySection->id,
    ]);

    $otherSectionEvent = CalendarEvent::factory()->create([
        'school_year_id' => $schoolYear->id,
        'section_id' => $otherSection->id,
    ]);

    $events = CalendarEvent::query()->forUser($teacher)->get();

    expect($events)->toHaveCount(2)
        ->pluck('id')->toContain($schoolWideEvent->id, $mySectionEvent->id)
        ->pluck('id')->not->toContain($otherSectionEvent->id);
});

test('student sees school-wide events and their own section events only', function () {
    $schoolYear = SchoolYear::factory()->active()->create();
    $gradeLevel = GradeLevel::factory()->create();

    $mySection = Section::factory()->create([
        'school_year_id' => $schoolYear->id,
        'grade_level_id' => $gradeLevel->id,
        'name' => 'Student Section Alpha',
    ]);

    $otherSection = Section::factory()->create([
        'school_year_id' => $schoolYear->id,
        'grade_level_id' => $gradeLevel->id,
        'name' => 'Student Section Beta',
    ]);

    $student = User::factory()->student()->create();
    StudentProfile::factory()->create([
        'user_id' => $student->id,
        'section_id' => $mySection->id,
    ]);

    $schoolWideEvent = CalendarEvent::factory()->create([
        'school_year_id' => $schoolYear->id,
        'section_id' => null,
    ]);

    $mySectionEvent = CalendarEvent::factory()->create([
        'school_year_id' => $schoolYear->id,
        'section_id' => $mySection->id,
    ]);

    $otherSectionEvent = CalendarEvent::factory()->create([
        'school_year_id' => $schoolYear->id,
        'section_id' => $otherSection->id,
    ]);

    $events = CalendarEvent::query()->forUser($student)->get();

    expect($events)->toHaveCount(2)
        ->pluck('id')->toContain($schoolWideEvent->id, $mySectionEvent->id)
        ->pluck('id')->not->toContain($otherSectionEvent->id);
});

test('scope in month filters events accurately', function () {
    $schoolYear = SchoolYear::factory()->active()->create();

    $septemberEvent = CalendarEvent::factory()->create([
        'school_year_id' => $schoolYear->id,
        'start_date' => '2026-09-15',
        'end_date' => null,
    ]);

    $spanningEvent = CalendarEvent::factory()->create([
        'school_year_id' => $schoolYear->id,
        'start_date' => '2026-08-28',
        'end_date' => '2026-09-03',
    ]);

    $octoberEvent = CalendarEvent::factory()->create([
        'school_year_id' => $schoolYear->id,
        'start_date' => '2026-10-05',
        'end_date' => null,
    ]);

    $septEvents = CalendarEvent::query()->inMonth(2026, 9)->get();

    expect($septEvents)->toHaveCount(2)
        ->pluck('id')->toContain($septemberEvent->id, $spanningEvent->id)
        ->pluck('id')->not->toContain($octoberEvent->id);
});
