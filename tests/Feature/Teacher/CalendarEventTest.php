<?php

use App\Enums\EventCategory;
use App\Models\CalendarEvent;
use App\Models\GradeLevel;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('teacher can view calendar with school-wide and assigned section events', function () {
    $teacher = User::factory()->teacher()->create();
    $schoolYear = SchoolYear::factory()->active()->create();
    $gradeLevel = GradeLevel::factory()->create();

    $section = Section::factory()->create([
        'school_year_id' => $schoolYear->id,
        'grade_level_id' => $gradeLevel->id,
        'adviser_id' => $teacher->id,
    ]);

    CalendarEvent::factory()->create([
        'school_year_id' => $schoolYear->id,
        'title' => 'School Foundation Day',
        'category' => EventCategory::SchoolEvent,
        'start_date' => '2026-09-15',
        'section_id' => null,
    ]);

    CalendarEvent::factory()->create([
        'school_year_id' => $schoolYear->id,
        'title' => 'Math Quiz #1',
        'category' => EventCategory::SectionEvent,
        'start_date' => '2026-09-20',
        'section_id' => $section->id,
        'created_by' => $teacher->id,
    ]);

    $response = $this->actingAs($teacher)->get('/teacher/calendar?year=2026&month=9');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('teacher/calendar/Index')
        ->has('events', 2)
        ->where('advisedSection.id', $section->id)
    );
});

test('teacher can create a section event for their own section', function () {
    $teacher = User::factory()->teacher()->create();
    $schoolYear = SchoolYear::factory()->active()->create();
    $gradeLevel = GradeLevel::factory()->create();

    $section = Section::factory()->create([
        'school_year_id' => $schoolYear->id,
        'grade_level_id' => $gradeLevel->id,
        'adviser_id' => $teacher->id,
    ]);

    $response = $this->actingAs($teacher)->post('/teacher/calendar', [
        'title' => 'Science Chapter 1 Assessment',
        'description' => 'Bring coloring pencils and ruler.',
        'category' => 'section_event',
        'start_date' => '2026-09-25',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $created = CalendarEvent::query()->where('title', 'Science Chapter 1 Assessment')->first();
    expect($created)->not->toBeNull()
        ->and($created->category)->toBe(EventCategory::SectionEvent)
        ->and($created->section_id)->toBe($section->id)
        ->and($created->created_by)->toBe($teacher->id)
        ->and($created->start_date->toDateString())->toBe('2026-09-25');
});

test('teacher cannot create a school-wide category event', function () {
    $teacher = User::factory()->teacher()->create();
    $schoolYear = SchoolYear::factory()->active()->create();
    $gradeLevel = GradeLevel::factory()->create();

    Section::factory()->create([
        'school_year_id' => $schoolYear->id,
        'grade_level_id' => $gradeLevel->id,
        'adviser_id' => $teacher->id,
    ]);

    $response = $this->actingAs($teacher)->post('/teacher/calendar', [
        'title' => 'Unauthorized Holiday',
        'category' => 'holiday',
        'start_date' => '2026-09-30',
    ]);

    $response->assertSessionHasErrors('category');
});

test('teacher can update their own event', function () {
    $teacher = User::factory()->teacher()->create();
    $schoolYear = SchoolYear::factory()->active()->create();
    $gradeLevel = GradeLevel::factory()->create();

    $section = Section::factory()->create([
        'school_year_id' => $schoolYear->id,
        'grade_level_id' => $gradeLevel->id,
        'adviser_id' => $teacher->id,
    ]);

    $event = CalendarEvent::factory()->create([
        'school_year_id' => $schoolYear->id,
        'section_id' => $section->id,
        'created_by' => $teacher->id,
        'title' => 'Old Title',
        'category' => EventCategory::SectionEvent,
        'start_date' => '2026-09-20',
    ]);

    $response = $this->actingAs($teacher)->put("/teacher/calendar/{$event->id}", [
        'title' => 'Updated Quiz Title',
        'category' => 'section_event',
        'start_date' => '2026-09-22',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $updated = $event->fresh();
    expect($updated->title)->toBe('Updated Quiz Title')
        ->and($updated->start_date->toDateString())->toBe('2026-09-22');
});

test('teacher cannot update another teacher event', function () {
    $teacher1 = User::factory()->teacher()->create();
    $teacher2 = User::factory()->teacher()->create();
    $schoolYear = SchoolYear::factory()->active()->create();
    $gradeLevel = GradeLevel::factory()->create();

    $section2 = Section::factory()->create([
        'school_year_id' => $schoolYear->id,
        'grade_level_id' => $gradeLevel->id,
        'adviser_id' => $teacher2->id,
    ]);

    $event = CalendarEvent::factory()->create([
        'school_year_id' => $schoolYear->id,
        'section_id' => $section2->id,
        'created_by' => $teacher2->id,
        'title' => 'Teacher 2 Event',
        'category' => EventCategory::SectionEvent,
        'start_date' => '2026-09-20',
    ]);

    $response = $this->actingAs($teacher1)->put("/teacher/calendar/{$event->id}", [
        'title' => 'Attempted Hack',
    ]);

    $response->assertSessionHasErrors('event');
});

test('teacher cannot delete another teacher event', function () {
    $teacher1 = User::factory()->teacher()->create();
    $teacher2 = User::factory()->teacher()->create();
    $schoolYear = SchoolYear::factory()->active()->create();

    $event = CalendarEvent::factory()->create([
        'school_year_id' => $schoolYear->id,
        'created_by' => $teacher2->id,
    ]);

    $response = $this->actingAs($teacher1)->delete("/teacher/calendar/{$event->id}");

    $response->assertSessionHasErrors('event');
    expect(CalendarEvent::find($event->id))->not->toBeNull();
});
